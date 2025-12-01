<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\AppointmentModel;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Models\DoctorScheduleModel;

class Appointment extends BaseController
{
    protected $appointmentModel;
    protected $patientModel;
    protected $userModel;
    protected $doctorScheduleModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->patientModel = new PatientModel();
        $this->userModel = new UserModel();
        $this->doctorScheduleModel = new DoctorScheduleModel();
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

        $filter = $this->request->getGet('filter') ?? 'today';
        $date   = $this->request->getGet('date');
        $today  = date('Y-m-d'); // server/app timezone

        if ($filter === 'all') {
            $appointments = $this->appointmentModel->getAppointmentsWithDetails();
        } elseif ($filter === 'date' && $date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $appointments = $this->appointmentModel->getAppointmentsByDateRange($date, $date);
        } else { // default: today
            $appointments = $this->appointmentModel->getAppointmentsByDateRange($today, $today);
            $filter = 'today';
            $date = $today;
        }

        $data = [
            'title' => "Appointment List",
            'active_menu' => 'appointments',
            'appointments' => $appointments,
            'currentFilter' => $filter,
            'currentDate' => $date ?? $today,
        ];

        return view('Roles/admin/appointments/Appointmentlist', $data);
    }

    /**
     * Doctor-facing appointment list (defaults to today's appointments for logged-in doctor).
     */
    public function doctorToday()
    {
        // Only doctors can access this view
        if (session('role') !== 'doctor') {
            return redirect()->to('login');
        }

        $doctorId = (string) session('user_id');
        if ($doctorId === '') {
            return redirect()->to('/dashboard')->with('error', 'Unable to resolve doctor account.');
        }

        $filter = $this->request->getGet('filter') ?? 'today';
        $date   = $this->request->getGet('date');
        $today  = date('Y-m-d');

        if ($filter === 'all') {
            $appointments = $this->appointmentModel->getUnifiedList($doctorId, null);
        } elseif ($filter === 'date' && $date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $appointments = $this->appointmentModel->getUnifiedList($doctorId, $date);
        } else {
            // Default: today's appointments for this doctor
            $appointments = $this->appointmentModel->getUnifiedList($doctorId, $today);
            $filter = 'today';
            $date = $today;
        }

        $data = [
            'title'          => "My Appointments",
            'active_menu'    => 'appointments',
            'appointments'   => $appointments,
            'currentFilter'  => $filter,
            'currentDate'    => $date ?? $today,
        ];

        return view('Roles/doctor/appointments/Appointmentlist', $data);
    }

    /**
     * Show book appointment form
     */
    public function book()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return redirect()->to('login');
        }

        // Get doctors from users table via roles join (users.role removed)
        $doctors = $this->userModel
            ->select('users.*')
            ->join('roles r', 'users.role_id = r.id', 'left')
            ->where('r.name', 'doctor')
            ->where('users.status', 'active')
            ->findAll();

        $data = [
            'title' => 'Book Appointment',
            'active_menu' => 'appointments',
            'patients' => $this->patientModel->findAll(),
            'doctors' => $doctors
        ];
        
        return view('Roles/admin/appointments/Bookappointment', $data);
    }

    /**
     * JSON: Get available schedule dates (distinct shift_date >= today)
     */
    public function getAvailableDates()
    {
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $today = date('Y-m-d');
        $db = \Config\Database::connect();
        $rows = $db->table('doctor_schedules ds')
            ->select('ds.shift_date')
            ->join('users u', 'ds.doctor_id = u.id', 'left')
            ->where('ds.shift_date >=', $today)
            ->where('ds.status !=', 'cancelled')
            ->where('u.status', 'active')
            ->distinct()
            ->orderBy('ds.shift_date', 'ASC')
            ->get()->getResultArray();

        $dates = array_values(array_unique(array_map(function($r){ return $r['shift_date']; }, $rows)));
        return $this->response->setJSON(['success' => true, 'dates' => $dates]);
    }

    /**
     * JSON: Get doctors by selected date
     */
    public function getDoctorsByDate()
    {
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $date = $this->request->getGet('date');
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid date'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $rows = $db->table('doctor_schedules ds')
            ->select("ds.doctor_id, COALESCE(u.username, ds.doctor_name) AS name, u.email")
            ->join('users u', 'u.id = ds.doctor_id', 'left')
            ->where('ds.shift_date', $date)
            ->where('ds.status !=', 'cancelled')
            ->where('u.status', 'active')
            ->groupBy('ds.doctor_id, name, u.email')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON(['success' => true, 'doctors' => $rows]);
    }

    /**
     * JSON: Get hourly time slots by doctor and date (expands shift into 1-hour slots)
     */
    public function getTimesByDoctorAndDate()
    {
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $date = $this->request->getGet('date');
        $doctorId = $this->request->getGet('doctor_id');
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !$doctorId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters'])->setStatusCode(400);
        }

        $db = \Config\Database::connect();

        // 1) Load doctor's shift blocks for the selected date (only if doctor is active)
        $rows = $db->table('doctor_schedules ds')
            ->select('ds.start_time, ds.end_time')
            ->join('users u', 'ds.doctor_id = u.id', 'left')
            ->where('ds.doctor_id', $doctorId)
            ->where('ds.shift_date', $date)
            ->where('ds.status !=', 'cancelled')
            ->where('u.status', 'active')
            ->orderBy('ds.start_time', 'ASC')
            ->get()->getResultArray();

        // 2) Load already-booked appointment times for that doctor/date (exclude cancelled/no_show)
        $booked = $db->table('appointments')
            ->select('appointment_time')
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get()->getResultArray();
        $bookedSet = [];
        foreach ($booked as $b) {
            // normalize to HH:MM:00
            $t = substr($b['appointment_time'], 0, 5) . ':00';
            $bookedSet[$t] = true;
        }

        // 3) Expand schedule into 1-hour slots and remove booked ones
        $slots = [];
        $tz = new \DateTimeZone('Asia/Manila');
        foreach ($rows as $r) {
            $start = new \DateTime($date . ' ' . $r['start_time'], $tz);
            $end = new \DateTime($date . ' ' . $r['end_time'], $tz);
            if ($end <= $start) { // cross-midnight handling
                $end->modify('+1 day');
            }
            $cursor = clone $start;
            while ($cursor < $end) {
                $value = $cursor->format('H:i:00'); // submit value
                if (!isset($bookedSet[$value])) {   // exclude already booked times
                    $slots[$value] = $cursor->format('g:i A'); // display label
                }
                $cursor->modify('+1 hour');
            }
        }

        // 4) Build response array
        ksort($slots);
        $times = [];
        foreach ($slots as $value => $label) {
            $times[] = [ 'value' => $value, 'label' => $label ];
        }

        return $this->response->setJSON(['success' => true, 'times' => $times]);
    }
    
    /**
     * Display staff schedule - redirect to doctor schedule
     */
    public function schedule()
    {
        // Redirect to the proper doctor scheduling page
        return redirect()->to('/doctor/schedule');
    }

    /**
     * Create new appointment
     */
    public function create()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            // For web requests, redirect to login
            if (!$this->request->isAJAX()) {
                return redirect()->to('login')->with('error', 'Please login to access this feature');
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        // Handle patient - prefer provided patient_id from autocomplete; fallback to name lookup/create
        $patientName = trim((string)$this->request->getPost('patient_name'));
        $patientId = (string) ($this->request->getPost('patient_id') ?? '');
        
        if ($patientId === '' && $patientName === '') {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Please enter patient name');
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Please enter patient name']);
        }

        if ($patientId === '') {
            // Try to find existing patient by name first (case-insensitive on first and last)
            $existingPatient = $this->patientModel
                ->groupStart()
                    ->like('first_name', $patientName)
                    ->orLike('last_name', $patientName)
                    ->orLike("CONCAT(first_name, ' ', last_name)", $patientName)
                ->groupEnd()
                ->first();
            
            if ($existingPatient) {
                $patientId = $existingPatient['id'];
            } else {
                // Auto-create minimal patient so booking does not depend on suggestions
                $nameParts = explode(' ', trim($patientName), 2);
                $firstName = $nameParts[0] ?? 'Unknown';
                $lastName = $nameParts[1] ?? 'Unknown';

                $randomNum = rand(1000, 9999);
                $email = strtolower(str_replace(' ', '.', $firstName . '.' . $lastName)) . $randomNum . '@temp.com';
                $phone = '09' . rand(100000000, 999999999);

                $newPatientData = [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'date_of_birth' => '1990-01-01',
                    'gender' => 'other',
                    'address' => 'Not provided',
                    'status' => 'active',
                    // Ensure auto-created records from booking are not listed as outpatients
                    'type' => 'walkin'
                ];

                $this->patientModel->skipValidation(true);
                $patientId = $this->patientModel->insert($newPatientData);
                $this->patientModel->skipValidation(false);

                if (empty($patientId)) {
                    $errors = $this->patientModel->errors();
                    $errorMessage = 'Failed to create patient record';
                    if (!empty($errors)) {
                        $errorMessage .= ': ' . implode(', ', $errors);
                    }

                    if (!$this->request->isAJAX()) {
                        return redirect()->back()->withInput()->with('error', $errorMessage);
                    }
                    return $this->response->setJSON(['success' => false, 'message' => $errorMessage]);
                }
            }
        }

        $rules = [
            'doctor_id' => 'required',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
            'appointment_type' => 'required|in_list[consultation,follow_up,emergency,routine_checkup]',
            'reason' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            // For web requests, redirect back with errors
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Please fill in all required fields correctly');
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'patient_id' => $patientId,
            'doctor_id' => (string)$this->request->getPost('doctor_id'),
            'appointment_date' => $this->request->getPost('appointment_date'),
            'appointment_time' => $this->request->getPost('appointment_time'),
            'appointment_type' => $this->request->getPost('appointment_type'),
            'reason' => $this->request->getPost('reason'),
            'status' => 'scheduled'
        ];

        // Check for appointment conflicts
        if ($this->appointmentModel->checkAppointmentConflict($data['doctor_id'], $data['appointment_date'], $data['appointment_time'])) {
            // For web requests, redirect back with error
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Doctor already has an appointment at this time. Please choose a different time.');
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor already has an appointment at this time'
            ]);
        }

        $appointmentId = $this->appointmentModel->insert($data);

        if ($appointmentId) {
            // For web requests, redirect to appointment list with success
            if (!$this->request->isAJAX()) {
                return redirect()->to('appointments/list')->with('success', "Appointment booked successfully! Appointment ID: {$appointmentId}");
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment created successfully',
                'appointment_id' => $appointmentId
            ]);
        } else {
            // For web requests, redirect back with error
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Failed to create appointment. Please try again.');
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create appointment',
                'errors' => $this->appointmentModel->errors()
            ]);
        }
    }

    /**
     * Get appointment details
     */
    public function show($id)
    {
        // Validate ID (accept alphanumeric IDs like APT-YYYYMMDD-####)
        if (empty($id) || !preg_match('/^[A-Za-z0-9\-]+$/', $id)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid appointment id']);
        }
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        // Get appointment with patient and doctor details using correct table structure
        $appointment = $this->appointmentModel
            ->select('appointments.*, 
                     patients.first_name as patient_first_name, 
                     patients.last_name as patient_last_name,
                     patients.phone as patient_phone,
                     users.username as doctor_name, 
                     users.email as doctor_email')
            ->join('patients', 'patients.id = appointments.patient_id', 'left')
            ->join('users', 'users.id = appointments.doctor_id', 'left')
            ->join('roles r', 'users.role_id = r.id', 'left')
            ->where('r.name', 'doctor')
            ->where('appointments.id', $id)
            ->first();

        if (!$appointment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Appointment not found'
            ]);
        }

        // Format the appointment data for the modal
        $appointment['patient_name'] = !empty($appointment['patient_first_name']) && !empty($appointment['patient_last_name']) 
            ? $appointment['patient_first_name'] . ' ' . $appointment['patient_last_name'] 
            : 'N/A';
            
        $appointment['doctor_name'] = !empty($appointment['doctor_name']) 
            ? 'Dr. ' . $appointment['doctor_name'] 
            : 'N/A';

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
        // Validate ID (accept alphanumeric IDs like APT-YYYYMMDD-####)
        if (empty($id) || !preg_match('/^[A-Za-z0-9\-]+$/', $id)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid appointment id']);
        }
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

        $rules = [
            'patient_id' => 'permit_empty|integer',
            'doctor_id' => 'permit_empty|integer',
            'appointment_date' => 'permit_empty|valid_date',
            'appointment_time' => 'permit_empty',
            'appointment_type' => 'permit_empty|in_list[consultation,follow_up,emergency,routine_checkup]',
            'status' => 'permit_empty|in_list[scheduled,confirmed,in_progress,completed,cancelled,no_show]',
            'reason' => 'permit_empty|string',
            'notes' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = [];
        $fields = ['patient_id', 'doctor_id', 'appointment_date', 'appointment_time', 'appointment_type', 'status', 'reason', 'notes'];
        
        foreach ($fields as $field) {
            $value = $this->request->getPost($field);
            if ($value !== null) {
                $data[$field] = $value;
            }
        }

        // Check for appointment conflicts if date/time/doctor changed
        if (isset($data['doctor_id']) || isset($data['appointment_date']) || isset($data['appointment_time'])) {
            $doctorId = $data['doctor_id'] ?? $appointment['doctor_id'];
            $date = $data['appointment_date'] ?? $appointment['appointment_date'];
            $time = $data['appointment_time'] ?? $appointment['appointment_time'];
            
            if ($this->appointmentModel->checkAppointmentConflict($doctorId, $date, $time, $id)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Doctor already has an appointment at this time'
                ]);
            }
        }

        if ($this->appointmentModel->update($id, $data)) {
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
        // Validate ID
        if (!is_numeric($id) || (int) $id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid appointment id']);
        }
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
        // Validate ID
        if (!is_numeric($id) || (int) $id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid appointment id']);
        }
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
        // Validate ID
        if (!is_numeric($id) || (int) $id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid appointment id']);
        }
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
     * Get appointments by doctor
     */
    public function getByDoctor($doctorId)
    {
        // Validate ID
        if (!is_numeric($doctorId) || (int) $doctorId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid doctor id']);
        }
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $date = $this->request->getGet('date');
        $appointments = $this->appointmentModel->getAppointmentsByDoctor($doctorId, $date);

        return $this->response->setJSON([
            'success' => true,
            'appointments' => $appointments
        ]);
    }

    /**
     * Get appointments by patient
     */
    public function getByPatient($patientId)
    {
        // Validate ID
        if (!is_numeric($patientId) || (int) $patientId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid patient id']);
        }
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $appointments = $this->appointmentModel->getAppointmentsByPatient($patientId);

        return $this->response->setJSON([
            'success' => true,
            'appointments' => $appointments
        ]);
    }

    /**
     * Get today's appointments
     */
    public function getTodays()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $appointments = $this->appointmentModel->getTodaysAppointments();

        return $this->response->setJSON([
            'success' => true,
            'appointments' => $appointments
        ]);
    }

    /**
     * Get upcoming appointments
     */
    public function getUpcoming()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $limit = $this->request->getGet('limit') ?? 10;
        $appointments = $this->appointmentModel->getUpcomingAppointments($limit);

        return $this->response->setJSON([
            'success' => true,
            'appointments' => $appointments
        ]);
    }

    /**
     * Get appointments by date range (YYYY-MM-DD)
     */
    public function byDateRange()
    {
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access'])->setStatusCode(401);
        }

        $start = $this->request->getGet('start_date');
        $end = $this->request->getGet('end_date');
        if (!$start || !$end || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid or missing date parameters'])->setStatusCode(400);
        }

        // Normalize order if needed
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $appointments = $this->appointmentModel->getAppointmentsByDateRange($start, $end);
        return $this->response->setJSON(['success' => true, 'appointments' => $appointments]);
    }

    /**
     * Search appointments
     */
    public function search()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $searchTerm = $this->request->getGet('q');
        if (empty($searchTerm)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search term is required'
            ]);
        }

        $appointments = $this->appointmentModel->searchAppointments($searchTerm);

        return $this->response->setJSON([
            'success' => true,
            'appointments' => $appointments
        ]);
    }

    /**
     * Get appointment statistics
     */
    public function getStats()
    {
        // Check if user is logged in and has appropriate role
        $allowedRoles = ['admin', 'doctor', 'nurse'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized access']);
        }

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        
        $stats = $this->appointmentModel->getAppointmentStats($startDate, $endDate);

        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Delete appointment
     */
    public function delete($id)
    {
        // Validate ID
        if (!is_numeric($id) || (int) $id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid appointment id']);
        }
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
}
