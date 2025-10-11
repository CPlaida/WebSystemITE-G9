<?php

namespace App\Controllers;
use App\Models\UserModel;

class Admin extends BaseController
{
    public function index()
    {
        // Redirect to the main dashboard
        return redirect()->to('/dashboard');
    }

    public function dashboard()
    {
        // This method is no longer needed as we're using the main dashboard
        return redirect()->to('/dashboard');
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
