<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AppointmentModel;
use App\Models\DoctorModel;
use App\Models\PatientModel;

class Appointment extends BaseController
{
    protected $appointmentModel;
    protected $doctorModel;
    protected $patientModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->doctorModel = new DoctorModel();
        $this->patientModel = new PatientModel();
    }

    /**
     * Display appointment list
     */
    public function index()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return redirect()->to('login');
        }

        // Get appointments with details
        $appointments = $this->appointmentModel->getAppointmentsWithDetails();

        $data = [
            'title' => 'Appointment List',
            'active_menu' => 'appointments',
            'appointments' => $appointments
        ];
        
        return view('admin/appointments/Appointmentlist', $data);
    }

    /**
     * Show appointment booking form
     */
    public function book()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return redirect()->to('login');
        }

        // Get doctors for dropdown
        $doctors = $this->doctorModel->getAllDoctors();

        $data = [
            'title' => 'Book Appointment',
            'active_menu' => 'appointments',
            'doctors' => $doctors
        ];
        
        return view('admin/appointments/Bookappointment', $data);
    }

    /**
     * Create new appointment
     */
    public function create()
    {
        log_message('info', 'Appointment create method called');
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));
        log_message('info', 'Session role: ' . session('role'));
        
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!session('user_id') || !in_array(session('role'), $allowedRoles)) {
            log_message('error', 'Unauthorized access attempt by role: ' . session('role') . ', user_id: ' . session('user_id'));
            return redirect()->to('/login')->with('error', 'Please login to book appointments');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'patient_name' => 'required|min_length[2]',
            'doctor_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'appointment_type' => 'required|in_list[consultation,follow_up,emergency,routine_checkup]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            log_message('error', 'Validation failed: ' . json_encode($validation->getErrors()));
            return redirect()->back()->withInput()->with('error', 'Please fill all required fields correctly.');
        }
        
        log_message('info', 'Validation passed, proceeding with appointment creation');

        // Check for appointment conflicts
        $doctorId = $this->request->getPost('doctor_id');
        $date = $this->request->getPost('appointment_date');
        $time = $this->request->getPost('appointment_time');

        if ($this->appointmentModel->checkAppointmentConflict($doctorId, $date, $time)) {
            log_message('info', 'Appointment conflict detected for doctor ' . $doctorId . ' on ' . $date . ' at ' . $time);
            return redirect()->back()->withInput()->with('error', 'This time slot is already booked. Please choose another time.');
        }
        
        log_message('info', 'No appointment conflict, proceeding with patient creation/lookup');

        // Create or find patient
        $patientName = $this->request->getPost('patient_name');
        $patient = $this->patientModel->where('first_name', $patientName)->orWhere('last_name', $patientName)->first();
        
        if (!$patient) {
            // Create new patient record
            $patientData = [
                'patient_id' => 'PAT' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'first_name' => $patientName,
                'last_name' => '',
                'phone' => $this->request->getPost('phone') ?? '',
                'email' => $this->request->getPost('email') ?? '',
                'date_of_birth' => date('Y-m-d', strtotime('-30 years')), // Default DOB
                'gender' => 'other', // Default gender
                'address' => ''
            ];
            log_message('info', 'Creating new patient: ' . json_encode($patientData));
            $patientId = $this->patientModel->insert($patientData);
            if (!$patientId) {
                log_message('error', 'Failed to create patient: ' . json_encode($this->patientModel->errors()));
                return redirect()->back()->withInput()->with('error', 'Failed to create patient record.');
            }
            log_message('info', 'Patient created successfully with ID: ' . $patientId);
        } else {
            $patientId = $patient['id'];
        }

        // Generate appointment ID
        $appointmentId = 'APT' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $appointmentData = [
            'appointment_id' => $appointmentId,
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'appointment_type' => $this->request->getPost('appointment_type'),
            'reason' => $this->request->getPost('reason'),
            'status' => 'scheduled'
        ];

        log_message('info', 'Attempting to insert appointment: ' . json_encode($appointmentData));
        
        $insertResult = $this->appointmentModel->insert($appointmentData);
        log_message('info', 'Insert result: ' . ($insertResult ? 'SUCCESS - ID: ' . $insertResult : 'FAILED'));
        
        if ($insertResult) {
            log_message('info', 'Appointment created successfully: ' . $appointmentId);
            return redirect()->to('appointments/list')->with('success', 'Appointment booked successfully!');
        } else {
            $errors = $this->appointmentModel->errors();
            log_message('error', 'Failed to create appointment: ' . json_encode($errors));
            log_message('error', 'Appointment data: ' . json_encode($appointmentData));
            log_message('error', 'Database error: ' . $this->appointmentModel->db->error());
            return redirect()->back()->withInput()->with('error', 'Failed to book appointment: ' . implode(', ', $errors));
        }
    }

    /**
     * Show appointment details
     */
    public function show($id)
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $appointment = $this->appointmentModel->getAppointmentWithDetails($id);
        
        if (!$appointment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Appointment not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'appointment' => $appointment
        ]);
    }

    /**
     * Update appointment
     */
    public function update($id)
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Appointment not found'
            ]);
        }

        $input = $this->request->getJSON(true);
        $updateData = [];

        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }
        if (isset($input['notes'])) {
            $updateData['notes'] = $input['notes'];
        }

        if ($this->appointmentModel->update($id, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update appointment',
                'errors' => $this->appointmentModel->errors()
            ]);
        }
    }

    /**
     * Cancel appointment
     */
    public function cancel($id)
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $notes = $this->request->getPost('notes');
        
        if ($this->appointmentModel->updateAppointmentStatus($id, 'cancelled', $notes)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment cancelled successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to cancel appointment'
            ]);
        }
    }

    /**
     * Mark appointment as completed
     */
    public function complete($id)
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $notes = $this->request->getPost('notes');
        
        if ($this->appointmentModel->updateAppointmentStatus($id, 'completed', $notes)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment marked as completed'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to complete appointment'
            ]);
        }
    }

    /**
     * Mark appointment as no-show
     */
    public function noShow($id)
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $notes = $this->request->getPost('notes');
        
        if ($this->appointmentModel->updateAppointmentStatus($id, 'no_show', $notes)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment marked as no-show'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to mark appointment as no-show'
            ]);
        }
    }

    /**
     * Delete appointment
     */
    public function delete($id)
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $appointment = $this->appointmentModel->find($id);
        if (!$appointment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Appointment not found'
            ]);
        }

        if ($this->appointmentModel->delete($id)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment deleted successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete appointment'
            ]);
        }
    }

    public function schedule()
    {
        // Check if user is logged in and has admin role
        if (session('role') !== 'admin') {            
            return redirect()->to('login');
        }

        $data = [
            'title' => 'Staff Schedule',
            'active_menu' => 'appointments'
        ];
        
        return view('admin/appointments/StaffSchedule', $data);
    }
}
