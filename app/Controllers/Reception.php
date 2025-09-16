<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Reception extends Controller
{
    public function index()
    {
        // Check if user is logged in and has receptionist role
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'receptionist') {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Reception Dashboard',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Reception/dashboard', $data);
    }

    public function dashboard()
    {
        return $this->index();
    }
}
