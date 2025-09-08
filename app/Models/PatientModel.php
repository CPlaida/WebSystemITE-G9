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
    protected $protectFields = true;
    protected $allowedFields = [
        'patient_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'blood_type',
        'emergency_contact',
        'medical_history',
        'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'first_name' => 'required|min_length[2]|max_length[100]',
        'last_name' => 'required|min_length[2]|max_length[100]',
        'phone' => 'required|min_length[10]|max_length[20]',
        'date_of_birth' => 'required|valid_date',
        'gender' => 'required|in_list[male,female,other]',
        'email' => 'permit_empty|valid_email|max_length[255]',
        'address' => 'permit_empty|max_length[500]',
        'blood_type' => 'permit_empty|max_length[5]',
        'emergency_contact' => 'permit_empty|max_length[20]',
        'medical_history' => 'permit_empty',
        'status' => 'permit_empty|in_list[active,inactive]'
    ];

    protected $validationMessages = [
        'first_name' => [
            'required' => 'First name is required',
            'min_length' => 'First name must be at least 2 characters long',
            'max_length' => 'First name cannot exceed 100 characters'
        ],
        'last_name' => [
            'required' => 'Last name is required',
            'min_length' => 'Last name must be at least 2 characters long',
            'max_length' => 'Last name cannot exceed 100 characters'
        ],
        'phone' => [
            'required' => 'Phone number is required',
            'min_length' => 'Phone number must be at least 10 digits',
            'max_length' => 'Phone number cannot exceed 20 characters'
        ],
        'date_of_birth' => [
            'required' => 'Date of birth is required',
            'valid_date' => 'Please enter a valid date'
        ],
        'gender' => [
            'required' => 'Gender is required',
            'in_list' => 'Please select a valid gender option'
        ],
        'email' => [
            'valid_email' => 'Please enter a valid email address'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['generatePatientId'];
    protected $beforeUpdate = [];

    /**
     * Generate unique patient ID before inserting
     */
    protected function generatePatientId(array $data)
    {
        if (!isset($data['data']['patient_id'])) {
            $data['data']['patient_id'] = $this->createUniquePatientId();
        }
        return $data;
    }

    /**
     * Create a unique patient ID
     */
    private function createUniquePatientId()
    {
        do {
            // Generate patient ID in format: P-YYYYMMDD-XXXX
            $date = date('Ymd');
            $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $patientId = "P-{$date}-{$random}";
        } while ($this->where('patient_id', $patientId)->first());

        return $patientId;
    }

    /**
     * Get patient by patient ID
     */
    public function getByPatientId($patientId)
    {
        return $this->where('patient_id', $patientId)->first();
    }

    /**
     * Get active patients
     */
    public function getActivePatients()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Search patients by name or patient ID
     */
    public function searchPatients($searchTerm)
    {
        return $this->groupStart()
                    ->like('first_name', $searchTerm)
                    ->orLike('last_name', $searchTerm)
                    ->orLike('patient_id', $searchTerm)
                    ->groupEnd()
                    ->findAll();
    }

    /**
     * Get patient statistics
     */
    public function getPatientStats()
    {
        return [
            'total' => $this->countAll(),
            'active' => $this->where('status', 'active')->countAllResults(),
            'inactive' => $this->where('status', 'inactive')->countAllResults(),
            'recent' => $this->orderBy('created_at', 'DESC')->limit(5)->findAll()
        ];
    }
}
