<?php

namespace App\Models;

use CodeIgniter\Model;

class DoctorScheduleModel extends Model
{
    protected $table = 'doctor_schedules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
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

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'doctor_id' => 'required|integer',
        'doctor_name' => 'required|max_length[255]',
        'department' => 'required|max_length[100]',
        'shift_type' => 'required|in_list[morning,afternoon,night]',
        'shift_date' => 'required|valid_date',
        'start_time' => 'required',
        'end_time' => 'required',
        'status' => 'in_list[scheduled,completed,cancelled]'
    ];

    protected $validationMessages = [
        'doctor_id' => [
            'required' => 'Doctor ID is required',
            'integer' => 'Doctor ID must be a valid number'
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
     * Get schedules for a specific doctor
     */
    public function getDoctorSchedules($doctorId, $startDate = null, $endDate = null)
    {
        $builder = $this->where('doctor_id', $doctorId);
        
        if ($startDate) {
            $builder->where('shift_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('shift_date <=', $endDate);
        }
        
        return $builder->orderBy('shift_date', 'ASC')
                      ->orderBy('start_time', 'ASC')
                      ->findAll();
    }

    /**
     * Check for scheduling conflicts
     */
    public function checkConflicts($doctorId, $shiftDate, $startTime, $endTime, $excludeId = null)
    {
        $builder = $this->where('doctor_id', $doctorId)
                       ->where('shift_date', $shiftDate)
                       ->where('status !=', 'cancelled')
                       ->groupStart()
                           ->where('start_time <', $endTime)
                           ->where('end_time >', $startTime)
                       ->groupEnd();
        
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        
        return $builder->findAll();
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
     * Get schedules by department
     */
    public function getSchedulesByDepartment($department, $startDate = null, $endDate = null)
    {
        $builder = $this->where('department', $department);
        
        if ($startDate) {
            $builder->where('shift_date >=', $startDate);
        }
        
        if ($endDate) {
            $builder->where('shift_date <=', $endDate);
        }
        
        return $builder->orderBy('shift_date', 'ASC')
                      ->orderBy('start_time', 'ASC')
                      ->findAll();
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
     * Get shift time ranges based on shift type
     */
    public function getShiftTimes($shiftType)
    {
        $times = [
            'morning' => ['06:00:00', '14:00:00'],
            'afternoon' => ['10:00:00', '16:00:00'],
            'night' => ['16:00:00', '22:00:00']
        ];
        
        return $times[$shiftType] ?? ['08:00:00', '16:00:00'];
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
        
        // Log the data being inserted for debugging
        log_message('debug', 'DoctorScheduleModel inserting data: ' . json_encode($data));
        
        $result = $this->insert($data);
        
        if ($result) {
            log_message('debug', 'Schedule inserted successfully with ID: ' . $result);
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
