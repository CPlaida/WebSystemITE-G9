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

        // Build dropdown options directly from users who have a doctor role
        $userModel = new UserModel();
        $doctors = $userModel->select('users.id AS doctor_id, users.id AS user_id, users.username')
                             ->join('roles r', 'users.role_id = r.id', 'left')
                             ->like('r.name', 'doctor', 'both')
                             ->orderBy('users.username', 'ASC')
                             ->findAll();
        // Note: doctor_id here is users.id; addSchedule will normalize to doctors.id before insert

        $data = [
            'schedules' => $schedules,
            'doctors' => $doctors,
            'startDate' => $startDate,
            'endDate' => $endDate,
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
            $data = [
                'doctor_id' => $this->request->getPost('doctor_id'),
                'doctor_name' => $this->request->getPost('doctor_name'),
                'department' => $this->request->getPost('department'),
                'shift_type' => $this->request->getPost('shift_type'),
                'shift_date' => $this->request->getPost('shift_date'),
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

            $result = $this->doctorScheduleModel->addSchedule($data);
            
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