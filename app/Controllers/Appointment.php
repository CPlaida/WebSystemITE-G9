<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Appointment extends BaseController
{
    public function book()
    {
        // Check if user is logged in and has admin role
        if (session('role') !== 'admin') {
            return redirect()->to('login');
        }

        $data = [
            'title' => 'Book Appointment',
            'active_menu' => 'appointments'
        ];
        
        return view('admin/appointments/Bookappointment', $data);
    }
    
    public function index()
    {
        // Check if user is logged in and has admin role
        if (session('role') !== 'admin') {          
         return redirect()->to('login');
        }

        $data = [
            'title' => 'Appointment List',
            'active_menu' => 'appointments'
        ];
        
        return view('admin/appointments/Appointmentlist', $data);
    }
    
    public function schedule()
    {
        // Check if user is logged in and has admin role
        if (session('role') !== 'admin') {            
            return redirect()->to('login');
        }

        $data = [
            'title' => 'Staff Schedule',
            'active_menu' => 'appointments'
        ];
        
        return view('admin/appointments/StaffSchedule', $data);
    }
}
