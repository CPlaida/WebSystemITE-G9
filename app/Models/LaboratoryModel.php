<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryModel extends Model
{
    protected $table = 'laboratory';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'patient_id', 'doctor_id', 'service_id', 'test_name', 'test_type', 'priority',
        'test_date', 'test_time', 'test_results', 'normal_range', 'status', 'billed',
        'cost', 'notes', 'result_file_path', 'result_file_name', 'result_file_type',
        'result_file_size', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = false; 
    protected $dateFormat   = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'test_name' => 'required|min_length[2]|max_length[200]',
        'test_type' => 'required|min_length[2]|max_length[100]',
        'priority'  => 'permit_empty|in_list[routine,normal,urgent,stat]'
    ];

    protected $validationMessages = [
        'test_name' => [
            'required' => 'Patient name is required',
        ],
        'test_type' => [
            'required' => 'Test type is required',
        ],
        'priority' => [
            'in_list' => 'Priority must be routine, normal, urgent, or stat'
        ]
    ];

    protected $beforeInsert = ['generateId'];

    protected function generateId(array $data)
    {
        if (!empty($data['data']['id'])) return $data;
        $db = \Config\Database::connect();
        $row = $db->table($this->table)
            ->select('id')
            ->like('id', 'LAB-', 'after')
            ->orderBy('id', 'DESC')
            ->get(1)->getRowArray();
        $next = 1;
        if ($row && isset($row['id'])) {
            $num = (int)substr($row['id'], 4);
            if ($num > 0) $next = $num + 1;
        }
        $data['data']['id'] = 'LAB-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        return $data;
    }

    /**
     * Get test statistics for reports
     */
    public function getTestStatistics(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['start_date'])) {
            $builder->where('test_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('test_date <=', $filters['end_date']);
        }

        if (!empty($filters['test_type'])) {
            $builder->where('test_type', $filters['test_type']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        $tests = $builder->get()->getResultArray();

        $stats = [
            'total_tests' => count($tests),
            'by_status' => ['pending' => 0, 'in_progress' => 0, 'completed' => 0],
            'by_type' => [],
            'completion_rate' => 0,
        ];

        $completed = 0;
        foreach ($tests as $test) {
            $status = $test['status'] ?? 'pending';
            if (isset($stats['by_status'][$status])) {
                $stats['by_status'][$status]++;
            }
            if ($status === 'completed') {
                $completed++;
            }

            $type = $test['test_type'] ?? 'Unknown';
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;
        }

        $stats['completion_rate'] = count($tests) > 0 ? ($completed / count($tests)) * 100 : 0;

        return [
            'statistics' => $stats,
            'tests' => $tests,
        ];
    }

    /**
     * Get test results summary
     */
    public function getTestResultsSummary(array $filters = []): array
    {
        $builder = $this->builder();
        $builder->where('status', 'completed');

        if (!empty($filters['start_date'])) {
            $builder->where('test_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('test_date <=', $filters['end_date']);
        }

        if (!empty($filters['test_type'])) {
            $builder->where('test_type', $filters['test_type']);
        }

        $tests = $builder->get()->getResultArray();

        return [
            'total_completed' => count($tests),
            'tests' => $tests,
        ];
    }
}
