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
        'cost', 'notes', 'created_at', 'updated_at'
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
}
