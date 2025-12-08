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
        
        // Note: doctor_id here is users.id; addSchedule will normalize to doctors.id before insert

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
        $doctor = $this->userModel->select('doctors.id AS doctor_id, users.id AS user_id, users.username, doctors.first_name, doctors.last_name')
                                ->join('roles r', 'users.role_id = r.id', 'left')
                                ->join('doctors', 'doctors.user_id = users.id', 'left')
                                ->where('users.id', $doctorId)
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
                'doctor_name' => $this->request->getPost('doctor_name'),
                'department' => $this->request->getPost('department'),
                'shift_type' => $this->request->getPost('shift_type'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'notes' => $this->request->getPost('notes') ?? ''
            ];

            // Normalize/validate doctor_id (users.id as VARCHAR) and ensure a doctor profile exists
            try {
                $db = \Config\Database::connect();
                $doctorsTbl = $db->table('doctors');
                $usersTbl = $db->table('users');

                $providedId = trim((string)($data['doctor_id'] ?? ''));
                if ($providedId !== '') {
                    // Ensure user exists
                    $userRow = $usersTbl->where('id', $providedId)->get()->getRowArray();
                    if ($userRow) {
                        // Ensure doctors profile exists keyed by user_id
                        $existing = $doctorsTbl->where('user_id', $userRow['id'])->get()->getRowArray();
                        if (!$existing) {
                            $email = trim((string)($userRow['email'] ?? ''));
                            if ($email === '') {
                                $email = 'doc_' . $userRow['id'] . '@local'; // unique fallback
                            }
                            $uname = (string) ($userRow['username'] ?? 'Doctor User');
                            $parts = preg_split('/\s+/', trim($uname));
                            $first = ucfirst($parts[0] ?? 'Doctor');
                            $last  = ucfirst($parts[1] ?? 'User');
                            $insert = [
                                'user_id'          => (string)$userRow['id'],
                                'email'            => $email,
                                'first_name'       => $first,
                                'last_name'        => $last,
                                'specialization'   => 'General',
                                'license_number'   => 'LIC-' . $userRow['id'], // unique fallback
                                'experience_years' => 0,
                                'consultation_fee' => 0.00,
                                'status'           => 'active',
                            ];
                            $doctorsTbl->insert($insert);
                        }
                        if (empty($data['doctor_name'])) {
                            $uname = (string) ($userRow['username'] ?? 'Doctor');
                            $data['doctor_name'] = ucfirst(str_replace('dr.', 'Dr. ', $uname));
                        }
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'Doctor ID normalization failed: ' . $e->getMessage());
            }

            // Safety: verify doctor_id points to a valid doctor user (users.id with doctor role)
            try {
                $db = \Config\Database::connect();
                $doctorUser = $db->table('users u')
                    ->select('u.id')
                    ->join('roles r', 'u.role_id = r.id', 'left')
                    ->where('u.id', trim((string)($data['doctor_id'] ?? '')))
                    ->groupStart()
                        ->like('r.name', 'doctor', 'both')
                    ->groupEnd()
                    ->get()->getRowArray();
                if (!$doctorUser) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'Unable to resolve doctor. Please select a valid doctor user.'
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
            if (empty($data['doctor_id']) || empty($data['shift_type']) || empty($data['department'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false, 
                    'message' => 'Missing required fields: doctor_id, shift_type, or department'
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
            'doctor_name' => $this->request->getPost('doctor_name'),
            'department' => $this->request->getPost('department'),
            'shift_type' => $this->request->getPost('shift_type'),
            'shift_date' => $this->request->getPost('shift_date'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes') ?? ''
        ];

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

        $vitals = $this->vitalModel->getLatestForPatient($patientId);

        if (!$vitals) {
            $patient = $this->patientModel
                ->select('id, vitals_bp, vitals_hr, vitals_temp, updated_at, created_at')
                ->find($patientId);

            if ($patient) {
                $vitals = [
                    'patient_id'     => (string) $patient['id'],
                    'blood_pressure' => $patient['vitals_bp'],
                    'heart_rate'     => $patient['vitals_hr'],
                    'temperature'    => $patient['vitals_temp'],
                    'created_at'     => $patient['updated_at'] ?? $patient['created_at'],
                    'source'         => 'patient_record',
                ];
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'vitals'  => $vitals,
        ]);
    }

    /**
     * POST /doctor/vitals/save
     * Body: patient_id, blood_pressure, heart_rate, temperature
     */
    public function saveVitals()
    {
        $this->requireRole(['doctor', 'nurse', 'admin']);

        $data = [
            'patient_id'     => (string) $this->request->getPost('patient_id'),
            'blood_pressure' => trim((string) $this->request->getPost('blood_pressure')),
            'heart_rate'     => $this->request->getPost('heart_rate'),
            'temperature'    => $this->request->getPost('temperature'),
            'recorded_by'    => (int) session()->get('user_id'),
        ];

        if ($data['patient_id'] === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        if (!$this->vitalModel->save($data)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Failed to save vitals',
                    'errors'  => $this->vitalModel->errors(),
                ]);
        }

        $latest = $this->vitalModel->getLatestForPatient($data['patient_id']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Vitals saved successfully',
            'vitals'  => $latest,
        ]);
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
                "COALESCE(NULLIF(CONCAT(TRIM(doctors.first_name), ' ', TRIM(doctors.last_name)), ' '), users.username, CONCAT('Doctor #', doctors.id)) AS physician_name",
                'users.username AS physician_username',
            ])
            ->join('beds', 'beds.id = admission_details.bed_id', 'left')
            ->join('doctors', 'doctors.id = admission_details.attending_doctor_id', 'left')
            ->join('users', 'users.id = doctors.user_id', 'left')
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

