<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Laboratory extends Controller
{
    public function index()
    {
        // Check if user is logged in and has labstaff role
        if (!session()->get('logged_in') || session()->get('role') !== 'labstaff') {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Laboratory Dashboard',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Lab_staff/lab_staff', $data);
    }

    public function dashboard()
    {
        return $this->index();
    }
}
