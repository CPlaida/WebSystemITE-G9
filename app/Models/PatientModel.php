<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientModel extends Model
{
    protected $table = 'patients';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    /**
     * Search patients by name and return minimal fields for autocomplete.
     */
    public function searchPatients(string $term): array
    {
        $term = trim($term);
        if ($term === '') return [];
        $builder = $this->builder();
        $builder->select("id, CONCAT(first_name, ' ', COALESCE(last_name, '')) AS name");
        $builder->groupStart()
            ->like('first_name', $term)
            ->orLike('last_name', $term)
            ->groupEnd();
        $builder->orderBy('first_name', 'ASC');
        $builder->limit(10);
        return $builder->get()->getResultArray();
    }

    protected $allowedFields = [
        'id',
        'first_name',
        'middle_name',
        'last_name',
        'name_extension',
        'email',
        'phone',
        'gender',
        'date_of_birth',
        'civil_status',
        'place_of_birth',
        'address',
        'province',
        'province_code',
        'city',
        'city_code',
        'barangay',
        'barangay_code',
        'street',
        'type',
        'blood_type',
        'emergency_contact',
        'emergency_contact_person',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'vitals_bp',
        'vitals_hr',
        'vitals_temp',
        'insurance_provider',
        'insurance_number',
        'hmo_provider_id',
        'hmo_member_no',
        'hmo_valid_from',
        'hmo_valid_to',
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
        'civil_status' => 'permit_empty|in_list[single,married,widowed,separated,divorced]',
        'place_of_birth' => 'permit_empty|max_length[255]',
        'province' => 'permit_empty|max_length[255]',
        'province_code' => 'permit_empty|max_length[10]',
        'city' => 'permit_empty|max_length[255]',
        'city_code' => 'permit_empty|max_length[10]',
        'barangay' => 'permit_empty|max_length[255]',
        'barangay_code' => 'permit_empty|max_length[10]',
        'street' => 'permit_empty|max_length[255]',
        'type' => 'permit_empty|in_list[inpatient,outpatient]',
        'insurance_provider' => 'permit_empty|string|max_length[255]',
        'insurance_number' => 'permit_empty|string|max_length[100]',
        'hmo_provider_id' => 'permit_empty|integer',
        'hmo_member_no' => 'permit_empty|string|max_length[100]',
        'hmo_valid_from' => 'permit_empty|valid_date',
        'hmo_valid_to' => 'permit_empty|valid_date',
        'vitals_bp' => 'permit_empty|string|max_length[20]',
        'vitals_hr' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[300]',
        'vitals_temp' => 'permit_empty|decimal',
        'status' => 'in_list[active,inactive]'
    ];

    protected $beforeInsert = ['assignStringId','setCreatedAt'];
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

    protected function assignStringId(array $data)
    {
        if (!isset($data['data']['id']) || empty($data['data']['id'])) {
            $prefix = 'PAT';
            $today = date('ymd');
            $like = $prefix . '-' . $today . '-%';
            $last = $this->where('id LIKE', $like)
                        ->orderBy('id', 'DESC')
                        ->first();
            $next = 1;
            if ($last && isset($last['id'])) {
                $parts = explode('-', $last['id']);
                $seq = end($parts);
                $next = (int)$seq + 1;
            }
            $data['data']['id'] = sprintf('%s-%s-%04d', $prefix, $today, $next);
        }
        return $data;
    }

    protected function storeOldBedAssignment(array $data)
    {
        return $data;
    }
}

