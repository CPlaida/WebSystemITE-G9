<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends Controller
{
    public function login()
    {
        // If user is already logged in, redirect to appropriate dashboard
        if (session()->get('logged_in')) {
            return $this->redirectToDashboard(session()->get('role'));
        }
        return view('auth/login');
    }

    public function process_login()
    {
        $session = session();
        $model = new UserModel();

<<<<<<< HEAD
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

=======
        // Get form data
        $email = $this->request->getPost('email') ?? $this->request->getPost('Email');
        $password = $this->request->getPost('password');

        // Basic validation
        if (empty($email) || empty($password)) {
            return redirect()->back()->with('error', 'Please enter your email and password.');
        }

        // Find user by email
        $user = $model->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Invalid email or password.');
        }

        if ($user['status'] !== 'active') {
            return redirect()->back()->with('error', 'Account is inactive. Please contact the administrator.');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->with('error', 'Invalid email or password.');
        }

        // Set user session
        $session->set([
            'user_id'   => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'logged_in' => true
        ]);

        // Redirect to appropriate dashboard
        return $this->redirectToDashboard($user['role']);
    }

    protected function redirectToDashboard($role)
    {
        $role = strtolower(trim($role));
        
        $dashboards = [
            'hospital administrator' => '/admin/dashboard',
            'doctor'                => '/doctor/dashboard',
            'nurse'                 => '/nurse/dashboard',
            'accountant'            => '/accounting/dashboard',
            'it staff'              => '/itstaff/dashboard',
            'laboratory staff'      => '/laboratory/dashboard',
            'pharmacist'            => '/pharmacy/dashboard',
            'receptionist'          => '/reception/dashboard'
        ];

        // Default to receptionist dashboard if role not found
        $dashboard = $dashboards[$role] ?? '/reception/dashboard';
        
        return redirect()->to($dashboard);
    }

>>>>>>> 57646d5 (Initial commit)
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
