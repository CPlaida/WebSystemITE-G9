<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Pharmacy extends Controller
{
    public function index()
    {
        // Check if user is logged in and has pharmacist role
        if (!session()->get('logged_in') || session()->get('role') !== 'pharmacist') {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Pharmacy Dashboard',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('pharmacy/dashboard', $data);
    }

    public function dashboard()
    {
        return $this->index();
    }
}
