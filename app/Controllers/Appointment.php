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
     * Get role-based view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleMap = [
            'admin' => 'admin',
            'doctor' => 'admin', // Use admin view for doctors (unified)
            'nurse' => 'admin', // Use admin view for nurses (unified)
            'receptionist' => 'admin',
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/{$viewName}";
    }

    /**
     * Display appointment list - unified for all roles
     */
    public function index()
    {
        // Role-based access control
        $allowedRoles = ['admin', 'doctor', 'nurse', 'receptionist'];
        $this->requireRole($allowedRoles);
        
        $role = session('role');
        $filter = $this->request->getGet('filter') ?? 'today';
        $date   = $this->request->getGet('date');
        $today  = date('Y-m-d'); // server/app timezone
        $appointments = [];

        // Admin, doctor, nurse, receptionist see all appointments
        if ($filter === 'all') {
            $appointments = $this->appointmentModel->getAppointmentsWithDetails();
        } elseif ($filter === 'date' && $date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $appointments = $this->appointmentModel->getAppointmentsByDateRange($date, $date);
        } else {
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

        return view($this->getRoleViewPath('appointments/Appointmentlist'), $data);
    }

    /**
     * Doctor-facing appointment list (legacy route - redirects to index)
     */
    public function doctorToday()
    {
        // Redirect to unified index method
        return $this->index();
    }

    /**
     * Show book appointment form
     */
    public function book()
    {
        // Only admin, nurse, and receptionist can book appointments
        $this->requireRole(['admin', 'nurse', 'receptionist']);

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
        
        return view($this->getRoleViewPath('appointments/Bookappointment'), $data);
    }

    /**
     * JSON: Get available schedule dates (only dates with available time slots)
     * Filters out dates that have no available time slots for any doctor
     */
    public function getAvailableDates()
    {
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $today = date('Y-m-d');
        $db = \Config\Database::connect();
        $tz = new \DateTimeZone('Asia/Manila');
        $now = new \DateTime('now', $tz);
        
        // Get all dates with schedules
        // Note: doctor_id in doctor_schedules references staff_profiles.id, not users.id
        $allDates = $db->table('doctor_schedules ds')
            ->select('ds.shift_date')
            ->join('staff_profiles sp', 'sp.id = ds.doctor_id', 'left')
            ->join('users u', 'u.id = sp.user_id', 'left')
            ->where('ds.shift_date >=', $today)
            ->where('ds.status !=', 'cancelled')
            ->where('sp.status', 'active')
            ->where('u.status', 'active')
            ->distinct()
            ->orderBy('ds.shift_date', 'ASC')
            ->get()->getResultArray();

        $datesWithSlots = [];
        
        // Check each date to see if it has any available time slots
        foreach ($allDates as $row) {
            $date = $row['shift_date'];
            $isToday = ($date === $today);
            
            // Get all doctors with schedules for this date
            // Note: doctor_id in doctor_schedules references staff_profiles.id, not users.id
            $doctors = $db->table('doctor_schedules ds')
                ->select('ds.doctor_id')
                ->join('staff_profiles sp', 'sp.id = ds.doctor_id', 'left')
                ->join('users u', 'u.id = sp.user_id', 'left')
                ->where('ds.shift_date', $date)
                ->where('ds.status !=', 'cancelled')
                ->where('sp.status', 'active')
                ->where('u.status', 'active')
                ->groupBy('ds.doctor_id')
                ->get()->getResultArray();
            
            $hasAvailableSlot = false;
            
            // Check each doctor for available slots
            foreach ($doctors as $doctor) {
                $doctorId = $doctor['doctor_id'];
                
                // Get doctor's shift blocks
                // Note: doctor_id in doctor_schedules references staff_profiles.id, not users.id
                $shifts = $db->table('doctor_schedules ds')
                    ->select('ds.start_time, ds.end_time')
                    ->join('staff_profiles sp', 'sp.id = ds.doctor_id', 'left')
                    ->join('users u', 'u.id = sp.user_id', 'left')
                    ->where('ds.doctor_id', $doctorId)
                    ->where('ds.shift_date', $date)
                    ->where('ds.status !=', 'cancelled')
                    ->where('sp.status', 'active')
                    ->where('u.status', 'active')
                    ->orderBy('ds.start_time', 'ASC')
                    ->get()->getResultArray();

                if (empty($shifts)) {
                    continue;
                }

                // Get booked appointment times
                $booked = $db->table('appointments')
                    ->select('appointment_time')
                    ->where('doctor_id', $doctorId)
                    ->where('appointment_date', $date)
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->get()->getResultArray();
                
                $bookedSet = [];
                foreach ($booked as $b) {
                    $t = substr($b['appointment_time'], 0, 5) . ':00';
                    $bookedSet[$t] = true;
                }

                // Check for available slots
                foreach ($shifts as $shift) {
                    $start = new \DateTime($date . ' ' . $shift['start_time'], $tz);
                    $end = new \DateTime($date . ' ' . $shift['end_time'], $tz);
                    if ($end <= $start) {
                        $end->modify('+1 day');
                    }
                    $cursor = clone $start;
                    while ($cursor < $end) {
                        // For today, only include future time slots (must be strictly after current time)
                        // This ensures we don't show time slots that have already passed
                        if ($isToday) {
                            // Compare the full datetime (date + time) to current datetime
                            if ($cursor <= $now) {
                                $cursor->modify('+1 hour');
                                continue;
                            }
                        }
                        
                        // For future dates, all slots are valid
                        // For today, only future slots pass the check above
                        $value = $cursor->format('H:i:00');
                        if (!isset($bookedSet[$value])) {
                            $hasAvailableSlot = true;
                            break 3; // Break out of all loops
                        }
                        $cursor->modify('+1 hour');
                    }
                }
            }
            
            // Only include dates with at least one available slot
            if ($hasAvailableSlot) {
                $datesWithSlots[] = $date;
            }
        }

        return $this->response->setJSON(['success' => true, 'dates' => $datesWithSlots]);
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
            ->select("ds.doctor_id, COALESCE(CONCAT(sp.first_name, ' ', sp.last_name), u.username) AS name, COALESCE(sp.email, u.email) as email")
            ->join('staff_profiles sp', 'sp.id = ds.doctor_id', 'left')
            ->join('users u', 'u.id = sp.user_id', 'left')
            ->where('ds.shift_date', $date)
            ->where('ds.status !=', 'cancelled')
            ->where('sp.status', 'active')
            ->groupBy('ds.doctor_id, name, u.email')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        return $this->response->setJSON(['success' => true, 'doctors' => $rows]);
    }

    /**
     * JSON: Get hourly time slots by doctor and date (expands shift into 1-hour slots)
     * Filters out past time slots and ensures all slots are within shift times
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
        $tz = new \DateTimeZone('Asia/Manila');
        $now = new \DateTime('now', $tz);
        $today = $now->format('Y-m-d');
        $isToday = ($date === $today);

        // 1) Load doctor's shift blocks for the selected date (only if doctor is active)
        // Note: doctorId here should be staff_profiles.id, but we need to handle both cases
        $rows = $db->table('doctor_schedules ds')
            ->select('ds.start_time, ds.end_time')
            ->join('staff_profiles sp', 'sp.id = ds.doctor_id', 'left')
            ->join('users u', 'u.id = sp.user_id', 'left')
            ->where('ds.doctor_id', $doctorId)
            ->where('ds.shift_date', $date)
            ->where('ds.status !=', 'cancelled')
            ->where('sp.status', 'active')
            ->orderBy('ds.start_time', 'ASC')
            ->get()->getResultArray();

        // If no shifts found, return empty
        if (empty($rows)) {
            return $this->response->setJSON(['success' => true, 'times' => []]);
        }

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

        // 3) Expand schedule into 1-hour slots, remove booked ones, and filter past times
        $slots = [];
        foreach ($rows as $r) {
            $start = new \DateTime($date . ' ' . $r['start_time'], $tz);
            $end = new \DateTime($date . ' ' . $r['end_time'], $tz);
            if ($end <= $start) { // cross-midnight handling (night shifts)
                $end->modify('+1 day');
            }
            $cursor = clone $start;
            while ($cursor < $end) {
                // For today, only show upcoming time slots (must be strictly after current time)
                // This ensures we don't show time slots that have already passed
                // For future dates, show all available slots
                if ($isToday) {
                    // Compare the full datetime (date + time) to current datetime
                    if ($cursor <= $now) {
                        $cursor->modify('+1 hour');
                        continue;
                    }
                }
                
                $value = $cursor->format('H:i:00'); // submit value
                if (!isset($bookedSet[$value])) {   // exclude already booked times
                    $slots[$value] = $cursor->format('g:i A'); // display label
                }
                $cursor->modify('+1 hour');
            }
        }
        
        // If today and no available slots after filtering, return empty
        if ($isToday && empty($slots)) {
            return $this->response->setJSON(['success' => true, 'times' => []]);
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
     * JSON: Check if patient already has an appointment on a specific date or has an active appointment
     */
    public function checkPatientAppointment()
    {
        $allowedRoles = ['admin', 'nurse', 'receptionist'];
        if (!in_array(session('role'), $allowedRoles)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
        }

        $patientId = $this->request->getGet('patient_id');
        $date = $this->request->getGet('date');
        
        if (!$patientId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid parameters'])->setStatusCode(400);
        }

        $today = date('Y-m-d');
        $message = null;
        $hasConflict = false;
        $conflictType = null; // 'same_date' or 'active'

        // Check if patient has an active appointment (status = scheduled)
        $activeAppointment = $this->appointmentModel
            ->where('patient_id', $patientId)
            ->where('status', 'scheduled')
            ->first();

        if ($activeAppointment) {
            $hasConflict = true;
            $conflictType = 'active';
            $message = 'This patient still has an active appointment.';
        } elseif ($date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // Check if patient already has an appointment on this date (excluding cancelled/no_show)
            $existingAppointment = $this->appointmentModel
                ->where('patient_id', $patientId)
                ->where('appointment_date', $date)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->first();

            if ($existingAppointment) {
                $hasConflict = true;
                $conflictType = 'same_date';
                $message = ($date === $today) 
                    ? 'This patient already has an appointment today.'
                    : 'This patient already has an appointment on this date.';
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'hasAppointment' => $hasConflict,
            'conflictType' => $conflictType,
            'message' => $message
        ]);
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

        // Handle patient - prefer provided patient_id from autocomplete; fallback to name lookup (must exist)
        $patientName = trim((string)$this->request->getPost('patient_name'));
        $patientId = (string) ($this->request->getPost('patient_id') ?? '');
        
        if ($patientId === '' && $patientName === '') {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Please enter patient name');
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Please enter patient name']);
        }

        if ($patientId === '') {
            // Try to find existing patient by name - must exist, no auto-creation
            // Search includes first_name, middle_name, and last_name
            $existingPatient = $this->patientModel
                ->groupStart()
                    ->like('first_name', $patientName)
                    ->orLike('middle_name', $patientName)
                    ->orLike('last_name', $patientName)
                    ->orLike("CONCAT_WS(' ', first_name, middle_name, last_name)", $patientName)
                ->groupEnd()
                ->first();
            
            if ($existingPatient) {
                $patientId = $existingPatient['id'];
            } else {
                // Patient does not exist - require registration first
                $errorMessage = 'Patient not found. Please register the patient first before booking an appointment.';
                if (!$this->request->isAJAX()) {
                    return redirect()->back()->withInput()->with('error', $errorMessage);
                }
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => $errorMessage
                ])->setStatusCode(404);
            }
        }
        
        // Verify patient exists (even if patient_id was provided directly)
        if (!empty($patientId)) {
            $patient = $this->patientModel->find($patientId);
            if (!$patient) {
                $errorMessage = 'Patient not found. Please register the patient first before booking an appointment.';
                if (!$this->request->isAJAX()) {
                    return redirect()->back()->withInput()->with('error', $errorMessage);
                }
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => $errorMessage
                ])->setStatusCode(404);
            }
            
            // Check if patient is inpatient/admitted - only outpatients can make appointments
            $patientType = strtolower(trim((string)($patient['type'] ?? '')));
            if ($patientType === 'inpatient') {
                $errorMessage = 'Inpatient/admitted patients cannot book appointments. Only outpatients can schedule appointments.';
                if (!$this->request->isAJAX()) {
                    return redirect()->back()->withInput()->with('error', $errorMessage);
                }
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => $errorMessage
                ])->setStatusCode(400);
            }
            
            // Also check if patient has an active admission (status = 'admitted')
            $db = \Config\Database::connect();
            if ($db->tableExists('admission_details')) {
                $activeAdmission = $db->table('admission_details')
                    ->where('patient_id', $patientId)
                    ->where('status', 'admitted')
                    ->get()->getRowArray();
                
                if ($activeAdmission) {
                    $errorMessage = 'Patient is currently admitted. Only outpatients can book appointments.';
                    if (!$this->request->isAJAX()) {
                        return redirect()->back()->withInput()->with('error', $errorMessage);
                    }
                    return $this->response->setJSON([
                        'success' => false, 
                        'message' => $errorMessage
                    ])->setStatusCode(400);
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

        // Validate that appointment time is within doctor's shift
        $db = \Config\Database::connect();
        $tz = new \DateTimeZone('Asia/Manila');
        $appointmentDateTime = new \DateTime($data['appointment_date'] . ' ' . $data['appointment_time'], $tz);
        $now = new \DateTime('now', $tz);
        
        // Block past appointments
        if ($appointmentDateTime <= $now) {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Cannot book appointments in the past. Please select a future date and time.');
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot book appointments in the past'
            ]);
        }

        // Check if appointment time is within any of the doctor's shift blocks
        $shifts = $db->table('doctor_schedules ds')
            ->select('ds.start_time, ds.end_time')
            ->join('staff_profiles sp', 'sp.id = ds.doctor_id', 'left')
            ->where('ds.doctor_id', $data['doctor_id'])
            ->where('ds.shift_date', $data['appointment_date'])
            ->where('ds.status !=', 'cancelled')
            ->where('sp.status', 'active')
            ->get()->getResultArray();

        if (empty($shifts)) {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Doctor is not available on the selected date.');
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Doctor is not available on the selected date'
            ]);
        }

        // Check if patient has an active appointment (status = scheduled)
        $activeAppointment = $this->appointmentModel
            ->where('patient_id', $patientId)
            ->where('status', 'scheduled')
            ->first();
        
        if ($activeAppointment) {
            $errorMessage = 'This patient still has an active appointment.';
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', $errorMessage);
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => $errorMessage
            ])->setStatusCode(409); // 409 Conflict
        }

        // Check if patient already has an appointment on the selected date
        $existingAppointment = $this->appointmentModel
            ->where('patient_id', $patientId)
            ->where('appointment_date', $data['appointment_date'])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->first();
        
        if ($existingAppointment) {
            // Check if the selected date is today for more accurate message
            $selectedDate = $data['appointment_date'];
            $today = date('Y-m-d');
            $errorMessage = ($selectedDate === $today) 
                ? 'This patient already has an appointment today.'
                : 'This patient already has an appointment on this date.';
            
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', $errorMessage);
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => $errorMessage
            ])->setStatusCode(409); // 409 Conflict
        }

        // Check if appointment time falls within any shift block
        $appointmentTime = $data['appointment_time'];
        $isWithinShift = false;
        foreach ($shifts as $shift) {
            $shiftStart = new \DateTime($data['appointment_date'] . ' ' . $shift['start_time'], $tz);
            $shiftEnd = new \DateTime($data['appointment_date'] . ' ' . $shift['end_time'], $tz);
            if ($shiftEnd <= $shiftStart) {
                // Cross-midnight shift
                $shiftEnd->modify('+1 day');
            }
            if ($appointmentDateTime >= $shiftStart && $appointmentDateTime < $shiftEnd) {
                $isWithinShift = true;
                break;
            }
        }

        if (!$isWithinShift) {
            if (!$this->request->isAJAX()) {
                return redirect()->back()->withInput()->with('error', 'Selected appointment time is outside the doctor\'s shift hours. Please select a time within the available shift.');
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected appointment time is outside the doctor\'s shift hours'
            ]);
        }

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
                     COALESCE(CONCAT(sp.first_name, " ", sp.last_name), users.username) as doctor_name, 
                     COALESCE(sp.email, users.email) as doctor_email')
            ->join('patients', 'patients.id = appointments.patient_id', 'left')
            ->join('staff_profiles sp', 'sp.id = appointments.doctor_id', 'left')
            ->join('users', 'users.id = sp.user_id', 'left')
            ->join('roles r', 'r.id = sp.role_id', 'left')
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
        // Only admin can delete appointments
        $this->requireRole(['admin']);
        
        // Validate ID
        if (!is_numeric($id) || (int) $id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid appointment id']);
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
