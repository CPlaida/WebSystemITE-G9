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
            'address' => 'permit_empty|string|max_length[255]',
            'blood_type' => 'permit_empty|string|max_length[10]',
            'emergency_contact' => 'permit_empty|string|max_length[100]',
            'insurance_provider' => 'permit_empty|string|max_length[255]',
            'insurance_number' => 'permit_empty|string|max_length[100]',
            'medical_history' => 'permit_empty|string'
        ];

        $type = $this->request->getPost('type') ?? 'outpatient';

        // Additional rules for inpatient admissions only
        if ($type === 'inpatient') {
            $rules = array_merge($rules, [
                'middle_name' => 'permit_empty|max_length[100]',
                'name_extension' => 'permit_empty|max_length[10]',
                'civil_status' => 'required|in_list[single,married,widowed,separated,divorced]',
                'place_of_birth' => 'required|max_length[255]',
                'admitting_diagnosis' => 'required|max_length[500]',
                'attending_physician' => 'required',
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
        $emergencyContact = $this->request->getPost('emergency_contact');
        if ($type === 'inpatient') {
            // For inpatients, store the primary emergency contact number in the existing column
            $emergencyContact = $this->request->getPost('emergency_contact_phone');
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'gender' => $this->request->getPost('gender'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'address' => $this->request->getPost('address'),
            'type' => $type,
            'ward' => $this->request->getPost('ward'),
            'room' => $this->request->getPost('room'),
            'bed' => $this->request->getPost('bed'),
            'blood_type' => $this->request->getPost('blood_type'),
            'emergency_contact' => $emergencyContact,
            'insurance_provider' => $this->request->getPost('insurance_provider'),
            'insurance_number' => $this->request->getPost('insurance_number'),
            'medical_history' => $this->request->getPost('medical_history'),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

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
        
        return view('Roles/admin/patients/index', $data);
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
