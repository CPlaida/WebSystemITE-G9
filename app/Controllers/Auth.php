<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    public function process_login()
    {
        $session = session();
        $model = new UserModel();

        // Accept both 'email' and 'Email' keys from the form
        $identity = $this->request->getPost('email') ?? $this->request->getPost('Email');
        $password = $this->request->getPost('password');

        // Basic validation
        if (empty($identity) || empty($password)) {
            return redirect()->back()->with('error', 'Please enter your email/username and password.');
        }

        // Find user by email or username
        $user = $model->where('email', $identity)
                      ->orWhere('username', $identity)
                      ->first();

        if ($user) {
            if ($user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Account is inactive, contact admin.');
            }

            if (password_verify($password, $user['password'])) {
                // Set session
                $session->set([
                    'user_id'  => $user['id'],
                    'username' => $user['username'],
                    'role'     => $user['role'],
                    'logged_in'=> true
                ]);
          

               // Redirect based on role
            if ($user['role'] === 'admin') {
                return redirect()->to('/admin/dashboard');
            } elseif ($user['role'] === 'doctor') {
                return redirect()->to('/doctor/dashboard');
            } elseif ($user['role'] === 'nurse') {
                return redirect()->to('/nurse/dashboard');
            } elseif ($user['role'] === 'accounting') {
                return redirect()->to('/accounting/dashboard');
            } elseif ($user['role'] === 'itstaff') {
                return redirect()->to('/itstaff/dashboard');
            } elseif ($user['role'] === 'labstaff') {
                return redirect()->to('/laboratory/dashboard');
            } elseif ($user['role'] === 'pharmacist') {
                return redirect()->to('/pharmacy/dashboard');
            } else {
                return redirect()->to('/reception/dashboard');
            }
            } else {
                return redirect()->back()->with('error', 'Wrong password.');
            }
        }

        return redirect()->back()->with('error', 'Invalid username/email or password.');
    }

    public function register()
    {
        return view('auth/register');
    }

    public function process_register()
    {
        $model = new UserModel();

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role') ?? 'receptionist',
            'status'   => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $model->insert($data);

        return redirect()->to('/login')->with('success', 'Registration successful! Please login.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
