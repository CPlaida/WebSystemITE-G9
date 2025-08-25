<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    public function login()
    {
        return view('auth/login');
    }
    
    public function process_login()
    {
        $email = $this->request->getPost('Email');
        $password = $this->request->getPost('password');
        
        // Load database and query builder
        $db = \Config\Database::connect();
        $user = $db->table('users')
                  ->where('email', $email)
                  ->get()
                  ->getRow();
        
        if ($user && password_verify($password, $user->password)) {
            // Set user session
            $session = session();
            $userData = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => isset($user->role) ? $user->role : 'staff',
                'is_logged_in' => true
            ];
            $session->set($userData);
            
            // Redirect to dashboard
            return redirect()->to('/dashboard');
        }
        
        // If authentication fails, redirect back to login with error
        return redirect()->back()->with('error', 'Invalid email or password');
    }
    
    public function register()
    {
        return view('auth/register');
    }
    
    public function process_register()
    {
        // Get form data
        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'birthdate' => $this->request->getPost('birthdate'),
            'gender' => $this->request->getPost('gender'),
            'contact_number' => $this->request->getPost('contact_number'),
            'address' => $this->request->getPost('address'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // TODO: Add validation and database insertion logic here
        // For now, just redirect to login with success message
        
        return redirect()->to('/login')->with('success', 'Registration successful! Please login with your credentials.');
    }
    
    public function logout()
    {
        // Destroy the session
        $session = session();
        $session->destroy();
        
        // Redirect to home page with success message
        return redirect()->to('/')->with('success', 'You have been logged out successfully.');
    }
}
