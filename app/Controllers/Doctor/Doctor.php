<?php

namespace App\Controllers\Doctor;
use App\Models\UserModel;
use App\Models\DoctorScheduleModel;
use App\Controllers\BaseController;

class Doctor extends BaseController
{
    protected $doctorScheduleModel;
    
    public function __construct()
    {
        $this->doctorScheduleModel = new DoctorScheduleModel();
    }

    /**
     * Display the doctor scheduling interface
     */
    public function schedule()
    {
        // Check if user is authenticated and has proper role
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'doctor'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Insufficient permissions.');
        }

        // Get current date range (default to current month)
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

        // Get schedules for the date range
        $schedules = $this->doctorScheduleModel->getSchedulesByDateRange($startDate, $endDate);
        
        // Get all doctors for the dropdown (use doctors.id to satisfy FK doctor_schedules.doctor_id -> doctors.id)
        $userModel = new UserModel();
        $doctors = $userModel->select('doctors.id AS doctor_id, users.id AS user_id, users.username, doctors.first_name, doctors.last_name')
                             ->join('roles r', 'users.role_id = r.id', 'left')
                             ->join('doctors', 'doctors.user_id = users.id', 'left')
                             ->where('r.name', 'doctor')
                             ->findAll();
        
        // Debug: Log the doctors data
        log_message('debug', 'Doctors found: ' . count($doctors));
        if (!empty($doctors)) {
            log_message('debug', 'First doctor: ' . json_encode($doctors[0]));
        }

        // Get schedule statistics
        $stats = $this->doctorScheduleModel->getScheduleStats();

        $data = [
            'schedules' => $schedules,
            'doctors' => $doctors,
            'stats' => $stats,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'conflicts' => $this->doctorScheduleModel->getAllConflicts()
        ];

        return view('Roles/admin/appointments/StaffSchedule', $data);
    }

    /**
     * Add a new doctor schedule
     */
    public function addSchedule()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            // Get all POST data for debugging
            $allPostData = $this->request->getPost();
            log_message('debug', 'All POST Data: ' . json_encode($allPostData));
            
            $data = [
                'doctor_id' => $this->request->getPost('doctor_id'),
                'doctor_name' => $this->request->getPost('doctor_name'),
                'department' => $this->request->getPost('department'),
                'shift_type' => $this->request->getPost('shift_type'),
                'shift_date' => $this->request->getPost('shift_date'),
                'notes' => $this->request->getPost('notes') ?? ''
            ];

            // Debug logging
            log_message('debug', 'Add Schedule Data: ' . json_encode($data));

            // Time validation - prevent adding shifts in the past
            // Use Asia/Manila timezone explicitly to match local environment
            $tz = new \DateTimeZone('Asia/Manila');
            $now = new \DateTime('now', $tz);
            $today = $now->format('Y-m-d');
            $currentHour = (int)$now->format('H');

            // If selected date is in the past, block outright
            if (strtotime($data['shift_date']) < strtotime($today)) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Cannot add a shift in the past date.'
                ]);
            }

            // If selected date is today, block if shift start time already passed (exact DateTime check)
            if ($data['shift_date'] === $today) {
                $shiftStartMap = [
                    'morning' => '06:00:00',
                    'afternoon' => '14:00:00',
                    'night' => '22:00:00'
                ];
                $type = strtolower(trim($data['shift_type'] ?? ''));
                if (isset($shiftStartMap[$type])) {
                    $shiftStart = new \DateTime($data['shift_date'] . ' ' . $shiftStartMap[$type], $tz);
                    if ($now >= $shiftStart) {
                        return $this->response->setStatusCode(400)->setJSON([
                            'success' => false,
                            'message' => 'Cannot add ' . $type . ' shift for today as the start time has already passed.'
                        ]);
                    }
                }
            }

            // Additional validation for consecutive night shifts
            if ($data['shift_type'] === 'night') {
                if (!$this->doctorScheduleModel->canWorkConsecutiveNights($data['doctor_id'], $data['shift_date'])) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'Doctor cannot work consecutive night shifts. Please choose a different doctor or date.'
                    ]);
                }
            }

            // Validate required fields
            if (empty($data['doctor_id']) || empty($data['shift_date']) || empty($data['shift_type'])) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false, 
                    'message' => 'Missing required fields: doctor_id, shift_date, or shift_type'
                ]);
            }

            // Check if DoctorScheduleModel exists and is loaded
            if (!$this->doctorScheduleModel) {
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false, 
                    'message' => 'DoctorScheduleModel not loaded'
                ]);
            }

            $result = $this->doctorScheduleModel->addSchedule($data);
            
            log_message('debug', 'Add Schedule Result: ' . json_encode($result));
            
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
     * Get conflicts for a specific schedule
     */
    public function getConflicts()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $doctorId = $this->request->getPost('doctor_id');
        $shiftDate = $this->request->getPost('shift_date');
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        $excludeId = $this->request->getPost('exclude_id');

        $conflicts = $this->doctorScheduleModel->checkConflicts(
            $doctorId,
            $shiftDate,
            $startTime,
            $endTime,
            $excludeId
        );

        return $this->response->setJSON([
            'success' => true,
            'conflicts' => $conflicts
        ]);
    }

    /**
     * Get schedule data for AJAX requests
     */
    public function getScheduleData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $view = $this->request->getGet('view') ?? 'month';
        $date = $this->request->getGet('date') ?? date('Y-m-d');

        switch ($view) {
            case 'day':
                $schedules = $this->doctorScheduleModel->getSchedulesByDateRange($date, $date);
                break;
            case 'week':
                $startDate = date('Y-m-d', strtotime('monday this week', strtotime($date)));
                $endDate = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
                $schedules = $this->doctorScheduleModel->getSchedulesByDateRange($startDate, $endDate);
                break;
            case 'month':
            default:
                $startDate = date('Y-m-01', strtotime($date));
                $endDate = date('Y-m-t', strtotime($date));
                $schedules = $this->doctorScheduleModel->getSchedulesByDateRange($startDate, $endDate);
                break;
        }

        return $this->response->setJSON([
            'success' => true,
            'schedules' => $schedules,
            'view' => $view,
            'date' => $date
        ]);
    }

    /**
     * Get doctor information for dropdown
     */
    public function getDoctors()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $userModel = new UserModel();
        $doctors = $userModel->select('doctors.id AS doctor_id, users.username, users.email, doctors.first_name, doctors.last_name')
                             ->join('roles r', 'users.role_id = r.id', 'left')
                             ->join('doctors', 'doctors.user_id = users.id', 'left')
                             ->where('r.name', 'doctor')
                             ->findAll();

        return $this->response->setJSON(['success' => true, 'doctors' => $doctors]);
    }
}
