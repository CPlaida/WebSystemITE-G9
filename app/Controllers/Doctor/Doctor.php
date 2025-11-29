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
        $schedules = $this->doctorScheduleModel->getSchedulesByDateRange($calendarStart, $calendarEnd);

        // Build dropdown options directly from users who have a doctor role
        // Only show active doctors who are not on leave
        $userModel = new UserModel();
        $db = \Config\Database::connect();
        
        $doctors = $db->table('users u')
            ->select('u.id AS doctor_id, u.id AS user_id, u.username, 
                     sp.specialization_id, ss.name AS specialization,
                     sp.department_id, sd.name AS department, sd.slug AS department_slug, sp.status')
            ->join('roles r', 'u.role_id = r.id', 'left')
            ->join('staff_profiles sp', 'sp.user_id = u.id', 'left')
            ->join('staff_specializations ss', 'ss.id = sp.specialization_id', 'left')
            ->join('staff_departments sd', 'sd.id = sp.department_id', 'left')
            ->like('r.name', 'doctor', 'both')
            ->orderBy('u.username', 'ASC')
            ->get()
            ->getResultArray();
        
        // Filter to only show active doctors (exclude on_leave and inactive)
        $doctors = array_filter($doctors, function($doctor) {
            // Include doctors without staff profile (status is null) or with active status
            return empty($doctor['status']) || $doctor['status'] === 'active';
        });
        
        // Note: doctor_id here is users.id; addSchedule will normalize to doctors.id before insert

        $data = [
            'schedules' => $schedules,
            'doctors' => $doctors,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currentMonth' => $monthParam ?? date('Y-m'),
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
                $shiftStartMap = [
                    'morning' => '06:00:00',
                    'afternoon' => '12:00:00',
                    'night' => '18:00:00',
                    'mid_shift' => '00:00:00' // Flexible, no restriction
                ];
                $type = strtolower(trim($data['shift_type'] ?? ''));
                if (isset($shiftStartMap[$type]) && $type !== 'mid_shift') {
                    $shiftStart = new \DateTime($startDate . ' ' . $shiftStartMap[$type], $tz);
                    if ($now >= $shiftStart) {
                        return $this->response->setStatusCode(400)->setJSON([
                            'success' => false,
                            'message' => 'Cannot add ' . $type . ' shift for today as the start time has already passed.'
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

}