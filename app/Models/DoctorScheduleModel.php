<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorScheduleModel extends Model
{
    protected $table = 'doctor_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $beforeInsert = ['generateId'];
    protected $allowedFields = [
        'id',
        'doctor_id',
        'doctor_name',
        'department',
        'shift_type',
        'shift_date',
        'start_time',
        'end_time',
        'status',
        'notes'
    ];

    /**
     * Generate string primary key: SCH-yymmdd-#### (daily sequence)
     */
    protected function generateId(array $data)
    {
        if (!empty($data['data']['id'])) {
            return $data;
        }
        $datePart = date('ymd');
        $prefix = 'SCH-' . $datePart . '-';
        $like = $prefix . '%';

        $row = $this->db->table($this->table)
            ->select('id')
            ->like('id', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRowArray();

        $next = 1;
        if ($row && isset($row['id'])) {
            $parts = explode('-', $row['id']);
            $lastSeq = end($parts);
            if (is_numeric($lastSeq)) {
                $next = (int)$lastSeq + 1;
            }
        }
        $data['data']['id'] = $prefix . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
        return $data;
    }

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'doctor_id' => 'required',
        'doctor_name' => 'required|max_length[255]',
        'department' => 'required|max_length[100]',
        'shift_type' => 'required|in_list[morning,afternoon,night,mid_shift]',
        'shift_date' => 'required|valid_date',
        'start_time' => 'required',
        'end_time' => 'required',
        'status' => 'in_list[scheduled,completed,cancelled]'
    ];

    protected $validationMessages = [
        'doctor_id' => [
            'required' => 'Doctor ID is required'
        ],
        'doctor_name' => [
            'required' => 'Doctor name is required'
        ],
        'department' => [
            'required' => 'Department is required'
        ],
        'shift_type' => [
            'required' => 'Shift type is required',
            'in_list' => 'Shift type must be morning, afternoon, or night'
        ],
        'shift_date' => [
            'required' => 'Shift date is required',
            'valid_date' => 'Please provide a valid date'
        ]
    ];

    /**
     * Get all schedules for a specific date range
     */
    public function getSchedulesByDateRange($startDate, $endDate)
    {
        return $this->where('shift_date >=', $startDate)
                   ->where('shift_date <=', $endDate)
                   ->orderBy('shift_date', 'ASC')
                   ->orderBy('start_time', 'ASC')
                   ->findAll();
    }

    /**
     * Get all schedules for a specific date
     * @param string $date Date in YYYY-MM-DD format
     * @return array Array of schedule records
     */
    public function getByDate($date)
    {
        return $this->where('shift_date', $date)
                   ->where('status !=', 'cancelled')
                   ->orderBy('start_time', 'ASC')
                   ->orderBy('doctor_name', 'ASC')
                   ->findAll();
    }

    

    /**
     * Check for scheduling conflicts
     */
    public function checkConflicts($doctorId, $shiftDate, $startTime, $endTime, $excludeId = null)
    {
        // Build full DateTime interval for the proposed shift
        $proposedStart = new \DateTime($shiftDate . ' ' . $startTime);
        $proposedEnd = new \DateTime($shiftDate . ' ' . $endTime);
        if ($proposedEnd <= $proposedStart) {
            // Cross-midnight shift (e.g., 22:00 -> 06:00 next day)
            $proposedEnd->modify('+1 day');
        }

        // Fetch existing schedules on adjacent days to catch cross-midnight overlaps
        $dayBefore = date('Y-m-d', strtotime($shiftDate . ' -1 day'));
        $dayAfter  = date('Y-m-d', strtotime($shiftDate . ' +1 day'));

        $builder = $this->where('doctor_id', $doctorId)
                        ->whereIn('shift_date', [$dayBefore, $shiftDate, $dayAfter])
                        ->where('status !=', 'cancelled');

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        $existing = $builder->findAll();

        // Compare intervals in PHP to robustly handle cross-midnight cases
        $conflicts = [];
        foreach ($existing as $row) {
            $rowStart = new \DateTime($row['shift_date'] . ' ' . $row['start_time']);
            $rowEnd = new \DateTime($row['shift_date'] . ' ' . $row['end_time']);
            if ($rowEnd <= $rowStart) {
                $rowEnd->modify('+1 day');
            }

            // Overlap check: startA < endB AND endA > startB
            if ($rowStart < $proposedEnd && $rowEnd > $proposedStart) {
                $conflicts[] = $row;
            }
        }

        return $conflicts;
    }

    /**
     * Get all conflicts in the system
     */
    public function getAllConflicts()
    {
        $sql = "SELECT ds1.*, ds2.id as conflict_id, ds2.start_time as conflict_start, ds2.end_time as conflict_end
                FROM doctor_schedules ds1
                JOIN doctor_schedules ds2 ON ds1.doctor_id = ds2.doctor_id 
                    AND ds1.shift_date = ds2.shift_date 
                    AND ds1.id != ds2.id
                    AND ds1.status != 'cancelled'
                    AND ds2.status != 'cancelled'
                    AND ds1.start_time < ds2.end_time 
                    AND ds1.end_time > ds2.start_time
                ORDER BY ds1.shift_date DESC, ds1.start_time ASC";
        
        return $this->db->query($sql)->getResultArray();
    }

    

    /**
     * Get schedule statistics
     */
    public function getScheduleStats()
    {
        $today = date('Y-m-d');
        
        return [
            'total_schedules' => $this->countAll(),
            'today_schedules' => $this->where('shift_date', $today)->countAllResults(),
            'upcoming_schedules' => $this->where('shift_date >', $today)->countAllResults(),
            'conflicts' => count($this->getAllConflicts()),
            'departments' => $this->select('department')
                                 ->distinct()
                                 ->findColumn('department')
        ];
    }

    /**
     * Get shift time ranges based on shift type - Standard templates
     */
    public function getShiftTimes($shiftType)
    {
        $times = [
            'morning' => ['06:00:00', '12:00:00'],   // 6 AM - 12 PM
            'afternoon' => ['12:00:00', '18:00:00'], // 12 PM - 6 PM
            'night' => ['18:00:00', '06:00:00'],     // 6 PM - 6 AM
            'mid_shift' => ['09:00:00', '17:00:00']  // Flexible: 9 AM - 5 PM (default)
        ];
        
        return $times[$shiftType] ?? ['08:00:00', '16:00:00'];
    }

    /**
     * Check if doctor can work consecutive night shifts
     */
    public function canWorkConsecutiveNights($doctorId, $shiftDate)
    {
        // Get previous day's schedule
        $previousDate = date('Y-m-d', strtotime($shiftDate . ' -1 day'));
        $previousShift = $this->where('doctor_id', $doctorId)
                             ->where('shift_date', $previousDate)
                             ->where('shift_type', 'night')
                             ->where('status !=', 'cancelled')
                             ->first();
        
        return !$previousShift; // Can work if no night shift previous day
    }


    /**
     * Add schedules for a date range
     */
    public function addScheduleRange($data)
    {
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $doctorId = $data['doctor_id'];
        $shiftType = $data['shift_type'];
        
        // Get shift times
        $times = $this->getShiftTimes($shiftType);
        $startTime = $times[0];
        $endTime = $times[1];
        
        // Check for conflicts across the entire date range first
        $conflicts = [];
        $currentDate = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);
        
        while ($currentDate <= $endDateTime) {
            $dateStr = $currentDate->format('Y-m-d');
            $dateConflicts = $this->checkConflicts($doctorId, $dateStr, $startTime, $endTime);
            if (!empty($dateConflicts)) {
                $conflicts = array_merge($conflicts, $dateConflicts);
            }
            $currentDate->modify('+1 day');
        }
        
        if (!empty($conflicts)) {
            return [
                'success' => false,
                'message' => 'This doctor already has a schedule during the selected date and shift.',
                'conflicts' => $conflicts
            ];
        }
        
        // Create schedules for each day in the range
        $created = 0;
        $skipped = 0;
        $errors = [];
        
        $currentDate = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);
        $tz = new \DateTimeZone('Asia/Manila');
        $now = new \DateTime('now', $tz);
        $today = $now->format('Y-m-d');
        
        while ($currentDate <= $endDateTime) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Skip past dates
            if (strtotime($dateStr) < strtotime($today)) {
                $skipped++;
                $currentDate->modify('+1 day');
                continue;
            }
            
            // Skip if today and shift time has passed
            if ($dateStr === $today) {
                $shiftStartMap = [
                    'morning' => '06:00:00',
                    'afternoon' => '12:00:00',
                    'night' => '18:00:00',
                    'mid_shift' => '00:00:00'
                ];
                if (isset($shiftStartMap[$shiftType]) && $shiftType !== 'mid_shift') {
                    $shiftStart = new \DateTime($dateStr . ' ' . $shiftStartMap[$shiftType], $tz);
                    if ($now >= $shiftStart) {
                        $skipped++;
                        $currentDate->modify('+1 day');
                        continue;
                    }
                }
            }
            
            // Check if doctor is on leave for this specific date (if you have leave dates table)
            // For now, we'll skip if there's a general on_leave status
            
            // Prepare schedule data for this date
            $scheduleData = [
                'doctor_id' => $doctorId,
                'doctor_name' => $data['doctor_name'],
                'department' => $data['department'],
                'shift_type' => $shiftType,
                'shift_date' => $dateStr,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'scheduled',
                'notes' => $data['notes'] ?? ''
            ];
            
            // Insert schedule
            $insertResult = $this->insert($scheduleData);
            if ($insertResult) {
                $created++;
            } else {
                $errors[] = "Failed to create schedule for {$dateStr}";
                $skipped++;
            }
            
            $currentDate->modify('+1 day');
        }
        
        if ($created > 0) {
            $message = "Doctor schedule successfully added for {$created} date(s).";
            if ($skipped > 0) {
                $message .= " {$skipped} date(s) were skipped (past dates or conflicts).";
            }
            return [
                'success' => true,
                'message' => $message,
                'created' => $created,
                'skipped' => $skipped,
                'errors' => $errors
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No schedules were created. All dates were skipped or had errors.',
                'created' => 0,
                'skipped' => $skipped,
                'errors' => $errors
            ];
        }
    }

    /**
     * Add a new schedule with automatic time setting
     */
    public function addSchedule($data)
    {
        // Set automatic times based on shift type
        if (isset($data['shift_type']) && !isset($data['start_time'])) {
            $times = $this->getShiftTimes($data['shift_type']);
            $data['start_time'] = $times[0];
            $data['end_time'] = $times[1];
        }

        // Enforce past-time rules at the model layer as well
        if (!empty($data['shift_date'])) {
            $tz = new \DateTimeZone('Asia/Manila');
            $now = new \DateTime('now', $tz);
            $today = $now->format('Y-m-d');

            // Block past dates outright
            if (strtotime($data['shift_date']) < strtotime($today)) {
                return [
                    'success' => false,
                    'message' => 'Cannot add a shift in the past date.'
                ];
            }

            // If today, block if start already passed
            if ($data['shift_date'] === $today && !empty($data['shift_type'])) {
                $startMap = [
                    'morning' => '06:00:00',
                    'afternoon' => '14:00:00',
                    'night' => '22:00:00'
                ];
                $type = strtolower(trim($data['shift_type']));
                if (isset($startMap[$type])) {
                    $shiftStart = new \DateTime($data['shift_date'] . ' ' . $startMap[$type], $tz);
                    if ($now >= $shiftStart) {
                        return [
                            'success' => false,
                            'message' => 'Cannot add ' . $type . ' shift for today as the start time has already passed.'
                        ];
                    }
                }
            }
        }
        
        // Check for conflicts before inserting
        if (isset($data['doctor_id'], $data['shift_date'], $data['start_time'], $data['end_time'])) {
            $conflicts = $this->checkConflicts(
                $data['doctor_id'],
                $data['shift_date'],
                $data['start_time'],
                $data['end_time']
            );
            
            if (!empty($conflicts)) {
                return [
                    'success' => false,
                    'message' => 'Scheduling conflict detected',
                    'conflicts' => $conflicts
                ];
            }
        }
        
        // Add default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'scheduled';
        }
        
        $result = $this->insert($data);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Schedule added successfully',
                'id' => $result
            ];
        } else {
            $errors = $this->errors();
            log_message('error', 'Failed to insert schedule. Errors: ' . json_encode($errors));
            return [
                'success' => false,
                'message' => 'Failed to add schedule: ' . implode(', ', $errors),
                'errors' => $errors
            ];
        }
    }
}
