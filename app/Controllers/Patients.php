<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\PatientModel;

class Patients extends BaseController
{
    protected $patientModel;

    public function __construct()
    {
        $this->patientModel = new PatientModel();
    }

    public function register()
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('login');
        }

        if (session('role') !== 'admin') {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Register New Patient',
            'active_menu' => 'patients'
        ];

        return view('admin/patients/register', $data);
    }

    public function processRegister()
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('login');
        }

        if (session('role') !== 'admin') {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        // Skip CSRF validation for now - will be handled by CodeIgniter automatically
        // if (!$this->validate(['csrf_token' => 'required'])) {
        //     return redirect()->back()->with('error', 'Invalid request. Please try again.');
        // }

        // Get form data
        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => strtolower($this->request->getPost('gender')),
            'address' => $this->request->getPost('address'),
            'blood_type' => $this->request->getPost('blood_type'),
            'emergency_contact' => $this->request->getPost('emergency_contact'),
            'medical_history' => $this->request->getPost('medical_history'),
            'status' => 'active'
        ];

        // Validate the data
        if (!$this->patientModel->validate($data)) {
            $errors = $this->patientModel->errors();
            log_message('error', 'Patient validation errors: ' . json_encode($errors));
            return redirect()->back()
                           ->withInput()
                           ->with('errors', $errors);
        }

        try {
            // Insert patient data
            $patientId = $this->patientModel->insert($data);
            
            if ($patientId) {
                $patient = $this->patientModel->find($patientId);
                return redirect()->to('patients/view')
                               ->with('success', 'Patient registered successfully! Patient ID: ' . $patient['patient_id']);
            } else {
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Failed to register patient. Please try again.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Patient registration error: ' . $e->getMessage());
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'An error occurred while registering the patient. Please try again.');
        }
    }

    public function view()
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('login');
        }

        // Check if user has admin role
        if (session('role') !== 'admin') {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        // Get all patients
        $patients = $this->patientModel->orderBy('created_at', 'DESC')->findAll();

        $data = [
            'title' => 'View Patients',
            'active_menu' => 'patients',
            'patients' => $patients
        ];

        return view('admin/patients/view', $data);
    }

    public function search()
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('login');
        }

        if (session('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Unauthorized access']);
        }

        $searchTerm = $this->request->getGet('term');
        
        if (empty($searchTerm)) {
            return $this->response->setJSON(['patients' => []]);
        }

        $patients = $this->patientModel->searchPatients($searchTerm);
        
        return $this->response->setJSON(['patients' => $patients]);
    }

    public function getPatient($id)
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('login');
        }

        if (session('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Unauthorized access']);
        }

        $patient = $this->patientModel->find($id);
        
        if (!$patient) {
            return $this->response->setJSON(['error' => 'Patient not found']);
        }

        return $this->response->setJSON(['patient' => $patient]);
    }
}
