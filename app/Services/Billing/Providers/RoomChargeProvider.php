<?php
namespace App\Services\Billing\Providers;

class RoomChargeProvider extends AbstractChargeProvider
{
    public function getCharges(string $patientId): array
    {
        $patientId = trim($patientId);
        if ($patientId === '') {
            return [];
        }

        $items = [];
        
        // First, try to get charges from admission_details table
        if ($this->tableExists('admission_details')) {
            $builder = $this->db->table('admission_details ad');
            $builder->select('ad.id, ad.patient_id, ad.bed_id, ad.admission_date, ad.admission_time, ad.updated_at, ad.status, ad.ward, ad.room');
            $builder->where('ad.patient_id', $patientId);
            if ($this->fieldExists('admission_details', 'billed')) {
                $builder->groupStart()
                    ->where('ad.billed', 0)
                    ->orWhere('ad.billed IS NULL', null, false)
                    ->groupEnd();
            }
            $builder->whereIn('ad.status', ['admitted', 'discharged']);
            $builder->orderBy('ad.admission_date', 'DESC');
            $admissions = $builder->get()->getResultArray();

            if (!empty($admissions)) {
                $admissions = $this->filterOutAlreadyLinked($admissions, 'admission_details');
                
                if (!empty($admissions)) {
                    $bedIds = array_values(array_filter(array_map(fn($row) => $row['bed_id'] ?? null, $admissions)));
                    $beds = $this->loadBeds($bedIds);

                    foreach ($admissions as $row) {
                        $bedId = (int)($row['bed_id'] ?? 0);
                        $bed = $bedId && isset($beds[$bedId]) ? $beds[$bedId] : null;
                        $rate = $bed ? (float)($bed['room_rate'] ?? 0) : 0.0;
                        if ($rate <= 0) {
                            $rate = $this->inferRateFromType($bed);
                        }
                        // Use default rate if still 0
                        if ($rate <= 0) {
                            $rate = 500.0; // Default room rate
                        }
                        $days = $this->calculateDays($row);
                        if ($days <= 0) {
                            $days = 1;
                        }
                        $amount = $rate * $days;
                        $ward = $row['ward'] ?? ($bed['ward'] ?? 'Room');
                        $roomNum = $row['room'] ?? ($bed['room'] ?? '');
                        $service = sprintf('Room %s %s - %d day%s', $ward, $roomNum ? "#{$roomNum}" : '', $days, $days > 1 ? 's' : '');
                        $service = trim(preg_replace('/\s+/', ' ', $service));
                        $item = $this->defaultItem();
                        $item['service'] = $service;
                        $item['qty'] = $days;
                        $item['price'] = $rate;
                        $item['amount'] = $amount;
                        $item['category'] = 'room';
                        $item['source_table'] = 'admission_details';
                        $item['source_id'] = (string)($row['id'] ?? '');
                        $items[] = $item;
                    }
                }
            }
        }
        
        // Fallback: Check if patient has a bed_id directly in patients table (for legacy data)
        if (empty($items) && $this->tableExists('patients') && $this->fieldExists('patients', 'bed_id')) {
            $patient = $this->db->table('patients')
                ->select('bed_id, type')
                ->where('id', $patientId)
                ->where('type', 'inpatient')
                ->get()
                ->getRowArray();
            
            if ($patient && !empty($patient['bed_id'])) {
                $bedId = (int)$patient['bed_id'];
                $bed = $this->loadBeds([$bedId]);
                $bed = $bed[$bedId] ?? null;
                
                if ($bed) {
                    $rate = (float)($bed['room_rate'] ?? 0);
                    if ($rate <= 0) {
                        $rate = $this->inferRateFromType($bed);
                    }
                    if ($rate <= 0) {
                        $rate = 500.0; // Default room rate
                    }
                    
                    // Use 1 day as default if no admission date
                    $days = 1;
                    $amount = $rate * $days;
                    $ward = $bed['ward'] ?? 'Room';
                    $roomNum = $bed['room'] ?? '';
                    $service = sprintf('Room %s %s - %d day%s', $ward, $roomNum ? "#{$roomNum}" : '', $days, $days > 1 ? 's' : '');
                    $service = trim(preg_replace('/\s+/', ' ', $service));
                    
                    $item = $this->defaultItem();
                    $item['service'] = $service;
                    $item['qty'] = $days;
                    $item['price'] = $rate;
                    $item['amount'] = $amount;
                    $item['category'] = 'room';
                    $item['source_table'] = 'patients';
                    $item['source_id'] = (string)$patientId;
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * @param array<int> $bedIds
     * @return array<int, array<string,mixed>> indexed by bed id
     */
    protected function loadBeds(array $bedIds): array
    {
        $bedIds = array_values(array_unique(array_filter(array_map('intval', $bedIds))));
        if (empty($bedIds) || !$this->tableExists('beds')) {
            return [];
        }
        $rows = $this->db->table('beds')->whereIn('id', $bedIds)->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id) {
                $map[$id] = $row;
            }
        }
        return $map;
    }

    protected function inferRateFromType(?array $bed): float
    {
        if (!$bed) {
            return 0.0;
        }
        $type = strtolower(trim((string)($bed['bed_type'] ?? '')));
        $ward = strtolower(trim((string)($bed['ward'] ?? '')));
        
        // Davao Room Rates - Based on available wards in the system
        $rateMap = [
            // Critical Care Units
            'icu' => 1500.00,
            'nicu' => 1500.00,
            'picu' => 1500.00,
            
            // Specialized Rooms
            'ed' => 500.00,
            'emergency department' => 500.00,
            'iso' => 1000.00,
            'isolation' => 1000.00,
            'isolation room' => 1000.00,
            'ld' => 500.00,
            'labor & delivery' => 500.00,
            'labor and delivery' => 500.00,
            'delivery room' => 500.00,
            'sdu' => 800.00,
            'step-down unit' => 800.00,
            
            // General Wards
            'pedia ward' => 500.00,
            'pediatric ward' => 500.00,
            'male ward' => 500.00,
            'female ward' => 500.00,
            'general ward' => 500.00,
            'ward' => 500.00,
            
            // Semi-Private and Private
            'semi-private ward' => 800.00,
            'semi-private' => 800.00,
            'private suites' => 1100.00,
            'private' => 1100.00,
            'private ward' => 1100.00,
            'private room' => 1100.00,
            
            // Additional common variations
            'ccu' => 1500.00,
            'coronary care unit' => 1500.00,
            'micu' => 1500.00,
            'medical icu' => 1500.00,
            'sicu' => 1500.00,
            'surgical icu' => 1500.00,
        ];
        
        // Check ward first (more specific), then bed_type
        foreach ([$ward, $type] as $key) {
            $key = trim($key);
            if ($key && isset($rateMap[$key])) {
                return (float)$rateMap[$key];
            }
        }
        
        return 0.0;
    }

    protected function calculateDays(array $row): int
    {
        $start = $row['admission_date'] ?? null;
        if (!$start) {
            return 0;
        }
        $end = $row['updated_at'] ?? null;
        if (!$end || ($row['status'] ?? '') === 'admitted') {
            $end = date('Y-m-d H:i:s');
        }
        try {
            $startDt = new \DateTime($start . ' ' . ($row['admission_time'] ?? '00:00:00'));
            $endDt = new \DateTime($end);
            $diff = $startDt->diff($endDt);
            $days = (int)$diff->format('%a');
            if ($diff->h > 0 || $diff->i > 0 || $diff->s > 0) {
                $days += 1; // partial day billed as full
            }
            return max($days, 1);
        } catch (\Throwable $e) {
            return 1;
        }
    }
}
