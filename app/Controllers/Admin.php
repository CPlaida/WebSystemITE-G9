<?php

namespace App\Controllers;
use App\Models\UserModel;

class Admin extends BaseController
{
    public function index()
    {
        return view('admin/dashboard');
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

    /**
     * Display the User Management page
     */
    public function manageUsers()
    {
        $data = [
            'title' => 'User Management',
            // Add any data you want to pass to the view here
        ];
        
        return view('admin/Administration/ManageUser', $data);
    }

    /**
     * Display the Role Management page
     */
    public function roleManagement()
    {
        $data = [
            'title' => 'Role Management',
            // Add any data needed for the role management view
        ];
        
        return view('admin/Administration/RoleManagement', $data);
    }
}
