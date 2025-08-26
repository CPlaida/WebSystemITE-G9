<?php

namespace App\Controllers;
use App\Models\UserModel;

class Doctor extends BaseController
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $userModel = new UserModel();

        // Example counts specific for doctors (you can customize)
        $data['totalPatients'] = $userModel->where('role', 'patient')->countAllResults();

        // Latest 5 patients (example, depends on your table data)
        $data['latestPatients'] = $userModel->where('role', 'patient')
                                            ->orderBy('created_at', 'DESC')
                                            ->findAll(5);

        return view('doctor/dashboard', $data);
    }
}
