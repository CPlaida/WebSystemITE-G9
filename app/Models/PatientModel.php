<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'patient_id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'patient_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'address',
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
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name' => 'permit_empty|max_length[100]',
        'email' => 'permit_empty|valid_email|is_unique[patients.email,patient_id,{patient_id}]',
        'phone' => 'permit_empty|max_length[20]',
        'gender' => 'required|in_list[male,female,other]',
        'date_of_birth' => 'required|valid_date',
        'status' => 'in_list[active,inactive]',
        'patient_id' => 'is_unique[patients.patient_id]'
    ];

    protected $beforeInsert = ['generatePatientId', 'setCreatedAt'];
    protected $beforeUpdate = ['setUpdatedAt'];

    protected function generatePatientId(array $data)
    {
        if (!isset($data['data']['patient_id']) || empty($data['data']['patient_id'])) {
            do {
                // Generate a random 8-character alphanumeric ID
                $randomId = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
                // Format as XXXX-XXXX
                $data['data']['patient_id'] = substr($randomId, 0, 4) . '-' . substr($randomId, 4, 4);
            } while ($this->where('patient_id', $data['data']['patient_id'])->countAllResults() > 0);
        }

        return $data;
    }

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
