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
        'address',
        'type',
        'bed_id',
        'blood_type',
        'emergency_contact',
        'emergency_contact_person',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'insurance_provider',
        'insurance_number',
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
        'bed_id' => 'permit_empty|integer|is_not_unique[beds.id]',
        'insurance_provider' => 'permit_empty|string|max_length[255]',
        'insurance_number' => 'permit_empty|string|max_length[100]',
        'status' => 'in_list[active,inactive]'
    ];

    protected $beforeInsert = ['assignStringId','setCreatedAt'];
    protected $beforeUpdate = ['setUpdatedAt', 'storeOldBedAssignment'];
    protected $afterInsert = ['updateBedStatus'];
    protected $afterUpdate = ['updateBedStatus'];
    
    // Store old bed assignment for updates
    protected static $oldBedAssignment = [];

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

    /**
     * Store old bed assignment before update
     */
    protected function storeOldBedAssignment(array $data)
    {
        if (isset($data['data']['id'])) {
            $oldPatient = $this->find($data['data']['id']);
            if ($oldPatient) {
                self::$oldBedAssignment[$data['data']['id']] = [
                    'bed_id' => $oldPatient['bed_id'] ?? null,
                ];
            }
        }
        return $data;
    }

    /**
     * Update bed status when patient is assigned or unassigned from a bed
     */
    protected function updateBedStatus(array $data)
    {
        $bedModel = new BedModel();

        // Get the patient ID from result
        $patientId = $data['id'] ?? null;
        if (!$patientId) {
            return $data;
        }

        // Get the patient data
        $patient = $this->find($patientId);
        if (!$patient) {
            return $data;
        }

        // Check if this is an update and we have old bed assignment
        if (isset(self::$oldBedAssignment[$patientId])) {
            $oldBed = self::$oldBedAssignment[$patientId];
            $oldBedId = $oldBed['bed_id'] ?? null;
            
            // Get new bed assignment
            $newBedId = $patient['bed_id'] ?? null;
            
            // If old bed assignment exists and is different from new, free it
            if ($oldBedId && $oldBedId !== $newBedId) {
                $bedModel->where('id', $oldBedId)
                         ->set('status', 'Available')
                         ->update();
            }
            
            // Clear stored old assignment
            unset(self::$oldBedAssignment[$patientId]);
        }

        // Get current bed assignment
        $bedId = $patient['bed_id'] ?? null;
        $type = $patient['type'] ?? '';

        // Update bed status based on patient assignment
        if ($type === 'inpatient' && $bedId) {
            // Set bed status to Occupied for inpatients
            $bedModel->where('id', $bedId)
                     ->set('status', 'Occupied')
                     ->update();
        } elseif ($bedId) {
            // Patient is not inpatient or was unassigned - free the bed
            $bedModel->where('id', $bedId)
                     ->set('status', 'Available')
                     ->update();
        }

        return $data;
    }
}

