<?php
namespace App\Services\Billing\Providers;

class RoomChargeProvider extends AbstractChargeProvider
{
    public function getCharges(string $patientId): array
    {
        $patientId = trim($patientId);
        if ($patientId === '' || !$this->tableExists('admission_details')) {
            return [];
        }

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

        if (empty($admissions)) {
            return [];
        }

        $admissions = $this->filterOutAlreadyLinked($admissions, 'admission_details');
        if (empty($admissions)) {
            return [];
        }

        $bedIds = array_values(array_filter(array_map(fn($row) => $row['bed_id'] ?? null, $admissions)));
        $beds = $this->loadBeds($bedIds);

        $items = [];
        foreach ($admissions as $row) {
            $bedId = (int)($row['bed_id'] ?? 0);
            $bed = $bedId && isset($beds[$bedId]) ? $beds[$bedId] : null;
            $rate = $bed ? (float)($bed['room_rate'] ?? 0) : 0.0;
            if ($rate <= 0) {
                $rate = $this->inferRateFromType($bed);
            }
            if ($rate <= 0) {
                continue;
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
        $type = strtolower((string)($bed['bed_type'] ?? ''));
        $ward = strtolower((string)($bed['ward'] ?? ''));
        $rateMap = [
            'general ward' => 500,
            'ward' => 500,
            'private ward' => 1100,
            'private room' => 1100,
            'icu' => 1500,
            'ccu' => 1500,
            'micu' => 1500,
            'nicu' => 1500,
            'picu' => 1500,
            'pediatric icu' => 1500,
            'isolation' => 1000,
            'neuro isolation' => 1000,
            'ortho isolation' => 1000,
            'delivery room' => 500,
            'labor & delivery' => 500,
        ];
        foreach ([$type, $ward] as $key) {
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
