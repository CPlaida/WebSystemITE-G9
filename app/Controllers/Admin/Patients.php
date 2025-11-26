<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Models\Financial\HmoProviderModel;

class Patients extends BaseController
{
    protected $patientModel;
    protected $userModel;
    protected $hmoProviderModel;
    
    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->hmoProviderModel = new HmoProviderModel();
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
            'medical_history' => 'permit_empty|string'
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
}
