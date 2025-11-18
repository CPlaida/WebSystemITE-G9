<?php

namespace App\Controllers;
use App\Models\UserModel;

class Admin extends BaseController
{
    public function index()
    {
        // Use unified dashboard for admins
        return redirect()->to('/dashboard');
    }

    public function manageUsers()
    {
        $db = \Config\Database::connect();
        $users = $db->table('users u')
                    ->select('u.id, u.username, u.email, u.status, u.role_id, r.name as role_name')
                    ->join('roles r', 'r.id = u.role_id', 'left')
                    ->orderBy('u.id', 'DESC')
                    ->get()->getResultArray();
        $roles = $db->table('roles')->select('id, name')->orderBy('name','ASC')->get()->getResultArray();

        // Live statistics
        $totalUsers = $db->table('users')->countAllResults();
        $activeUsers = $db->table('users')->where('status', 'active')->countAllResults();
        $doctorCount = $db->table('users u')
                          ->join('roles r', 'r.id = u.role_id', 'left')
                          ->where('r.name', 'Doctor')
                          ->countAllResults();
        $nurseCount = $db->table('users u')
                         ->join('roles r', 'r.id = u.role_id', 'left')
                         ->where('r.name', 'Nurse')
                         ->countAllResults();
        $data = [
            'title' => 'User Management',
            'users' => $users,
            'roles' => $roles,
            'stats' => [
                'total' => $totalUsers,
                'doctors' => $doctorCount,
                'nurses' => $nurseCount,
                'active' => $activeUsers,
            ],
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

    public function createUser()
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $model = new UserModel();
        $username = trim((string) $this->request->getPost('username'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $status = strtolower((string) $this->request->getPost('status'));

        if ($username === '' || $email === '' || $password === '' || empty($roleId)) {
            return redirect()->back()->withInput()->with('error', 'Please fill in all required fields.');
        }

        // Uniqueness checks to avoid DB duplicate key errors
        if ($model->where('email', $email)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Email already exists.');
        }
        if ($model->where('username', $username)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'Username already exists.');
        }

        $data = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => in_array($status, ['active','inactive']) ? $status : 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $model->insert($data);
        } catch (\Throwable $e) {
            // Fallback for any DB errors (e.g., race condition hitting unique index)
            $message = (strpos(strtolower($e->getMessage()), 'duplicate') !== false)
                ? 'Email already exists.'
                : 'Failed to create user. Please try again.';
            return redirect()->back()->withInput()->with('error', $message);
        }

        return redirect()->to('/admin/Administration/ManageUser')->with('success', 'User created successfully.');
    }

    public function updateUser($id)
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }
        $model = new UserModel();
        $username = trim((string) $this->request->getPost('username'));
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $status = strtolower((string) $this->request->getPost('status'));

        $data = [
            'username' => $username,
            'email' => $email,
            'role_id' => $roleId,
            'status' => in_array($status, ['active','inactive']) ? $status : 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $model->update($id, $data);
        return redirect()->to('/admin/Administration/ManageUser')->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }
        $model = new UserModel();
        $model->delete($id);
        return redirect()->to('/admin/Administration/ManageUser')->with('success', 'User deleted successfully.');
    }

    public function resetPassword($id)
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $new = trim((string) $this->request->getPost('new_password'));
        $confirm = trim((string) $this->request->getPost('confirm_password'));

        if ($new === '' || $confirm === '') {
            return redirect()->to('/admin/Administration/ManageUser')->with('error', 'New password and confirm password are required.');
        }
        if ($new !== $confirm) {
            return redirect()->to('/admin/Administration/ManageUser')->with('error', 'Passwords do not match.');
        }
        if (strlen($new) < 8) {
            return redirect()->to('/admin/Administration/ManageUser')->with('error', 'Password must be at least 8 characters.');
        }

        $model = new UserModel();
        $model->update($id, [
            'password' => password_hash($new, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/Administration/ManageUser')->with('success', 'Password updated successfully.');
    }
}
