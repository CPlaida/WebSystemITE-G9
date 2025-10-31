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
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $status = strtolower((string) $this->request->getPost('status'));

        if ($username === '' || $email === '' || $password === '' || empty($roleId)) {
            return redirect()->back()->with('error', 'Please fill in all required fields.');
        }

        $data = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => in_array($status, ['active','inactive','suspended']) ? $status : 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $model->insert($data);
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
            'status' => in_array($status, ['active','inactive','suspended']) ? $status : 'active',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        $model->update((int)$id, $data);
        return redirect()->to('/admin/Administration/ManageUser')->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }
        $model = new UserModel();
        $model->delete((int)$id);
        return redirect()->to('/admin/Administration/ManageUser')->with('success', 'User deleted successfully.');
    }
}
