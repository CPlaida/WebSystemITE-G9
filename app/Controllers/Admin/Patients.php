<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Models\Financial\HmoProviderModel;
use App\Models\AdmissionModel;

class Patients extends BaseController
{
    protected $patientModel;
    protected $userModel;
    protected $hmoProviderModel;
    protected $admissionModel;
    
    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->hmoProviderModel = new HmoProviderModel();
        $this->admissionModel = new AdmissionModel();
        helper(['form', 'url', 'auth']);
    }

    public function register()
    {
        $data = [
            'title' => 'Register New Patient',
            'validation' => \Config\Services::validation(),
            'hmoProviders' => $this->hmoProviderModel
                ->where('active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ];
        
        return view('Roles/admin/patients/register', $data);
    }

    public function processRegister()
    {
        // Only allow AJAX requests
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        // Validate input
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => 'required|valid_email|is_unique[patients.email]',
            'phone' => 'required|min_length[10]|max_length[15]',
            'gender' => 'required|in_list[male,female,other]',
            'date_of_birth' => 'required|valid_date',
            'civil_status' => 'permit_empty|in_list[single,married,widowed,separated,divorced]',
            'place_of_birth' => 'permit_empty|max_length[255]',
            'address' => 'permit_empty|string|max_length[255]',
            'province' => 'permit_empty|max_length[255]',
            'province_code' => 'permit_empty|max_length[10]',
            'city' => 'permit_empty|max_length[255]',
            'city_code' => 'permit_empty|max_length[10]',
            'barangay' => 'permit_empty|max_length[255]',
            'barangay_code' => 'permit_empty|max_length[10]',
            'street' => 'permit_empty|max_length[255]',
            'blood_type' => 'permit_empty|string|max_length[10]',
            'emergency_contact' => 'permit_empty|string|max_length[100]',
            'insurance_provider' => 'permit_empty|string|max_length[255]',
            'insurance_number' => 'permit_empty|string|max_length[100]',
            'hmo_provider_id' => 'permit_empty|integer',
            'hmo_member_no' => 'permit_empty|string|max_length[100]',
            'hmo_valid_from' => 'permit_empty|valid_date',
            'hmo_valid_to' => 'permit_empty|valid_date',
            'medical_history' => 'permit_empty|string',
            'vitals_bp' => 'permit_empty|string|max_length[20]',
            'vitals_hr' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[300]',
            'vitals_temp' => 'permit_empty|decimal'
        ];

        $type = $this->request->getPost('type') ?? 'outpatient';

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors(),
                'message' => 'Please fix the following errors'
            ])->setStatusCode(422);
        }

        // Prepare patient data
        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'middle_name' => $this->request->getPost('middle_name') ?: null,
            'last_name' => $this->request->getPost('last_name'),
            'name_extension' => $this->request->getPost('name_extension') ?: null,
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'gender' => $this->request->getPost('gender'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'civil_status' => $this->request->getPost('civil_status') ?: null,
            'place_of_birth' => $this->request->getPost('place_of_birth') ?: null,
            'address' => $this->request->getPost('address'),
            'province' => $this->request->getPost('province') ?: null,
            'province_code' => $this->request->getPost('province_code') ?: null,
            'city' => $this->request->getPost('city') ?: null,
            'city_code' => $this->request->getPost('city_code') ?: null,
            'barangay' => $this->request->getPost('barangay') ?: null,
            'barangay_code' => $this->request->getPost('barangay_code') ?: null,
            'street' => $this->request->getPost('street') ?: null,
            'type' => $type,
            'blood_type' => $this->request->getPost('blood_type'),
            'insurance_provider' => $this->request->getPost('insurance_provider'),
            'insurance_number' => $this->request->getPost('insurance_number'),
            'hmo_provider_id' => $this->request->getPost('hmo_provider_id') ?: null,
            'hmo_member_no' => $this->request->getPost('hmo_member_no') ?: null,
            'hmo_valid_from' => $this->request->getPost('hmo_valid_from') ?: null,
            'hmo_valid_to' => $this->request->getPost('hmo_valid_to') ?: null,
            'medical_history' => $this->request->getPost('medical_history'),
            'vitals_bp' => $this->request->getPost('vitals_bp') ?: null,
            'vitals_hr' => $this->request->getPost('vitals_hr') ?: null,
            'vitals_temp' => $this->request->getPost('vitals_temp') ?: null,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Handle emergency contact - store in separate columns
        // Emergency contact (kept simple; not tied to admission)
        $data['emergency_contact_person'] = $this->request->getPost('emergency_contact_person') ?: null;
        $data['emergency_contact_relationship'] = $this->request->getPost('emergency_contact_relationship') ?: null;
        $data['emergency_contact_phone'] = $this->request->getPost('emergency_contact_phone') ?: null;
        $data['emergency_contact'] = $this->request->getPost('emergency_contact') ?: $data['emergency_contact_phone'];

        if ($this->patientModel->save($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Patient registered successfully!',
                'data' => [
                    'id' => $this->patientModel->getInsertID()
                ]
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to register patient. Please try again.'
        ])->setStatusCode(500);
    }

    // Other methods will be added here
    public function index()
    {
        $filter = $this->request->getGet('filter') ?? 'inpatient';
        $allowedFilters = ['inpatient', 'outpatient', 'admitted', 'discharged'];
        $filter = in_array($filter, $allowedFilters, true) ? $filter : 'inpatient';

        if ($filter === 'admitted') {
            $patients = $this->getAdmittedPatients();
        } elseif ($filter === 'discharged') {
            $patients = $this->getDischargedPatients();
        } else {
            $patients = $this->patientModel
                ->where('type', $filter === 'outpatient' ? 'outpatient' : 'inpatient')
                ->orderBy('last_name', 'ASC')
                ->orderBy('first_name', 'ASC')
                ->findAll();
        }

        return view('Roles/admin/patients/view', [
            'title' => 'Patient Records',
            'patients' => $patients,
            'currentFilter' => $filter,
        ]);
    }

    public function dischargedTable()
    {
        $patients = $this->getDischargedPatients();

        return view('Roles/admin/patients/partials/discharged_table', [
            'patients' => $patients,
            'currentFilter' => 'discharged',
        ]);
    }

    public function inpatient()
    {
        $doctors = $this->userModel
            ->select("users.id, COALESCE(NULLIF(CONCAT(d.first_name, ' ', d.last_name), ' '), users.username) AS display_name, d.first_name, d.last_name, users.username")
            ->join('roles r', 'users.role_id = r.id', 'left')
            ->join('doctors d', 'd.user_id = users.id', 'left')
            ->where('r.name', 'doctor')
            ->where('users.status', 'active')
            ->orderBy('d.first_name', 'ASC')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Register Inpatient',
            'validation' => \Config\Services::validation(),
            'doctors' => $doctors,
            'hmoProviders' => $this->hmoProviderModel
                ->where('active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ];

        return view('Roles/admin/patients/Inpatient', $data);
    }

    public function view($id = null)
    {
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }

        $data = [
            'title' => 'View Patient',
            'patient' => $patient
        ];
        
        return view('Roles/admin/patients/view', $data);
    }

    protected function countPatientsByType(string $type): int
    {
        return (int) $this->patientModel->where('type', $type)->countAllResults();
    }

    protected function countAdmittedPatients(): int
    {
        return count($this->getAdmittedPatients());
    }

    protected function getAdmittedPatients(): array
    {
        $rows = $this->admissionModel
            ->select([
                'admission_details.id AS admission_id',
                'admission_details.patient_id AS admission_patient_id',
                'admission_details.admission_date',
                'admission_details.admission_time',
                'admission_details.admission_type',
                'admission_details.status AS admission_status',
                'admission_details.admitting_diagnosis',
                'admission_details.reason_admission',
                'admission_details.ward AS admission_ward',
                'admission_details.room AS admission_room',
                'admission_details.bed_id',
                'patients.*',
                'beds.ward AS bed_ward',
                'beds.room AS bed_room',
                'beds.bed AS bed_label',
                'users.username AS physician_username',
                "COALESCE(CONCAT(doctors.first_name, ' ', doctors.last_name), users.username) AS physician_name",
                'role.name AS physician_role',
            ])
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->join('beds', 'beds.id = admission_details.bed_id', 'left')
            ->join('users', 'users.id = admission_details.attending_physician', 'left')
            ->join('doctors', 'doctors.user_id = users.id', 'left')
            ->join('roles role', 'role.id = users.role_id', 'left')
            ->where('admission_details.status', 'admitted')
            ->orderBy('CASE WHEN role.name = "doctor" THEN 0 ELSE 1 END', 'ASC', false)
            ->orderBy('admission_details.created_at', 'DESC')
            ->orderBy('admission_details.id', 'DESC')
            ->orderBy('admission_details.admission_date', 'DESC')
            ->orderBy('admission_details.admission_time', 'DESC')
            ->findAll();

        if (empty($rows)) {
            return [];
        }

        $unique = [];
        $seen = [];
        foreach ($rows as $row) {
            $patientId = $row['admission_patient_id'] ?? ($row['patient_id'] ?? ($row['id'] ?? null));
            if (!$patientId || isset($seen[$patientId])) {
                continue;
            }
            $seen[$patientId] = true;
            $unique[] = $row;
        }

        return $unique;
    }

    protected function getDischargedPatients(): array
    {
        $rows = $this->admissionModel
            ->select([
                'admission_details.id AS admission_id',
                'admission_details.patient_id AS admission_patient_id',
                'admission_details.admission_date',
                'admission_details.updated_at AS discharge_date',
                'admission_details.admission_type',
                'admission_details.status AS admission_status',
                'admission_details.admitting_diagnosis',
                'admission_details.reason_admission',
                'admission_details.ward AS admission_ward',
                'admission_details.room AS admission_room',
                'patients.*',
                'users.username AS physician_username',
                "COALESCE(CONCAT(doctors.first_name, ' ', doctors.last_name), users.username) AS physician_name",
                'role.name AS physician_role',
            ])
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->join('users', 'users.id = admission_details.attending_physician', 'left')
            ->join('doctors', 'doctors.user_id = users.id', 'left')
            ->join('roles role', 'role.id = users.role_id', 'left')
            ->where('admission_details.status', 'discharged')
            ->orderBy('admission_details.updated_at', 'DESC')
            ->orderBy('admission_details.id', 'DESC')
            ->findAll();

        if (empty($rows)) {
            return [];
        }

        $unique = [];
        $seen = [];
        foreach ($rows as $row) {
            $patientId = $row['admission_patient_id'] ?? ($row['patient_id'] ?? ($row['id'] ?? null));
            if (!$patientId || isset($seen[$patientId])) {
                continue;
            }
            $seen[$patientId] = true;
            $unique[] = $row;
        }

        return $unique;
    }
}
