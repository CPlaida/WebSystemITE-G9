<?php
namespace App\Services\Billing\Providers;

use App\Models\ServiceModel;

class RoomChargeProvider extends AbstractChargeProvider
{
    protected ?ServiceModel $serviceModel = null;
    
    public function __construct(?\CodeIgniter\Database\ConnectionInterface $db = null)
    {
        parent::__construct($db);
        if (class_exists(ServiceModel::class)) {
            $this->serviceModel = new ServiceModel();
        }
    }
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
            
            // Filter by billed status
            if ($this->fieldExists('admission_details', 'billed')) {
                $builder->groupStart()
                    ->where('ad.billed', 0)
                    ->orWhere('ad.billed IS NULL', null, false)
                    ->groupEnd();
            }
            
            // Only include admitted status (current active admission) OR discharged admissions
            // that haven't been linked to billing_items (not yet billed)
            if ($this->tableExists('billing_items')) {
                // Include admitted status OR discharged that are not linked to billing_items
                // Use a subquery to exclude discharged admissions already in billing_items
                $builder->groupStart()
                    ->where('ad.status', 'admitted')
                    ->orGroupStart()
                        ->where('ad.status', 'discharged')
                        // Exclude discharged admissions that are already linked to billing_items
                        ->where('NOT EXISTS (SELECT 1 FROM billing_items bi WHERE bi.source_table = \'admission_details\' AND CAST(bi.source_id AS UNSIGNED) = ad.id)', null, false)
                        ->groupEnd()
                    ->groupEnd();
            } else {
                // If billing_items table doesn't exist, show both admitted and discharged
                $builder->whereIn('ad.status', ['admitted', 'discharged']);
            }
            
            $builder->orderBy('ad.admission_date', 'DESC');
            $admissions = $builder->get()->getResultArray();

            if (!empty($admissions)) {
                // Additional filter to exclude admissions already linked to billing_items
                // This is a safety net to ensure we don't show already-billed admissions
                $admissions = $this->filterOutAlreadyLinked($admissions, 'admission_details');
                
                if (!empty($admissions)) {
                    $bedIds = array_values(array_filter(array_map(fn($row) => $row['bed_id'] ?? null, $admissions)));
                    $beds = $this->loadBeds($bedIds);

                    foreach ($admissions as $row) {
                        $bedId = (int)($row['bed_id'] ?? 0);
                        $bed = $bedId && isset($beds[$bedId]) ? $beds[$bedId] : null;
                        $ward = $row['ward'] ?? ($bed['ward'] ?? 'Room');
                        $bedType = $bed['bed_type'] ?? '';
                        
                        // Determine rate and service_id
                        $rateInfo = $this->determineRoomRate($ward, $bedType, $bed);
                        $rate = $rateInfo['rate'];
                        $serviceId = $rateInfo['service_id'];
                        $serviceName = $rateInfo['service_name'];
                        
                        // Use default rate if still 0
                        if ($rate <= 0) {
                            $rate = 500.0; // Default room rate
                        }
                        $days = $this->calculateDays($row);
                        if ($days <= 0) {
                            $days = 1;
                        }
                        $amount = $rate * $days;
                        $roomNum = $row['room'] ?? ($bed['room'] ?? '');
                        
                        // Build service description
                        if ($serviceName) {
                            $service = sprintf('%s - %s %s - %d day%s', 
                                $serviceName, 
                                $ward, 
                                $roomNum ? "#{$roomNum}" : '', 
                                $days, 
                                $days > 1 ? 's' : ''
                            );
                        } else {
                            $service = sprintf('Room %s %s - %d day%s', 
                                $ward, 
                                $roomNum ? "#{$roomNum}" : '', 
                                $days, 
                                $days > 1 ? 's' : ''
                            );
                        }
                        $service = trim(preg_replace('/\s+/', ' ', $service));
                        
                        $item = $this->defaultItem();
                        $item['service'] = $service;
                        $item['qty'] = $days;
                        $item['price'] = $rate;
                        $item['amount'] = $amount;
                        $item['category'] = 'room';
                        $item['source_table'] = 'admission_details';
                        $item['source_id'] = (string)($row['id'] ?? '');
                        
                        // Add service_id if available
                        if ($serviceId) {
                            $item['service_id'] = $serviceId;
                        }
                        
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
                    $ward = $bed['ward'] ?? 'Room';
                    $bedType = $bed['bed_type'] ?? '';
                    
                    // Determine rate and service_id
                    $rateInfo = $this->determineRoomRate($ward, $bedType, $bed);
                    $rate = $rateInfo['rate'];
                    $serviceId = $rateInfo['service_id'];
                    $serviceName = $rateInfo['service_name'];
                    
                    if ($rate <= 0) {
                        $rate = 500.0; // Default room rate
                    }
                    
                    // Use 1 day as default if no admission date
                    $days = 1;
                    $amount = $rate * $days;
                    $roomNum = $bed['room'] ?? '';
                    
                    // Build service description
                    if ($serviceName) {
                        $service = sprintf('%s - %s %s - %d day%s', 
                            $serviceName, 
                            $ward, 
                            $roomNum ? "#{$roomNum}" : '', 
                            $days, 
                            $days > 1 ? 's' : ''
                        );
                    } else {
                        $service = sprintf('Room %s %s - %d day%s', 
                            $ward, 
                            $roomNum ? "#{$roomNum}" : '', 
                            $days, 
                            $days > 1 ? 's' : ''
                        );
                    }
                    $service = trim(preg_replace('/\s+/', ' ', $service));
                    
                    $item = $this->defaultItem();
                    $item['service'] = $service;
                    $item['qty'] = $days;
                    $item['price'] = $rate;
                    $item['amount'] = $amount;
                    $item['category'] = 'room';
                    $item['source_table'] = 'patients';
                    $item['source_id'] = (string)$patientId;
                    
                    // Add service_id if available
                    if ($serviceId) {
                        $item['service_id'] = $serviceId;
                    }
                    
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

    /**
     * Determine room rate from services table or fallback to hardcoded values
     * 
     * @param string $ward
     * @param string $bedType
     * @param array|null $bed
     * @return array{rate: float, service_id: int|null, service_name: string|null}
     */
    protected function determineRoomRate(string $ward, string $bedType, ?array $bed): array
    {
        $result = [
            'rate' => 0.0,
            'service_id' => null,
            'service_name' => null,
        ];
        
        // Priority 1: Use room_rate from bed if available
        if ($bed && isset($bed['room_rate']) && (float)$bed['room_rate'] > 0) {
            $result['rate'] = (float)$bed['room_rate'];
            // Try to find matching service
            $service = $this->findRoomServiceByWard($ward, $bedType);
            if ($service) {
                $result['service_id'] = (int)$service['id'];
                $result['service_name'] = $service['name'] ?? null;
            }
            return $result;
        }
        
        // Priority 2: Look up service from services table
        $service = $this->findRoomServiceByWard($ward, $bedType);
        if ($service && isset($service['base_price']) && (float)$service['base_price'] > 0) {
            $result['rate'] = (float)$service['base_price'];
            $result['service_id'] = (int)$service['id'];
            $result['service_name'] = $service['name'] ?? null;
            return $result;
        }
        
        // Priority 3: Fallback to hardcoded values (for backward compatibility)
        $result['rate'] = $this->inferRateFromType($bed);
        return $result;
    }
    
    /**
     * Find room service by ward and bed type
     * 
     * @param string $ward
     * @param string $bedType
     * @return array|null
     */
    protected function findRoomServiceByWard(string $ward, string $bedType): ?array
    {
        if ($this->serviceModel === null) {
            return null;
        }
        
        $wardLower = strtolower(trim($ward));
        $typeLower = strtolower(trim($bedType));
        
        // Map ward/bed_type to service codes
        $codeMap = [
            // Critical Care Units
            'icu' => 'ROOM-ICU',
            'nicu' => 'ROOM-NICU',
            'picu' => 'ROOM-PICU',
            'ccu' => 'ROOM-CCU',
            'coronary care unit' => 'ROOM-CCU',
            'micu' => 'ROOM-MICU',
            'medical icu' => 'ROOM-MICU',
            'sicu' => 'ROOM-SICU',
            'surgical icu' => 'ROOM-SICU',
            
            // Specialized Rooms
            'ed' => 'ROOM-ED',
            'emergency department' => 'ROOM-ED',
            'iso' => 'ROOM-ISOLATION',
            'isolation' => 'ROOM-ISOLATION',
            'isolation room' => 'ROOM-ISOLATION',
            'ld' => 'ROOM-LD',
            'labor & delivery' => 'ROOM-LD',
            'labor and delivery' => 'ROOM-LD',
            'delivery room' => 'ROOM-LD',
            'sdu' => 'ROOM-SDU',
            'step-down unit' => 'ROOM-SDU',
            
            // General Wards
            'pedia ward' => 'ROOM-PEDIA',
            'pediatric ward' => 'ROOM-PEDIA',
            'male ward' => 'ROOM-WARD',
            'female ward' => 'ROOM-WARD',
            'general ward' => 'ROOM-WARD',
            'ward' => 'ROOM-WARD',
            
            // Semi-Private and Private
            'semi-private ward' => 'ROOM-SEMIPRIVATE',
            'semi-private' => 'ROOM-SEMIPRIVATE',
            'private suites' => 'ROOM-PRIVATE',
            'private' => 'ROOM-PRIVATE',
            'private ward' => 'ROOM-PRIVATE',
            'private room' => 'ROOM-PRIVATE',
        ];
        
        // Check ward first, then bed_type
        $code = null;
        foreach ([$wardLower, $typeLower] as $key) {
            if ($key && isset($codeMap[$key])) {
                $code = $codeMap[$key];
                break;
            }
        }
        
        if ($code) {
            try {
                $service = $this->serviceModel->where('code', $code)
                    ->where('active', 1)
                    ->where('category', 'room')
                    ->first();
                if ($service) {
                    return $service;
                }
            } catch (\Throwable $e) {
                // Ignore errors
            }
        }
        
        return null;
    }
    
    /**
     * Fallback method for inferring rate from type (backward compatibility)
     * 
     * @param array|null $bed
     * @return float
     */
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
