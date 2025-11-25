<?php

namespace App\Controllers;
use App\Models\UserModel;
use App\Models\StaffProfileModel;
use App\Models\StaffDepartmentModel;
use App\Models\StaffSpecializationModel;

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

        $staffModel = new StaffProfileModel();
        $staffOptions = $staffModel->select(
            'staff_profiles.id, staff_profiles.first_name, staff_profiles.middle_name, staff_profiles.last_name, '
            . 'staff_profiles.email as staff_email, staff_profiles.role_id, '
            . 'staff_profiles.department_id, staff_profiles.specialization_id, '
            . 'roles.name as role_name, sd.name as department_name, ss.name as specialization_name'
        )
            ->join('roles', 'roles.id = staff_profiles.role_id', 'left')
            ->join('staff_departments sd', 'sd.id = staff_profiles.department_id', 'left')
            ->join('staff_specializations ss', 'ss.id = staff_profiles.specialization_id', 'left')
            ->where('staff_profiles.user_id', null)
            ->orderBy('staff_profiles.last_name', 'ASC')
            ->orderBy('staff_profiles.first_name', 'ASC')
            ->findAll();

        $staffOptions = array_map(function ($staff) {
            $fullName = trim(($staff['last_name'] ?? '') . ', ' . ($staff['first_name'] ?? '') . ' ' . ($staff['middle_name'] ?? ''));
            $staff['full_name'] = preg_replace('/\s+/', ' ', $fullName);
            $staff['username_suggestion'] = $this->generateUsernameSuggestion($staff);
            $labelParts = [$staff['full_name']];
            if (!empty($staff['role_name'])) {
                $labelParts[] = '(' . $staff['role_name'] . ')';
            }
            if (!empty($staff['department_name'])) {
                $labelParts[] = $staff['department_name'];
            }
            $staff['display_label'] = trim(implode(' ', $labelParts));
            return $staff;
        }, $staffOptions);

        $prefillId = (int) ($this->request->getGet('staff_id') ?? 0);
        $prefillStaff = null;
        if ($prefillId > 0) {
            foreach ($staffOptions as $option) {
                if ((int)$option['id'] === $prefillId) {
                    $prefillStaff = $option;
                    break;
                }
            }
        }

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
            'staffOptions' => $staffOptions,
            'prefillStaff' => $prefillStaff,
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
        $staffModel = new StaffProfileModel();

        $username = trim((string) $this->request->getPost('username'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');
        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $status = strtolower((string) $this->request->getPost('status'));
        $staffId = (int) ($this->request->getPost('staff_id') ?? 0);

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

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $model->insert($data);
            $newUserId = $model->getInsertID();

            if ($staffId > 0) {
                $staff = $staffModel->find($staffId);
                if (!$staff) {
                    throw new \RuntimeException('Selected staff profile not found.');
                }
                if (!empty($staff['user_id'])) {
                    throw new \RuntimeException('This staff profile is already linked to a user.');
                }
                $staffModel->update($staffId, ['user_id' => $newUserId]);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            // Fallback for any DB errors (e.g., race condition hitting unique index)
            $message = (strpos(strtolower($e->getMessage()), 'duplicate') !== false)
                ? 'Email already exists.'
                : ($e->getMessage() ?: 'Failed to create user. Please try again.');
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
        $staffModel = new StaffProfileModel();
        $username = trim((string) $this->request->getPost('username'));
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $roleId = (int) ($this->request->getPost('role_id') ?? 0);
        $status = strtolower((string) $this->request->getPost('status'));
        $staffId = (int) ($this->request->getPost('staff_id') ?? 0);

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
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $model->update($id, $data);

            if ($staffId > 0) {
                $staff = $staffModel->find($staffId);
                if (!$staff) {
                    throw new \RuntimeException('Selected staff profile not found.');
                }
                if (!empty($staff['user_id']) && (int)$staff['user_id'] !== (int)$id) {
                    throw new \RuntimeException('This staff profile is already linked to another user.');
                }
                $staffModel->update($staffId, ['user_id' => $id]);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage() ?: 'Failed to update user.');
        }

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

    public function manageStaff()
    {
        $staffModel = new StaffProfileModel();
        $builder = $staffModel->select(
            'staff_profiles.*, '
            . 'u.username as user_username, u.email as user_email, u.status as user_status, '
            . 'ur.name as user_role_name, sr.name as staff_role_name, '
            . 'sd.name as department_name, ss.name as specialization_name'
        )
            ->join('users u', 'u.id = staff_profiles.user_id', 'left')
            ->join('roles ur', 'ur.id = u.role_id', 'left')
            ->join('roles sr', 'sr.id = staff_profiles.role_id', 'left')
            ->join('staff_departments sd', 'sd.id = staff_profiles.department_id', 'left')
            ->join('staff_specializations ss', 'ss.id = staff_profiles.specialization_id', 'left')
            ->orderBy('staff_profiles.last_name', 'ASC')
            ->orderBy('staff_profiles.first_name', 'ASC');

        $staffList = $builder->findAll();
        $db = \Config\Database::connect();
        $users = $db->table('users u')
            ->select('u.id, u.username, u.email, u.status, r.name as role_name, r.id as role_id')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->orderBy('u.username', 'ASC')
            ->get()->getResultArray();

        $assignedUserIds = array_filter(array_map(static function ($staff) {
            return $staff['user_id'] ?? null;
        }, $staffList));

        $userOptions = array_map(static function ($user) use ($assignedUserIds) {
            $user['is_assigned'] = in_array($user['id'], $assignedUserIds, true);
            return $user;
        }, $users);

        $staffModelSimple = new StaffProfileModel();
        $totalStaff = $staffModelSimple->countAllResults();
        $activeStaff = $staffModelSimple->where('status', 'active')->countAllResults();
        $onLeaveStaff = $staffModelSimple->where('status', 'on_leave')->countAllResults();

        $deptModel = new StaffDepartmentModel();
        $departments = $deptModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        $specModel = new StaffSpecializationModel();
        $specializations = $specModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        $roles = $db->table('roles')->select('id, name')->orderBy('name', 'ASC')->get()->getResultArray();
        $roles = array_map(function ($role) {
            $role['scope'] = $this->mapRoleToScope($role['name'] ?? '');
            return $role;
        }, $roles);

        $data = [
            'title' => 'Staff Management',
            'staff' => $staffList,
            'users' => $userOptions,
            'departments' => $departments,
            'specializations' => $specializations,
            'roles' => $roles,
            'statuses' => ['active' => 'Active', 'inactive' => 'Inactive', 'on_leave' => 'On Leave'],
            'genders' => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'],
            'stats' => [
                'total' => $totalStaff,
                'active' => $activeStaff,
                'on_leave' => $onLeaveStaff,
                'with_accounts' => count($assignedUserIds),
            ],
        ];

        return view('Roles/admin/Administration/StaffManagement', $data);
    }

    public function createStaff()
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $staffModel = new StaffProfileModel();
        $payload = $this->buildStaffPayload();

        $roleSyncResult = $this->syncRoleFromUser($payload);
        if ($roleSyncResult !== true) {
            return $roleSyncResult;
        }

        $specValidation = $this->ensureValidSpecialization($payload);
        if ($specValidation !== true) {
            return $specValidation;
        }

        try {
            $staffModel->insert($payload, true);
        } catch (\Throwable $e) {
            $message = (strpos(strtolower($e->getMessage()), 'duplicate') !== false)
                ? 'User already assigned.'
                : 'Failed to create staff profile.';
            return redirect()->back()->withInput()->with('error', $message);
        }

        return redirect()->to('/admin/Administration/StaffManagement')->with('success', 'Staff profile created successfully.');
    }

    public function updateStaff($id)
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $staffModel = new StaffProfileModel();
        $staff = $staffModel->find($id);
        if (!$staff) {
            return redirect()->back()->with('error', 'Staff profile not found.');
        }

        $payload = $this->buildStaffPayload();

        $roleSyncResult = $this->syncRoleFromUser($payload);
        if ($roleSyncResult !== true) {
            return $roleSyncResult;
        }

        $specValidation = $this->ensureValidSpecialization($payload);
        if ($specValidation !== true) {
            return $specValidation;
        }

        $staffModel->update($id, $payload);

        return redirect()->to('/admin/Administration/StaffManagement')->with('success', 'Staff profile updated successfully.');
    }

    public function deleteStaff($id)
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $staffModel = new StaffProfileModel();
        $staffModel->delete($id);

        return redirect()->to('/admin/Administration/StaffManagement')->with('success', 'Staff profile deleted successfully.');
    }

    private function buildStaffPayload(): array
    {
        $status = strtolower((string)$this->request->getPost('status'));

        $roleId = (int)($this->request->getPost('role_id') ?? 0);
        $departmentId = (int)($this->request->getPost('department_id') ?? 0);
        $specializationId = (int)($this->request->getPost('specialization_id') ?? 0);

        return [
            'first_name' => trim((string)$this->request->getPost('first_name')),
            'middle_name' => trim((string)($this->request->getPost('middle_name') ?? '')) ?: null,
            'last_name' => trim((string)$this->request->getPost('last_name')),
            'gender' => $this->sanitizeGender($this->request->getPost('gender')),
            'date_of_birth' => $this->nullableDate($this->request->getPost('date_of_birth')),
            'phone' => trim((string)$this->request->getPost('phone')) ?: null,
            'email' => trim((string)$this->request->getPost('staff_email')) ?: null,
            'department_id' => $departmentId > 0 ? $departmentId : null,
            'specialization_id' => $specializationId > 0 ? $specializationId : null,
            'role_id' => $roleId > 0 ? $roleId : null,
            'license_number' => trim((string)$this->request->getPost('license_number')) ?: null,
            'address' => trim((string)$this->request->getPost('address')) ?: null,
            'hire_date' => $this->nullableDate($this->request->getPost('hire_date')),
            'status' => in_array($status, ['active', 'inactive', 'on_leave'], true) ? $status : 'active',
            'emergency_contact_name' => trim((string)$this->request->getPost('emergency_contact_name')) ?: null,
            'emergency_contact_phone' => trim((string)$this->request->getPost('emergency_contact_phone')) ?: null,
        ];
    }

    private function sanitizeGender($value): ?string
    {
        $val = strtolower((string)$value);
        return in_array($val, ['male', 'female', 'other'], true) ? $val : null;
    }

    private function nullableDate($value): ?string
    {
        $date = trim((string)$value);
        return $date !== '' ? $date : null;
    }

    private function isUserLinkedToOtherStaff(string $userId, ?int $ignoreId = null): bool
    {
        $model = new StaffProfileModel();
        $model->where('user_id', $userId);
        if ($ignoreId !== null) {
            $model->where('id !=', $ignoreId);
        }
        return (bool)$model->first();
    }

    private function mapRoleToScope(string $roleName): string
    {
        $roleName = strtolower(trim($roleName));
        if (strpos($roleName, 'doctor') !== false) {
            return 'doctor';
        }
        if (strpos($roleName, 'nurse') !== false) {
            return 'nurse';
        }
        return 'all';
    }

    private function syncRoleFromUser(array &$payload)
    {
        if (empty($payload['user_id'])) {
            return true;
        }

        $userModel = new UserModel();
        $user = $userModel->select('role_id')->find($payload['user_id']);
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Linked user not found.');
        }

        $userRoleId = (int)($user['role_id'] ?? 0);
        if ($userRoleId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Linked user has no role assigned.');
        }

        if (!empty($payload['role_id']) && (int)$payload['role_id'] !== $userRoleId) {
            return redirect()->back()->withInput()->with('error', 'Selected role must match the linked user role.');
        }

        $payload['role_id'] = $userRoleId;
        return true;
    }

    private function ensureValidSpecialization(array &$payload)
    {
        if (empty($payload['specialization_id'])) {
            return true;
        }

        $specModel = new StaffSpecializationModel();
        $specialization = $specModel->select('department_id')->find($payload['specialization_id']);
        if (!$specialization) {
            return redirect()->back()->withInput()->with('error', 'Selected specialization not found.');
        }

        $specDeptId = (int)($specialization['department_id'] ?? 0);
        if ($specDeptId > 0) {
            if (empty($payload['department_id'])) {
                $payload['department_id'] = $specDeptId;
            } elseif ((int)$payload['department_id'] !== $specDeptId) {
                return redirect()->back()->withInput()->with('error', 'Specialization does not belong to the chosen department.');
            }
        }

        return true;
    }

    private function generateUsernameSuggestion(array $staff): string
    {
        $first = strtolower(preg_replace('/[^a-z]/i', '', $staff['first_name'] ?? ''));
        $last = strtolower(preg_replace('/[^a-z]/i', '', $staff['last_name'] ?? ''));
        $username = $first;
        if ($last !== '') {
            $username .= '.' . $last;
        }
        return $username ?: ('staff' . ($staff['id'] ?? '')); 
    }
}
