<?php

namespace App\Controllers;
use App\Models\UserModel;

class Admin extends BaseController
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $userModel = new UserModel();

        // Counts
        $data['totalUsers'] = $userModel->countAll();
        $data['totalDoctors'] = $userModel->where('role', 'doctor')->countAllResults();
        $data['totalNurses'] = $userModel->where('role', 'nurse')->countAllResults();
        $data['totalReceptionists'] = $userModel->where('role', 'receptionist')->countAllResults();

        // Latest 5 users
        $data['latestUsers'] = $userModel->orderBy('created_at', 'DESC')->findAll(5);

        return view('admin/dashboard', $data);
    }
}
