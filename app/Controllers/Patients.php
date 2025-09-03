<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Patients extends BaseController
{
    public function register()
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('login');
        }

        if (session('role') !== 'admin') {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Register New Patient',
            'active_menu' => 'patients'
        ];

        return view('admin/patients/register', $data);
    }

    public function view()
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect()->to('login');
        }

        // Check if user has admin role
        if (session('role') !== 'admin') {
            return redirect()->back()->with('error', 'You do not have permission to access this page.');
        }

        $data = [
            'title' => 'View Patients',
            'active_menu' => 'patients'
        ];

        return view('admin/patients/view', $data);
    }
}
