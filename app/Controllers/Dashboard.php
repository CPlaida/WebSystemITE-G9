<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $userRole = session()->get('role');
        
        // Redirect based on role
        switch ($userRole) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'doctor':
                return redirect()->to('/doctor/dashboard');
            case 'nurse':
                return redirect()->to('/nurse/dashboard');
            default:
                return redirect()->to('/reception/dashboard');
        }
    }
}
