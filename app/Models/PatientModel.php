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
        'bed_id',
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
        'bed_id' => 'permit_empty|integer',
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

    /**
     * Get patient statistics
     */
    public function getPatientStatistics(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['start_date'])) {
            $builder->where('created_at >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('created_at <=', $filters['end_date'] . ' 23:59:59');
        }

        if (!empty($filters['patient_type'])) {
            $builder->where('type', $filters['patient_type']);
        }

        if (!empty($filters['gender'])) {
            $builder->where('gender', $filters['gender']);
        }

        $patients = $builder->get()->getResultArray();

        $stats = [
            'total_patients' => count($patients),
            'inpatient' => 0,
            'outpatient' => 0,
            'by_gender' => ['male' => 0, 'female' => 0, 'other' => 0],
            'by_age_group' => [
                '0-18' => 0,
                '19-35' => 0,
                '36-50' => 0,
                '51-65' => 0,
                '65+' => 0,
            ],
            'new_patients' => 0,
            'active_patients' => 0,
        ];

        $today = new \DateTime();
        foreach ($patients as $patient) {
            if ($patient['type'] === 'inpatient') {
                $stats['inpatient']++;
            } else {
                $stats['outpatient']++;
            }

            $gender = $patient['gender'] ?? 'other';
            if (isset($stats['by_gender'][$gender])) {
                $stats['by_gender'][$gender]++;
            }

            if (!empty($patient['date_of_birth'])) {
                $dob = new \DateTime($patient['date_of_birth']);
                $age = $today->diff($dob)->y;

                if ($age <= 18) {
                    $stats['by_age_group']['0-18']++;
                } elseif ($age <= 35) {
                    $stats['by_age_group']['19-35']++;
                } elseif ($age <= 50) {
                    $stats['by_age_group']['36-50']++;
                } elseif ($age <= 65) {
                    $stats['by_age_group']['51-65']++;
                } else {
                    $stats['by_age_group']['65+']++;
                }
            }

            if ($patient['status'] === 'active') {
                $stats['active_patients']++;
            }

            $created = new \DateTime($patient['created_at'] ?? date('Y-m-d'));
            if ($created->format('Y-m-d') >= ($filters['start_date'] ?? '2000-01-01')) {
                $stats['new_patients']++;
            }
        }

        return [
            'statistics' => $stats,
            'patients' => $patients,
        ];
    }

    /**
     * Get patient medical history
     */
    public function getPatientHistory(string $patientId, string $startDate = '', string $endDate = ''): array
    {
        $patient = $this->find($patientId);
        if (!$patient) {
            return [];
        }

        $db = \Config\Database::connect();

        // Get appointments
        $appointmentBuilder = $db->table('appointments')
            ->where('patient_id', $patientId);
        if ($startDate) {
            $appointmentBuilder->where('appointment_date >=', $startDate);
        }
        if ($endDate) {
            $appointmentBuilder->where('appointment_date <=', $endDate);
        }
        $appointments = $appointmentBuilder->orderBy('appointment_date', 'DESC')->get()->getResultArray();

        // Get prescriptions
        $prescriptionBuilder = $db->table('prescriptions')
            ->where('patient_id', $patientId);
        if ($startDate) {
            $prescriptionBuilder->where('created_at >=', $startDate);
        }
        if ($endDate) {
            $prescriptionBuilder->where('created_at <=', $endDate . ' 23:59:59');
        }
        $prescriptions = $prescriptionBuilder->orderBy('created_at', 'DESC')->get()->getResultArray();

        // Get laboratory tests
        $labBuilder = $db->table('laboratory')
            ->where('patient_id', $patientId);
        if ($startDate) {
            $labBuilder->where('test_date >=', $startDate);
        }
        if ($endDate) {
            $labBuilder->where('test_date <=', $endDate);
        }
        $laboratory = $labBuilder->orderBy('test_date', 'DESC')->get()->getResultArray();

        // Get admissions
        $admissionBuilder = $db->table('admissions')
            ->where('patient_id', $patientId);
        if ($startDate) {
            $admissionBuilder->where('admission_date >=', $startDate);
        }
        if ($endDate) {
            $admissionBuilder->where('admission_date <=', $endDate);
        }
        $admissions = $admissionBuilder->orderBy('admission_date', 'DESC')->get()->getResultArray();

        // Get bills
        $billingBuilder = $db->table('billing')
            ->where('patient_id', $patientId);
        if ($startDate) {
            $billingBuilder->where('bill_date >=', $startDate);
        }
        if ($endDate) {
            $billingBuilder->where('bill_date <=', $endDate);
        }
        $bills = $billingBuilder->orderBy('bill_date', 'DESC')->get()->getResultArray();

        // Get vital signs
        $vitalBuilder = $db->table('patient_vitals')
            ->where('patient_id', $patientId);
        if ($startDate) {
            $vitalBuilder->where('recorded_at >=', $startDate);
        }
        if ($endDate) {
            $vitalBuilder->where('recorded_at <=', $endDate . ' 23:59:59');
        }
        $vitals = $vitalBuilder->orderBy('recorded_at', 'DESC')->get()->getResultArray();

        return [
            'patient' => $patient,
            'appointments' => $appointments,
            'prescriptions' => $prescriptions,
            'laboratory' => $laboratory,
            'admissions' => $admissions,
            'bills' => $bills,
            'vitals' => $vitals,
        ];
    }
}

