<?php

namespace App\Controllers;

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

    /**
     * Get role-based view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleMap = [
            'admin' => 'admin',
            'doctor' => 'admin', // Use admin view for doctors
            'nurse' => 'admin', // Use admin view for nurses
            'receptionist' => 'admin',
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/patients/{$viewName}";
    }

    /**
     * Lightweight JSON endpoint consumed by billing autocomplete.
     */
    public function search()
    {
        $term = trim((string)$this->request->getGet('term'));
        if ($term === '') {
            return $this->response->setJSON(['patients' => []]);
        }

        try {
            $results = $this->patientModel->searchPatients($term);
        } catch (\Throwable $e) {
            log_message('error', 'Patient search failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Unable to search patients right now.'
            ]);
        }

        return $this->response->setJSON(['patients' => $results]);
    }

    /**
     * Return a single patient record for insurance autofill/use in billing.
     */
    public function getPatient($id = null)
    {
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Patient ID is required']);
        }

        $patient = $this->patientModel->find($id);
        if (!$patient) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Patient not found']);
        }

        return $this->response->setJSON(['patient' => $patient]);
    }

    public function register()
    {
        // Only admin, nurse, and receptionist can register patients
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
        $data = [
            'title' => 'Register New Patient',
            'validation' => \Config\Services::validation(),
            'hmoProviders' => $this->hmoProviderModel
                ->where('active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ];
        
        return view($this->getRoleViewPath('register'), $data);
    }

    public function processRegister()
    {
        // Only admin, nurse, and receptionist can register patients
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
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
            'address' => 'permit_empty|string|max_length[255]',
            'blood_type' => 'permit_empty|string|max_length[10]',
            'emergency_contact' => 'permit_empty|string|max_length[100]',
            'insurance_provider' => 'permit_empty|string|max_length[255]',
            'insurance_number' => 'permit_empty|string|max_length[100]',
            'hmo_provider_id' => 'permit_empty|integer',
            'hmo_member_no' => 'permit_empty|string|max_length[100]',
            'hmo_valid_from' => 'permit_empty|valid_date',
            'hmo_valid_to' => 'permit_empty|valid_date',
            'medical_history' => 'permit_empty|string',
            'civil_status' => 'permit_empty|in_list[single,married,widowed,separated,divorced]',
            'place_of_birth' => 'permit_empty|max_length[255]',
            'province' => 'permit_empty|max_length[255]',
            'province_code' => 'permit_empty|max_length[10]',
            'city' => 'permit_empty|max_length[255]',
            'city_code' => 'permit_empty|max_length[10]',
            'barangay' => 'permit_empty|max_length[255]',
            'barangay_code' => 'permit_empty|max_length[10]',
            'street' => 'permit_empty|max_length[255]',
            'vitals_bp' => 'permit_empty|string|max_length[20]',
            'vitals_hr' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[300]',
            'vitals_temp' => 'permit_empty|decimal'
        ];

        $type = $this->request->getPost('type') ?? 'outpatient';

        // Additional rules for inpatient registrations only
        if ($type === 'inpatient') {
            $rules = array_merge($rules, [
                'middle_name' => 'permit_empty|max_length[100]',
                'name_extension' => 'permit_empty|max_length[10]',
                'civil_status' => 'required|in_list[single,married,widowed,separated,divorced]',
                'place_of_birth' => 'required|max_length[255]',
                'emergency_contact_person' => 'required|max_length[100]',
                'emergency_contact_relationship' => 'permit_empty|max_length[50]',
                'emergency_contact_phone' => 'required|min_length[10]|max_length[15]',
            ]);
        }

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
            'bed_id' => $this->request->getPost('bed_id') ?: null,
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
        if ($type === 'inpatient') {
            // For inpatients, save all three emergency contact fields
            $data['emergency_contact_person'] = $this->request->getPost('emergency_contact_person');
            $data['emergency_contact_relationship'] = $this->request->getPost('emergency_contact_relationship');
            $data['emergency_contact_phone'] = $this->request->getPost('emergency_contact_phone');
            // Keep emergency_contact for backward compatibility (store phone number)
            $data['emergency_contact'] = $this->request->getPost('emergency_contact_phone');
        } else {
            // For outpatients, use the old emergency_contact field if provided
            $data['emergency_contact'] = $this->request->getPost('emergency_contact');
            // Also save if emergency contact person/phone are provided
            $data['emergency_contact_person'] = $this->request->getPost('emergency_contact_person') ?: null;
            $data['emergency_contact_phone'] = $this->request->getPost('emergency_contact_phone') ?: null;
            $data['emergency_contact_relationship'] = $this->request->getPost('emergency_contact_relationship') ?: null;
        }

        // Save to database
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

    /**
     * Display patient list - unified for all roles
     */
    public function index()
    {
        // Role-based access control
        $role = session('role');
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        $this->requireRole($allowedRoles);

        $filter = $this->request->getGet('filter') ?? 'inpatient';
        $allowedFilters = ['inpatient', 'outpatient', 'admitted', 'discharged'];
        $filter = in_array($filter, $allowedFilters, true) ? $filter : 'inpatient';

        // Admin, doctor, nurse, receptionist see all patients
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

        return view($this->getRoleViewPath('view'), [
            'title' => 'Patient Records',
            'patients' => $patients,
            'currentFilter' => $filter,
        ]);
    }

    public function inpatient()
    {
        // Only admin, nurse, and receptionist can register inpatients
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
        $doctors = $this->userModel
            ->select("users.id, COALESCE(NULLIF(CONCAT(sp.first_name, ' ', sp.last_name), ' '), users.username) AS display_name, sp.first_name, sp.last_name, users.username")
            ->join('roles r', 'users.role_id = r.id', 'left')
            ->join('staff_profiles sp', 'sp.user_id = users.id', 'left')
            ->join('roles r2', 'r2.id = sp.role_id', 'left')
            ->where('r2.name', 'doctor')
            ->where('r.name', 'doctor')
            ->where('users.status', 'active')
            ->orderBy('sp.first_name', 'ASC')
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
        
        return view($this->getRoleViewPath('Inpatient'), $data);
    }

    public function view($id = null)
    {
        // Role-based access control
        $this->requireRole(['admin', 'doctor', 'nurse', 'receptionist']);
        
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }

        $data = [
            'title' => 'View Patient',
            'patient' => $patient
        ];
        
        return view($this->getRoleViewPath('view'), $data);
    }

    public function edit($id = null)
    {
        // Only admin, nurse, and receptionist can edit patients
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }

        $data = [
            'title' => 'Edit Patient',
            'validation' => \Config\Services::validation(),
            'patient' => $patient,
            'hmoProviders' => $this->hmoProviderModel
                ->where('active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ];
        
        return view($this->getRoleViewPath('register'), $data);
    }

    public function update($id = null)
    {
        // Only admin, nurse, and receptionist can update patients
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
        if (!$id) {
            return redirect()->to('/admin/patients')->with('error', 'Patient ID is required.');
        }

        $patient = $this->patientModel->find($id);
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }

        // Validate input (similar to processRegister but allow existing email)
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name' => 'required|min_length[2]|max_length[50]',
            'email' => "required|valid_email|is_unique[patients.email,id,{$id}]",
            'phone' => 'required|min_length[10]|max_length[15]',
            'gender' => 'required|in_list[male,female,other]',
            'date_of_birth' => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare update data (similar to processRegister)
        $updateData = [
            'first_name' => $this->request->getPost('first_name'),
            'middle_name' => $this->request->getPost('middle_name') ?: null,
            'last_name' => $this->request->getPost('last_name'),
            'name_extension' => $this->request->getPost('name_extension') ?: null,
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'gender' => $this->request->getPost('gender'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'address' => $this->request->getPost('address'),
            'blood_type' => $this->request->getPost('blood_type'),
            'insurance_provider' => $this->request->getPost('insurance_provider'),
            'insurance_number' => $this->request->getPost('insurance_number'),
            'hmo_provider_id' => $this->request->getPost('hmo_provider_id') ?: null,
            'hmo_member_no' => $this->request->getPost('hmo_member_no') ?: null,
            'hmo_valid_from' => $this->request->getPost('hmo_valid_from') ?: null,
            'hmo_valid_to' => $this->request->getPost('hmo_valid_to') ?: null,
            'medical_history' => $this->request->getPost('medical_history'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->patientModel->update($id, $updateData)) {
            return redirect()->to('/admin/patients')->with('success', 'Patient updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update patient. Please try again.');
    }

    public function delete($id = null)
    {
        // Only admin can delete patients
        $this->requireRole(['admin']);
        
        if (!$id) {
            return redirect()->to('/admin/patients')->with('error', 'Patient ID is required.');
        }

        $patient = $this->patientModel->find($id);
        if (!$patient) {
            return redirect()->to('/admin/patients')->with('error', 'Patient not found.');
        }

        // Soft delete by setting status to inactive instead of hard delete
        if ($this->patientModel->update($id, ['status' => 'inactive'])) {
            return redirect()->to('/admin/patients')->with('success', 'Patient deleted successfully!');
        }

        return redirect()->to('/admin/patients')->with('error', 'Failed to delete patient. Please try again.');
    }

    protected function countPatientsByType(string $type): int
    {
        return (int) $this->patientModel->where('type', $type)->countAllResults();
    }

    protected function countAdmittedPatients(): int
    {
        return count($this->getAdmittedPatients());
    }

    protected function getAdmittedPatients(?string $doctorId = null): array
    {
        $builder = $this->admissionModel
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
                'staff_profiles.id AS attending_doctor_id',
                'users.username AS physician_username',
                "COALESCE(NULLIF(CONCAT(TRIM(staff_profiles.first_name), ' ', TRIM(staff_profiles.last_name)), ' '), users.username, CONCAT('Doctor #', staff_profiles.id)) AS physician_name",
                'role.name AS physician_role',
            ])
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->join('beds', 'beds.id = admission_details.bed_id', 'left')
            ->join('staff_profiles', 'staff_profiles.id = admission_details.attending_doctor_id', 'left')
            ->join('users', 'users.id = staff_profiles.user_id', 'left')
            ->join('roles role', 'role.id = users.role_id', 'left')
            ->where('admission_details.status', 'admitted');
        
        // Filter by doctor if specified
        if ($doctorId) {
            $builder->where('admission_details.attending_doctor_id', $doctorId);
        }
        
        $rows = $builder->orderBy('CASE WHEN role.name = "doctor" THEN 0 ELSE 1 END', 'ASC', false)
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

    protected function getDischargedPatients(?string $doctorId = null): array
    {
        $builder = $this->admissionModel
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
                'staff_profiles.id AS attending_doctor_id',
                'users.username AS physician_username',
                "COALESCE(NULLIF(CONCAT(TRIM(staff_profiles.first_name), ' ', TRIM(staff_profiles.last_name)), ' '), users.username, CONCAT('Doctor #', staff_profiles.id)) AS physician_name",
                'role.name AS physician_role',
            ])
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->join('staff_profiles', 'staff_profiles.id = admission_details.attending_doctor_id', 'left')
            ->join('users', 'users.id = staff_profiles.user_id', 'left')
            ->join('roles role', 'role.id = users.role_id', 'left')
            ->where('admission_details.status', 'discharged');
        
        // Filter by doctor if specified
        if ($doctorId) {
            $builder->where('admission_details.attending_doctor_id', $doctorId);
        }
        
        $rows = $builder->orderBy('admission_details.updated_at', 'DESC')
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
