<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\UserModel;

class Patients extends BaseController
{
    protected $patientModel;
    protected $userModel;
    
    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        helper(['form', 'url', 'auth']);
    }

    public function register()
    {
        $data = [
            'title' => 'Register New Patient',
            'validation' => \Config\Services::validation()
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
            'medical_history' => 'permit_empty|string',
            'admission_date' => 'permit_empty|valid_date',
            'admission_time' => 'permit_empty',
            'admission_type' => 'permit_empty|in_list[emergency,elective,transfer]',
            'attending_physician' => 'permit_empty|string|max_length[20]|is_not_unique[users.id]',
            'bed_id' => 'permit_empty|integer|is_not_unique[beds.id]',
            'admitting_diagnosis' => 'permit_empty|string',
            'reason_admission' => 'permit_empty|string',
            'vitals_bp' => 'permit_empty|string|max_length[20]',
            'vitals_hr' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[300]',
            'vitals_temp' => 'permit_empty|decimal'
        ];

        $type = $this->request->getPost('type') ?? 'outpatient';

        // Additional rules for inpatient admissions only
        if ($type === 'inpatient') {
            $rules = array_merge($rules, [
                'middle_name' => 'permit_empty|max_length[100]',
                'name_extension' => 'permit_empty|max_length[10]',
                'civil_status' => 'required|in_list[single,married,widowed,separated,divorced]',
                'place_of_birth' => 'required|max_length[255]',
                'province' => 'required|max_length[255]',
                'city' => 'required|max_length[255]',
                'barangay' => 'required|max_length[255]',
                'admission_date' => 'required|valid_date',
                'admission_time' => 'required',
                'admission_type' => 'required|in_list[emergency,elective,transfer]',
                'attending_physician' => 'required|string|max_length[20]|is_not_unique[users.id]',
                'bed_id' => 'required|integer|is_not_unique[beds.id]',
                'admitting_diagnosis' => 'required|max_length[500]',
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
        $bedId = $this->request->getPost('bed_id');
        $bedId = ($bedId !== null && $bedId !== '') ? (int) $bedId : null;

        $attendingPhysician = $this->request->getPost('attending_physician');
        $attendingPhysician = ($attendingPhysician !== null && $attendingPhysician !== '') ? (string) $attendingPhysician : null;

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
            'bed_id' => $bedId,
            'admission_date' => $this->request->getPost('admission_date') ?: null,
            'admission_time' => $this->request->getPost('admission_time') ?: null,
            'admission_type' => $this->request->getPost('admission_type') ?: null,
            'attending_physician' => $attendingPhysician,
            'blood_type' => $this->request->getPost('blood_type'),
            'insurance_provider' => $this->request->getPost('insurance_provider'),
            'insurance_number' => $this->request->getPost('insurance_number'),
            'admitting_diagnosis' => $this->request->getPost('admitting_diagnosis') ?: null,
            'reason_admission' => $this->request->getPost('reason_admission') ?: null,
            'vitals_bp' => $this->request->getPost('vitals_bp') ?: null,
            'vitals_hr' => $this->request->getPost('vitals_hr') ?: null,
            'vitals_temp' => $this->request->getPost('vitals_temp') ?: null,
            'medical_history' => $this->request->getPost('medical_history'),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Handle emergency contact - store in separate columns
        if ($type === 'inpatient') {
            $data['emergency_contact_person'] = $this->request->getPost('emergency_contact_person');
            $data['emergency_contact_relationship'] = $this->request->getPost('emergency_contact_relationship');
            $data['emergency_contact_phone'] = $this->request->getPost('emergency_contact_phone');
            $data['emergency_contact'] = $data['emergency_contact_phone'];
        } else {
            $data['emergency_contact_person'] = $this->request->getPost('emergency_contact_person') ?: null;
            $data['emergency_contact_relationship'] = $this->request->getPost('emergency_contact_relationship') ?: null;
            $data['emergency_contact_phone'] = $this->request->getPost('emergency_contact_phone') ?: null;
            $data['emergency_contact'] = $this->request->getPost('emergency_contact') ?: $data['emergency_contact_phone'];
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

    // Other methods will be added here
    public function index()
    {
        $data = [
            'title' => 'Manage Patients',
            'patients' => $this->patientModel->findAll()
        ];
        
        return view('Roles/admin/patients/view', $data);
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
}
