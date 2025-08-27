<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Itstaff extends Controller
{
    public function index()
    {
        // Check if user is logged in and has itstaff role
        if (!session()->get('logged_in') || session()->get('role') !== 'itstaff') {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'IT Staff Dashboard',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('IT_Staff/it_staff', $data);
    }

    public function dashboard()
    {
        return $this->index();
    }
}
