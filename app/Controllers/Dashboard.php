<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Use role from session only; DB now enforces role_id on login
        $userRole = session()->get('role') ?: 'guest';
        $username = session()->get('username') ?? 'User';

        $data = $this->buildDashboardData($userRole, $username);
        return view('auth/dashboard', $data);
    }
    
    /**
     * Accountant Dashboard
     */
    public function accountant()
    {
        // Check if user is logged in and has accounting role
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'accounting') {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Accountant Dashboard',
            'name' => session()->get('name') ?? 'Accountant',
            'todayRevenue' => 0, // You can add actual data here
            'pendingBills' => [], // You can add actual data here
            'insuranceClaims' => [], // Initialize empty array for insurance claims
            'outstandingBalance' => 0, // You can add actual data here
            'recentTransactions' => [] // You can add actual data here
        ];

        return view('auth/dashboard', $data);
    }

    private function buildDashboardData(string $userRole, string $username): array
    {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // Default data structure
        $data = [
            'userRole' => $userRole,
            'username' => $username,
            'appointmentsCount' => 0,
            'patientsCount' => 0,
            'newPatientsToday' => 0,
            'activeCases' => 0,
            'todayRevenue' => 0.0,
            'paidThisMonth' => 0.0,
            'outstanding' => 0.0,
            'pendingBills' => 0,
            'insuranceClaims' => 0,
            'outstandingBalance' => 0.0,
            'prescriptionsCount' => 0,
            'labTestsCount' => 0,
            'labStats' => [],
            'userCounts' => [],
            'systemStatus' => 'Online',
            'roles' => [
                'admin' => 'Administrator',
                'doctor' => 'Doctor',
                'nurse' => 'Nurse',
                'accounting' => 'Accounting',
                'lab_staff' => 'Laboratory Staff',
                'itstaff' => 'IT Staff',
                'pharmacist' => 'Pharmacist',
                'receptionist' => 'Receptionist'
            ],
            // Lab staff specific data
            'pendingTests' => [],
            'completedToday' => 0,
            'monthlyTests' => 0
        ];

        // If user is lab staff, load lab-specific data
        if ($userRole === 'lab_staff') {
            $data['pendingTests'] = [];
            $data['completedToday'] = 0;
            $data['monthlyTests'] = 0;
        }

        return $data;
    }
}
