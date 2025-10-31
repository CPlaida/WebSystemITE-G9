<?php

namespace App\Models;

use CodeIgniter\Model;

class LaboratoryModel extends Model
{
    protected $table = 'laboratory';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'patient_id', 'doctor_id', 'test_name', 'test_type', 'priority',
        'test_date', 'test_time', 'test_results', 'normal_range', 'status',
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
}
