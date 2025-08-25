<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        
        // Check if user is logged in
        if (!$session->has('is_logged_in')) {
            return redirect()->to('/login');
        }

        $userRole = $session->get('user_role');

        $roleViewMap = [
            'Hospital Administrator' => 'dashboard/admin',
            'Doctor'                 => 'dashboard/doctor',
            'Nurse'                  => 'dashboard/nurse',
            'Receptionist'           => 'dashboard/receptionist',
            'Laboratory Staff'       => 'dashboard/lab_staff',
            'Pharmacist'             => 'dashboard/pharmacist',
            'Accountant'             => 'dashboard/accountant',
            'IT Staff'               => 'dashboard/it_staff',
        ];

        if (isset($roleViewMap[$userRole]) && is_file(APPPATH . 'Views/' . $roleViewMap[$userRole] . '.php')) {
            return view($roleViewMap[$userRole]);
        } else {
            // Fallback for any other roles or if role is not set
            return redirect()->to('/login')->with('error', 'Dashboard not found for your role.');
        }
    }
}
