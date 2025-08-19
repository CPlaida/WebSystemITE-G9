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
        // Get the input
        $username = $this->request->getPost('Email');
        $password = $this->request->getPost('password');
        
        // TODO: Add actual authentication logic here
        // For now, just redirect to dashboard if username and password are not empty
        if (!empty($username) && !empty($password)) {
            // Set user session or token here
            return redirect()->to('/dashboard');
        }
        
        // If authentication fails, redirect back to login with error
        return redirect()->back()->with('error', 'Invalid username or password');
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
}
