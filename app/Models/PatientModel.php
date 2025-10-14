<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'address',
        'type',
        'blood_type',
        'emergency_contact',
        'medical_history',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $skipValidation = false;

    protected $validationRules = [
        'first_name' => "required|min_length[2]|max_length[100]|regex_match[/^[A-Za-z\s\-\'\.]+$/]",
        'last_name' => "permit_empty|max_length[100]|regex_match[/^[A-Za-z\s\-\'\.]+$/]",
        'email' => 'permit_empty|valid_email|is_unique[patients.email,id,{id}]',
        'phone' => 'permit_empty|max_length[20]',
        'gender' => 'required|in_list[male,female,other]',
        'date_of_birth' => 'required|valid_date',
        'type' => 'permit_empty|in_list[inpatient,outpatient]',
        'status' => 'in_list[active,inactive]'
    ];

    protected $beforeInsert = ['setCreatedAt'];
    protected $beforeUpdate = ['setUpdatedAt'];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email is already registered.'
        ],
        'id' => [
            'is_unique' => 'A patient with this ID already exists.'
        ]
    ];

    protected function setCreatedAt(array $data)
    {
        $data['data']['created_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    protected function setUpdatedAt(array $data)
    {
        $data['data']['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }
}

