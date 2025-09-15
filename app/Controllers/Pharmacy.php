<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Pharmacy extends Controller
{
    public function index()
    {
        // Check if user is logged in and has pharmacist role
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
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

    public function medicine()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Medicine Inventory',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('admin/InventoryMan/Medicine', $data);
    }

    public function inventory()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Pharmacy Inventory',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('admin/InventoryMan/PharmacyInventory', $data);
    }
}
