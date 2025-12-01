<?php

namespace App\Models;

use CodeIgniter\Model;

class AdmissionModel extends Model
{
    protected $table = 'admission_details';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'patient_id',
        'admission_date',
        'admission_time',
        'admission_type',
        'attending_doctor_id',
        'ward',
        'room',
        'bed_id',
        'admitting_diagnosis',
        'reason_admission',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'required|string|is_not_unique[patients.id] ',
        'admission_date' => 'required|valid_date',
        'admission_time' => 'permit_empty',
        'admission_type' => 'required|in_list[emergency,elective,transfer]',
        'attending_doctor_id' => 'required|is_not_unique[doctors.id]',
        'ward' => 'permit_empty|string|max_length[100]',
        'room' => 'permit_empty|string|max_length[100]',
        'bed_id' => 'required|integer|is_not_unique[beds.id]',
        'admitting_diagnosis' => 'required|string',
        'reason_admission' => 'permit_empty|string',
        'status' => 'permit_empty|in_list[admitted,discharged,cancelled]'
    ];

    protected $afterInsert = ['occupyBed'];

    protected function occupyBed(array $data)
    {
        try {
            $id = $data['id'] ?? null;
            if (!$id) return $data;
            $row = $this->find($id);
            if (!$row) return $data;
            $bedId = $row['bed_id'] ?? null;
            if (!$bedId) return $data;

            $bedModel = new BedModel();
            $bedModel->update($bedId, ['status' => 'Occupied']);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to occupy bed on admission: ' . $e->getMessage());
        }
        return $data;
    }

    /**
     * Get admission statistics
     */
    public function getAdmissionStatistics(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['start_date'])) {
            $builder->where('admission_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('admission_date <=', $filters['end_date']);
        }

        if (!empty($filters['department'])) {
            $builder->where('ward', $filters['department']);
        }

        $admissions = $builder->get()->getResultArray();

        $stats = [
            'total_admissions' => count($admissions),
            'by_type' => ['emergency' => 0, 'elective' => 0, 'transfer' => 0],
            'by_department' => [],
            'average_length_of_stay' => 0,
        ];

        $totalDays = 0;
        $dischargedCount = 0;

        foreach ($admissions as $adm) {
            $type = $adm['admission_type'] ?? 'elective';
            if (isset($stats['by_type'][$type])) {
                $stats['by_type'][$type]++;
            }

            $ward = $adm['ward'] ?? 'Unknown';
            $stats['by_department'][$ward] = ($stats['by_department'][$ward] ?? 0) + 1;

            if ($adm['status'] === 'discharged' && !empty($adm['admission_date'])) {
                $admitDate = new \DateTime($adm['admission_date']);
                // Use updated_at as discharge date if available, otherwise use current date
                $dischargeDate = !empty($adm['updated_at']) 
                    ? new \DateTime($adm['updated_at']) 
                    : new \DateTime();
                $days = $admitDate->diff($dischargeDate)->days;
                $totalDays += $days;
                $dischargedCount++;
            }
        }

        $stats['average_length_of_stay'] = $dischargedCount > 0 ? $totalDays / $dischargedCount : 0;

        return [
            'statistics' => $stats,
            'admissions' => $admissions,
        ];
    }

    /**
     * Get discharge statistics
     * Note: discharge_date column doesn't exist, using updated_at as proxy for discharge date
     */
    public function getDischargeStatistics(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('status', 'discharged');

        // Filter by updated_at (when status was changed to discharged) or admission_date
        if (!empty($filters['start_date'])) {
            $builder->groupStart();
            $builder->where('updated_at >=', $filters['start_date'] . ' 00:00:00');
            $builder->orWhere('admission_date >=', $filters['start_date']);
            $builder->groupEnd();
        }

        if (!empty($filters['end_date'])) {
            $builder->groupStart();
            $builder->where('updated_at <=', $filters['end_date'] . ' 23:59:59');
            $builder->orWhere('admission_date <=', $filters['end_date']);
            $builder->groupEnd();
        }

        if (!empty($filters['department'])) {
            $builder->where('ward', $filters['department']);
        }

        $discharges = $builder->get()->getResultArray();

        $lengthOfStay = [];
        foreach ($discharges as $discharge) {
            if (!empty($discharge['admission_date'])) {
                $admit = new \DateTime($discharge['admission_date']);
                // Use updated_at as discharge date if available, otherwise use current date
                $dischargeDate = !empty($discharge['updated_at']) 
                    ? new \DateTime($discharge['updated_at']) 
                    : new \DateTime();
                $days = $admit->diff($dischargeDate)->days;
                $lengthOfStay[] = $days;
            }
        }

        $avgLength = count($lengthOfStay) > 0 ? array_sum($lengthOfStay) / count($lengthOfStay) : 0;

        return [
            'total_discharges' => count($discharges),
            'average_length_of_stay' => $avgLength,
            'discharges' => $discharges,
        ];
    }
}
