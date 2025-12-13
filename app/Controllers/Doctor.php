<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\DoctorScheduleModel;
use App\Models\PatientVitalModel;
use App\Models\PatientModel;
use App\Models\PrescriptionModel;
use App\Models\AdmissionModel;

class Doctor extends BaseController
{
    protected $doctorScheduleModel;
    protected $userModel;
    protected $vitalModel;
    protected $patientModel;
    protected $prescriptionModel;
    protected $admissionModel;
    
    public function __construct()
    {
        $this->doctorScheduleModel = new DoctorScheduleModel();
        $this->userModel = new UserModel();
        $this->vitalModel = new PatientVitalModel();
        $this->patientModel = new PatientModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->admissionModel = new AdmissionModel();
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
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/{$viewName}";
    }

    // ==================== SCHEDULE METHODS ====================

    /**
     * Display the doctor scheduling interface (admin/doctor)
     */
    public function schedule()
    {
        $this->requireRole(['admin', 'doctor']);
        
        $role = session('role');
        
        // Get current date range (default to current month, or selected month for month view)
        $monthParam = $this->request->getGet('month');
        if ($monthParam) {
            // If month parameter is provided (e.g., "2025-11"), use that month
            $startDate = date('Y-m-01', strtotime($monthParam . '-01'));
            $endDate = date('Y-m-t', strtotime($monthParam . '-01'));
        } else {
            // Default to current month
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        }

        // Get schedules for the date range (expand to include full calendar view)
        // Include days from previous/next month that appear in the calendar grid
        $calendarStart = date('Y-m-d', strtotime('last sunday', strtotime($startDate)));
        $calendarEnd = date('Y-m-d', strtotime('next saturday', strtotime($endDate)));
        
        // If doctor, only get their own schedule
        if ($role === 'doctor') {
            $doctorId = (string) session('user_id');
            if ($doctorId === '') {
                return redirect()->to('/dashboard')->with('error', 'Unable to resolve doctor account.');
            }
            // Filter schedules by doctor_id
            $schedules = $this->doctorScheduleModel
                ->where('doctor_id', $doctorId)
                ->where('shift_date >=', $calendarStart)
                ->where('shift_date <=', $calendarEnd)
                ->where('status !=', 'cancelled')
                ->orderBy('shift_date', 'ASC')
                ->orderBy('start_time', 'ASC')
                ->findAll();
            $doctors = []; // No doctor dropdown for doctors
        } else {
            // Admin sees all schedules
            $schedules = $this->doctorScheduleModel->getSchedulesByDateRange($calendarStart, $calendarEnd);

            // Build dropdown options directly from users who have a doctor role
            // Only show active doctors who have accounts and are active status
            $db = \Config\Database::connect();
            
            $doctors = $db->table('users u')
                ->select('u.id AS doctor_id, u.id AS user_id, u.username, 
                         sp.specialization_id, ss.name AS specialization,
                         sp.department_id, sd.name AS department, sd.slug AS department_slug, sp.status')
                ->join('roles r', 'u.role_id = r.id', 'left')
                ->join('staff_profiles sp', 'sp.user_id = u.id', 'left')
                ->join('staff_specializations ss', 'ss.id = sp.specialization_id', 'left')
                ->join('staff_departments sd', 'sd.id = sp.department_id', 'left')
                ->where('r.name', 'doctor')
                ->where('u.status', 'active')
                ->groupStart()
                    ->where('sp.status', 'active')
                    ->orWhere('sp.status IS NULL')
                ->groupEnd()
                ->orderBy('u.username', 'ASC')
                ->get()
                ->getResultArray();
            
            // Additional filter to ensure only active doctors (exclude on_leave and inactive)
            $doctors = array_filter($doctors, function($doctor) {
                // Only include doctors with active status or no status (new doctors without profile)
                // sp.status can be 'active', 'inactive', 'on_leave', or NULL
                return empty($doctor['status']) || $doctor['status'] === 'active';
            });
        }
        
        // Note: doctor_id here is users.id; addSchedule will normalize to staff_profiles.id before insert

        $data = [
            'schedules' => $schedules,
            'doctors' => $doctors,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currentMonth' => $monthParam ?? date('Y-m'),
            'isReadOnly' => ($role === 'doctor'), // Doctors can only view, not edit
            'userRole' => $role,
        ];

        return view($this->getRoleViewPath('appointments/StaffSchedule'), $data);
    }

    /**
     * Doctor's own schedule view (read-only for doctors)
     */
    public function mySchedule()
    {
        $this->requireRole(['doctor']);
        
        // Get current date range (default to current month)
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

        // Get only the logged-in doctor's schedule
        $doctorId = session()->get('user_id');
        
        // Get the doctor's schedule
        $schedules = $this->doctorScheduleModel->where('doctor_id', $doctorId)
                                             ->where('shift_date >=', $startDate)
                                             ->where('shift_date <=', $endDate)
                                             ->findAll();
        
        // Get doctor's info
        $doctor = $this->userModel->select('staff_profiles.id AS doctor_id, users.id AS user_id, users.username, staff_profiles.first_name, staff_profiles.last_name')
                                ->join('roles r', 'users.role_id = r.id', 'left')
                                ->join('staff_profiles', 'staff_profiles.user_id = users.id', 'left')
                                ->where('users.id', $doctorId)
                                ->where('r.name', 'doctor')
                                ->first();
        
        // For view compatibility
        $doctors = [$doctor];
        
        // Get schedule statistics
        $stats = [
            'totalShifts' => count($schedules),
            'upcomingShifts' => count(array_filter($schedules, function($s) {
                return strtotime($s['shift_date']) >= strtotime('today');
            })),
            'completedShifts' => count(array_filter($schedules, function($s) {
                return strtotime($s['shift_date']) < strtotime('today');
            }))
        ];

        $data = [
            'schedules' => $schedules,
            'doctors' => $doctors,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'conflicts' => [],
            'isReadOnly' => true
        ];

        return view($this->getRoleViewPath('appointments/StaffSchedule'), $data);
    }

    /**
     * Add a new doctor schedule
     */
    public function addSchedule()
    {
        // Only admin can add schedules, doctors cannot
        $this->requireRole(['admin']);
        
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $startDate = $this->request->getPost('start_date');
            $endDate = $this->request->getPost('end_date');
            
            // If only start_date is provided, use it as single date (backward compatibility)
            if ($startDate && !$endDate) {
                $endDate = $startDate;
            }
            
            // Validate date range
            if (!$startDate || !$endDate) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Start date and end date are required'
                ]);
            }
            
            if (strtotime($startDate) > strtotime($endDate)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Start date must be before or equal to end date'
                ]);
            }

            $data = [
                'doctor_id' => $this->request->getPost('doctor_id'),
                'department_id' => $this->request->getPost('department_id') ?: $this->request->getPost('department'),
                'shift_type' => $this->request->getPost('shift_type'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'notes' => $this->request->getPost('notes') ?? ''
            ];
            
            // Convert department name to department_id if needed
            if (!empty($data['department_id']) && !is_numeric($data['department_id'])) {
                $db = \Config\Database::connect();
                $dept = $db->table('staff_departments')
                    ->where('name', $data['department_id'])
                    ->get()
                    ->getRowArray();
                $data['department_id'] = $dept ? $dept['id'] : null;
            } else {
                $data['department_id'] = !empty($data['department_id']) ? (int)$data['department_id'] : null;
            }

            // Normalize/validate doctor_id (now staff_profiles.id as INT)
            // Accept either staff_profiles.id (INT) or user_id (VARCHAR) and convert to staff_profiles.id
            try {
                $db = \Config\Database::connect();
                $staffProfilesTbl = $db->table('staff_profiles');
                $usersTbl = $db->table('users');
                $rolesTbl = $db->table('roles');

                $providedId = trim((string)($data['doctor_id'] ?? ''));
                $staffProfileId = null;
                
                if ($providedId !== '') {
                    // Check if it's already a staff_profiles.id (numeric)
                    if (is_numeric($providedId)) {
                        $staffProfile = $staffProfilesTbl->where('id', (int)$providedId)->get()->getRowArray();
                        if ($staffProfile) {
                            $staffProfileId = (int)$staffProfile['id'];
                        }
                    }
                    
                    // If not found as staff_profiles.id, try as user_id
                    if (!$staffProfileId) {
                        $userRow = $usersTbl->select('users.*, roles.name AS role_name')
                            ->join('roles', 'roles.id = users.role_id', 'left')
                            ->where('users.id', $providedId)
                            ->where('roles.name', 'doctor')
                            ->get()->getRowArray();
                        if ($userRow) {
                            // Ensure staff profile exists keyed by user_id
                            $existing = $staffProfilesTbl->where('user_id', $userRow['id'])->get()->getRowArray();
                            if (!$existing) {
                                $email = trim((string)($userRow['email'] ?? ''));
                                if ($email === '') {
                                    $email = 'doc_' . $userRow['id'] . '@local'; // unique fallback
                                }
                                $uname = (string) ($userRow['username'] ?? 'Doctor User');
                                $parts = preg_split('/\s+/', trim($uname));
                                $first = ucfirst($parts[0] ?? 'Doctor');
                                $last  = ucfirst($parts[1] ?? 'User');
                                // Get doctor role_id
                                $doctorRole = $rolesTbl->where('name', 'doctor')->get()->getRowArray();
                                $insert = [
                                    'user_id'          => (string)$userRow['id'],
                                    'email'            => $email,
                                    'first_name'       => $first,
                                    'last_name'        => $last,
                                    'role_id'          => $doctorRole['id'] ?? null,
                                    'license_number'   => 'LIC-' . $userRow['id'], // unique fallback
                                    'status'           => 'active',
                                ];
                                $staffProfilesTbl->insert($insert);
                                $staffProfileId = (int)$db->insertID();
                            } else {
                                $staffProfileId = (int)$existing['id'];
                            }
                            
                            // doctor_name is no longer stored - it's derived from staff_profiles
                        }
                    }
                }
                
                // Update data['doctor_id'] to use staff_profiles.id
                if ($staffProfileId) {
                    $data['doctor_id'] = $staffProfileId;
                }
            } catch (\Throwable $e) {
                log_message('error', 'Doctor ID normalization failed: ' . $e->getMessage());
            }

            // Safety: verify doctor_id points to a valid staff profile with doctor role
            try {
                $db = \Config\Database::connect();
                $doctorProfile = $db->table('staff_profiles sp')
                    ->select('sp.id')
                    ->join('roles r', 'r.id = sp.role_id', 'left')
                    ->where('sp.id', (int)($data['doctor_id'] ?? 0))
                    ->where('r.name', 'doctor')
                    ->where('sp.status', 'active')
                    ->get()->getRowArray();
                if (!$doctorProfile) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'Unable to resolve doctor. Please select a valid doctor profile.'
                    ]);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Doctor verification failed: ' . $e->getMessage());
            }

            // Time validation - prevent adding shifts in the past
            // Use Asia/Manila timezone explicitly to match local environment
            $tz = new \DateTimeZone('Asia/Manila');
            $now = new \DateTime('now', $tz);
            $today = $now->format('Y-m-d');
            $todayTimestamp = strtotime($today);

            // If start date is in the past, block outright
            if (strtotime($startDate) < $todayTimestamp) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Start date cannot be in the past. Please select today or a future date.'
                ]);
            }

            // If end date is in the past, block outright
            if (strtotime($endDate) < $todayTimestamp) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'End date cannot be in the past. Please select today or a future date.'
                ]);
            }

            // If start date is today, block if shift start time already passed (exact DateTime check)
            if ($startDate === $today) {
                $type = strtolower(trim($data['shift_type'] ?? ''));
                $timeRanges = $this->doctorScheduleModel->getShiftTimes($type);
                if (!empty($timeRanges)) {
                    $firstRange = $timeRanges[0];
                    $firstStartTime = $firstRange[0];
                    $shiftStart = new \DateTime($startDate . ' ' . $firstStartTime, $tz);
                    if ($now >= $shiftStart) {
                        return $this->response->setStatusCode(400)->setJSON([
                            'success' => false,
                            'message' => 'Cannot add shift for today as the start time has already passed.'
                        ]);
                    }
                }
            }

            // Validate required fields
            if (empty($data['doctor_id']) || empty($data['shift_type'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false, 
                    'message' => 'Missing required fields: doctor_id or shift_type'
                ]);
            }

            // Check if doctor is on leave for any date in the range
            $db = \Config\Database::connect();
            $staffProfile = $db->table('staff_profiles')
                ->where('user_id', $data['doctor_id'])
                ->where('status', 'on_leave')
                ->get()
                ->getRowArray();
            
            if ($staffProfile) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'This doctor is currently on leave and cannot be scheduled.'
                ]);
            }

            // Process date range - create schedules for each day
            $result = $this->doctorScheduleModel->addScheduleRange($data);
            
            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'Add Schedule Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false, 
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing schedule
     */
    public function updateSchedule($id)
    {
        // Only admin can update schedules, doctors cannot
        $this->requireRole(['admin']);
        
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        if (!is_numeric($id) || (int)$id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid schedule id']);
        }

        $data = [
            'doctor_id' => $this->request->getPost('doctor_id'),
            'department_id' => $this->request->getPost('department_id') ?: $this->request->getPost('department'),
            'shift_type' => $this->request->getPost('shift_type'),
            'shift_date' => $this->request->getPost('shift_date'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes') ?? ''
        ];
        
        // Convert department name to department_id if needed
        if (!empty($data['department_id']) && !is_numeric($data['department_id'])) {
            $db = \Config\Database::connect();
            $dept = $db->table('staff_departments')
                ->where('name', $data['department_id'])
                ->get()
                ->getRowArray();
            $data['department_id'] = $dept ? $dept['id'] : null;
        } else {
            $data['department_id'] = !empty($data['department_id']) ? (int)$data['department_id'] : null;
        }

        // Check for conflicts (excluding current record)
        if (isset($data['doctor_id'], $data['shift_date'], $data['start_time'], $data['end_time'])) {
            $conflicts = $this->doctorScheduleModel->checkConflicts(
                $data['doctor_id'],
                $data['shift_date'],
                $data['start_time'],
                $data['end_time'],
                $id
            );
            
            if (!empty($conflicts)) {
                return $this->response->setStatusCode(409)->setJSON([
                    'success' => false,
                    'message' => 'Scheduling conflict detected',
                    'conflicts' => $conflicts
                ]);
            }
        }

        $result = $this->doctorScheduleModel->update($id, $data);
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Schedule updated successfully'
            ]);
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Failed to update schedule',
                'errors' => $this->doctorScheduleModel->errors()
            ]);
        }
    }

    /**
     * Delete a schedule
     */
    public function deleteSchedule($id)
    {
        // Only admin can delete schedules, doctors cannot
        $this->requireRole(['admin']);
        
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        if (!is_numeric($id) || (int)$id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid schedule id']);
        }

        $result = $this->doctorScheduleModel->delete($id);
        
        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
        } else {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Failed to delete schedule'
            ]);
        }
    }

    /**
     * JSON: Get all schedules for a specific date
     * Route: /doctor/schedules-by-date?date=YYYY-MM-DD
     */
    public function getSchedulesByDate()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        // Check authentication
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $date = $this->request->getGet('date');
        
        // Validate date format
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false, 
                'message' => 'Invalid date format. Expected YYYY-MM-DD'
            ]);
        }

        try {
            // Use model method to get schedules
            $schedules = $this->doctorScheduleModel->getByDate($date);

            // Format the response
            $formatted = [];
            foreach ($schedules as $schedule) {
                $startTime = !empty($schedule['start_time']) ? date('g:i A', strtotime($schedule['start_time'])) : '';
                $endTime = !empty($schedule['end_time']) ? date('g:i A', strtotime($schedule['end_time'])) : '';
                $timeDisplay = $startTime && $endTime ? $startTime . ' - ' . $endTime : ($startTime ? $startTime : '');
                
                // Map status to display text
                $statusDisplay = ucfirst($schedule['status'] ?? 'scheduled');
                if ($statusDisplay === 'Scheduled') {
                    $statusDisplay = 'Available';
                }
                
                $formatted[] = [
                    'id' => $schedule['id'] ?? '',
                    'doctor_id' => $schedule['doctor_id'] ?? '',
                    'doctor_name' => $schedule['doctor_name'] ?? 'Unknown',
                    'department' => $schedule['department'] ?? 'General',
                    'shift_type' => $schedule['shift_type'] ?? '',
                    'time' => $timeDisplay,
                    'start_time' => $schedule['start_time'] ?? '',
                    'end_time' => $schedule['end_time'] ?? '',
                    'status' => $schedule['status'] ?? 'scheduled',
                    'status_display' => $statusDisplay,
                    'notes' => $schedule['notes'] ?? ''
                ];
            }

            // Always return 200 with success=true, even if empty
            return $this->response->setStatusCode(200)->setJSON([
                'success' => true,
                'date' => $date,
                'schedules' => $formatted,
                'count' => count($formatted)
            ]);
        } catch (\Exception $e) {
            log_message('error', 'getSchedulesByDate error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
    }

    // ==================== VITALS METHODS ====================

    /**
     * GET /doctor/vitals?patient_id=...
     * Returns latest vitals for the given patient.
     */
    public function vitals()
    {
        $this->requireRole(['doctor', 'nurse', 'admin', 'receptionist']);

        $patientId = (string) $this->request->getGet('patient_id');
        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        try {
            $vitals = $this->vitalModel->getLatestForPatient($patientId);

            // If no vitals found, return null (vitals should only come from patient_vitals table)
            // No fallback to patients table since vitals columns don't exist there
            // Ensure vitals is null (not empty array or false) if not found
            if (empty($vitals) || $vitals === false) {
                $vitals = null;
            }

            return $this->response->setJSON([
                'success' => true,
                'vitals'  => $vitals,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching vitals: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error fetching vitals',
                'vitals' => null,
            ]);
        }
    }

    /**
     * POST /doctor/vitals/save
     * Body: patient_id, blood_pressure, heart_rate, temperature
     */
    public function saveVitals()
    {
        $this->requireRole(['doctor', 'nurse', 'admin']);

        try {
            $patientId = trim((string) $this->request->getPost('patient_id'));
            $bloodPressure = trim((string) $this->request->getPost('blood_pressure'));
            $heartRate = $this->request->getPost('heart_rate');
            $temperature = $this->request->getPost('temperature');
            $userId = session()->get('user_id');

            if ($patientId === '') {
                return $this->response->setStatusCode(400)
                    ->setJSON(['success' => false, 'message' => 'patient_id is required']);
            }

            // Prepare data - convert empty strings to null for optional fields
            $data = [
                'patient_id'     => $patientId,
                'blood_pressure' => $bloodPressure !== '' ? $bloodPressure : null,
                'heart_rate'     => $heartRate !== '' && $heartRate !== null ? (int)$heartRate : null,
                'temperature'    => $temperature !== '' && $temperature !== null ? (float)$temperature : null,
                'recorded_by'    => $userId ? (string)$userId : null, // VARCHAR(20) in database
            ];

            // Validate that at least one vital sign is provided
            if (empty($data['blood_pressure']) && empty($data['heart_rate']) && empty($data['temperature'])) {
                return $this->response->setStatusCode(400)
                    ->setJSON(['success' => false, 'message' => 'At least one vital sign is required']);
            }

            // Save vitals
            if (!$this->vitalModel->save($data)) {
                $errors = $this->vitalModel->errors();
                log_message('error', 'Failed to save vitals: ' . json_encode($errors));
                return $this->response->setStatusCode(400)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Failed to save vitals',
                        'errors'  => $errors,
                    ]);
            }

            // Get the saved record
            $latest = $this->vitalModel->getLatestForPatient($patientId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Vitals saved successfully',
                'vitals'  => $latest,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error saving vitals: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Error saving vitals: ' . $e->getMessage(),
                ]);
        }
    }

    // ==================== PRESCRIPTION METHODS ====================

    /**
     * GET /doctor/prescription?patient_id=...
     * Return latest prescription note for the given patient.
     */
    public function prescription()
    {
        $this->requireRole(['doctor', 'nurse', 'admin', 'receptionist']);

        $patientId = (string) $this->request->getGet('patient_id');
        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        $record = $this->prescriptionModel
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $this->response->setJSON([
            'success' => true,
            'note'    => $record['note'] ?? '',
        ]);
    }

    /**
     * POST /doctor/prescription/save
     * Body: patient_id, note
     */
    public function savePrescription()
    {
        $this->requireRole(['doctor', 'admin']);

        $patientId = (string) $this->request->getPost('patient_id');
        $note      = (string) $this->request->getPost('note');

        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        $data = [
            'patient_id'     => $patientId,
            'date'           => date('Y-m-d'),
            'payment_method' => 'cash',
            'subtotal'       => 0,
            'tax'            => 0,
            'total_amount'   => 0,
            'note'           => $note,
        ];

        $this->prescriptionModel->skipValidation(true);
        $insertId = $this->prescriptionModel->insert($data);
        $this->prescriptionModel->skipValidation(false);

        if (!$insertId) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Failed to save prescription note',
                ]);
        }

        $latest = $this->prescriptionModel
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Prescription note saved successfully',
            'note'    => $latest['note'] ?? $note,
        ]);
    }

    // ==================== MEDICAL RECORDS METHODS ====================

    /**
     * GET /doctor/medical-records?patient_id=
     * Returns admission history for a patient so EHR can display medical records.
     */
    public function medicalRecords()
    {
        $this->requireRole(['doctor', 'nurse', 'admin', 'receptionist']);

        $patientId = (string) $this->request->getGet('patient_id');
        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        $rows = $this->admissionModel
            ->select([
                'admission_details.id AS admission_id',
                'admission_details.patient_id',
                'admission_details.admission_date',
                'admission_details.admission_time',
                'admission_details.admission_type',
                'admission_details.status AS admission_status',
                'admission_details.admitting_diagnosis',
                'admission_details.reason_admission',
                'admission_details.ward AS admission_ward',
                'admission_details.room AS admission_room',
                'admission_details.updated_at AS update_timestamp',
                'admission_details.attending_doctor_id',
                'beds.ward AS bed_ward',
                'beds.room AS bed_room',
                'beds.bed AS bed_label',
                "COALESCE(NULLIF(CONCAT(TRIM(staff_profiles.first_name), ' ', TRIM(staff_profiles.last_name)), ' '), users.username, CONCAT('Doctor #', staff_profiles.id)) AS physician_name",
                'users.username AS physician_username',
            ])
            ->join('beds', 'beds.id = admission_details.bed_id', 'left')
            ->join('staff_profiles', 'staff_profiles.id = admission_details.attending_doctor_id', 'left')
            ->join('users', 'users.id = staff_profiles.user_id', 'left')
            ->where('admission_details.patient_id', $patientId)
            ->groupBy('admission_details.id')
            ->orderBy('admission_details.admission_date', 'DESC')
            ->orderBy('admission_details.created_at', 'DESC')
            ->findAll();

        $records = array_map(static function (array $row) {
            $dischargeDate = null;
            if (($row['admission_status'] ?? '') === 'discharged') {
                $dischargeDate = $row['update_timestamp'] ?? null;
            }

            return [
                'id' => $row['admission_id'],
                'admission_date' => $row['admission_date'],
                'admission_time' => $row['admission_time'],
                'admission_type' => $row['admission_type'],
                'status' => $row['admission_status'],
                'discharge_date' => $dischargeDate,
                'ward' => $row['admission_ward'] ?? $row['bed_ward'] ?? null,
                'room' => $row['admission_room'] ?? $row['bed_room'] ?? null,
                'bed' => $row['bed_label'] ?? null,
                'physician' => $row['physician_name'] ?? $row['physician_username'] ?? '—',
                'diagnosis' => $row['admitting_diagnosis'] ?? '—',
                'reason' => $row['reason_admission'] ?? null,
            ];
        }, $rows ?? []);

        return $this->response->setJSON([
            'success' => true,
            'records' => $records,
        ]);
    }
}

