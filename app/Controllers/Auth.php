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

        $identity = $this->request->getPost('email') ?? $this->request->getPost('Email');
        $password = $this->request->getPost('password');

        $identity = trim((string) $identity);
        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            $identity = strtolower($identity);
        }

        if (empty($identity) || empty($password)) {
            return redirect()->back()->with('error', 'Please enter your email/username and password.');
        }

        $user = $model->where('email', $identity)
                      ->orWhere('username', $identity)
                      ->first();

        if ($user) {
            if ($user['status'] !== 'active') {
                return redirect()->back()->with('error', 'Your account is inactive. Please contact the administrator.');
            }

            if (password_verify($password, $user['password'])) {
                $db = \Config\Database::connect();
                $roleRow = null;
                if (!empty($user['role_id'])) {
                    $roleRow = $db->table('roles')->where('id', $user['role_id'])->get()->getRowArray();
                }
                $roleName = $roleRow['name'] ?? null;
                if (empty($roleName)) {
                    return redirect()->back()->with('error', 'No role assigned to this account. Please contact admin.');
                }
                $session->set([
                    'user_id'  => $user['id'],
                    'username' => $user['username'],
                    'role'     => $roleName,
                    'isLoggedIn'=> true
                ]);
                session()->regenerate();
                return redirect()->to('/dashboard');
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
        $db = \Config\Database::connect();
        $postedRoleId = $this->request->getPost('role_id');
        $postedRoleName = $this->request->getPost('role');
        $resolvedRoleId = null;
        if (!empty($postedRoleId)) {
            $resolvedRoleId = (int) $postedRoleId;
        } elseif (!empty($postedRoleName)) {
            $roleRow = $db->table('roles')->where('name', $postedRoleName)->get()->getRowArray();
            $resolvedRoleId = $roleRow['id'] ?? null;
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id'  => $resolvedRoleId,
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
