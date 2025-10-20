<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function index()
    {
        // Use unified dashboard for admins
        return redirect()->to('/dashboard');
    }

    public function manageUsers()
    {
        $data = [
            'title' => 'User Management',
        ];
        return view('Roles/admin/Administration/ManageUser', $data);
    }

    public function roleManagement()
    {
        $data = [
            'title' => 'Role Management',
        ];
        return view('Roles/admin/Administration/RoleManagement', $data);
    }
}
