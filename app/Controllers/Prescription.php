<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Prescription extends Controller
{
    public function index()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist','admin'])) {
            return redirect()->to('/login');
        }
        return view('Roles/admin/pharmacy/PrescriptionDispencing', ['title' => 'Prescription Dispensing']);
    }
}