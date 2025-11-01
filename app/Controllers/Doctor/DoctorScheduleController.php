<?php

namespace App\Controllers\Doctor;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\DoctorScheduleModel;

class DoctorScheduleController extends BaseController
{
    protected $doctorScheduleModel;
    protected $userModel;

    public function __construct()
    {
        $this->doctorScheduleModel = new \App\Models\DoctorScheduleModel();
        $this->userModel = new UserModel();
    }

    public function view()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        if ($userRole !== 'doctor') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Doctor only.');
        }

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

        return view('Roles/doctor/appointments/StaffSchedule', $data);
    }
}
