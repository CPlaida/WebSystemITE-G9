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
        if ($userRole === 'doctor') {
            return view('Roles/doctor/dashboard', $data);
        }
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

        return view('Roles/Accountant/dashboard', $data);
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

        // Doctor KPIs: 4 simple, connected metrics
        if ($userRole === 'doctor') {
            try {
                $db = \Config\Database::connect();
                $doctorId = (string) (session()->get('user_id') ?? '');
                if ($doctorId !== '') {
                    // 1) Today's appointments (exclude cancelled/no_show)
                    $data['appointmentsCount'] = (int) $db->table('appointments')
                        ->where('doctor_id', $doctorId)
                        ->where('appointment_date', $today)
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                        ->countAllResults();

                    // 2) Pending today (scheduled/confirmed/in_progress only)
                    $data['pendingAppointmentsToday'] = (int) $db->table('appointments')
                        ->where('doctor_id', $doctorId)
                        ->where('appointment_date', $today)
                        ->whereIn('status', ['scheduled','confirmed','in_progress'])
                        ->countAllResults();

                    // 3) Pending lab results (if lab_requests has doctor_id)
                    $data['pendingLabResults'] = 0;
                    if ($db->tableExists('lab_requests') && $db->fieldExists('doctor_id', 'lab_requests')) {
                        $data['pendingLabResults'] = (int) $db->table('lab_requests')
                            ->where('doctor_id', $doctorId)
                            ->whereIn('status', ['pending','in_progress'])
                            ->countAllResults();
                    }

                    // 4) Today's shifts (doctor's own schedule only)
                    $data['todaysShifts'] = 0;
                    if ($db->tableExists('doctor_schedules')) {
                        $data['todaysShifts'] = (int) $db->table('doctor_schedules')
                            ->where('doctor_id', $doctorId)
                            ->where('shift_date', $today)
                            ->countAllResults();
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'Doctor dashboard KPI error: ' . $e->getMessage());
            }
        }

        return $data;

        // Optional models (may not exist in all setups)
        $prescriptionModel = class_exists('App\\Models\\PrescriptionModel') ? new \App\Models\PrescriptionModel() : null;
        $labRequestModel = class_exists('App\\Models\\LabRequestModel') ? new \App\Models\LabRequestModel() : null;

        try {
            // Appointments & Patients
            if (in_array($userRole, ['admin', 'receptionist', 'nurse', 'doctor'])) {
                // Today's appointments (exclude cancelled/no_show)
                $data['appointmentsCount'] = $appointmentModel
                    ->where('appointment_date', $today)
                    ->whereNotIn('status', ['cancelled','no_show'])
                    ->countAllResults();

                $data['patientsCount'] = $patientModel->countAllResults();
                // New patients today
                if ($patientModel->db->fieldExists('created_at', 'patients')) {
                    $data['newPatientsToday'] = $patientModel->builder()
                        ->select('COUNT(*) AS c')
                        ->where('DATE(created_at)', $today)
                        ->get()->getRow('c') ?? 0;
                }
            }

            // Active cases: confirmed or in_progress today
            if (in_array($userRole, ['admin', 'doctor', 'nurse'])) {
                $data['activeCases'] = $appointmentModel
                    ->where('appointment_date', $today)
                    ->whereIn('status', ['confirmed','in_progress'])
                    ->countAllResults();
            }

            // Billing totals (normalized schema)
            if (in_array($userRole, ['admin', 'accounting'])) {
                // Today's paid revenue
                $data['todayRevenue'] = (float) ($billingModel->builder()
                    ->selectSum('final_amount', 'sum')
                    ->where('payment_status', 'paid')
                    ->where('bill_date', $today)
                    ->get()->getRow('sum') ?? 0);

                // Monthly and outstanding via helper
                if (method_exists($billingModel, 'getTotals')) {
                    $totals = $billingModel->getTotals();
                    $data['paidThisMonth'] = (float) ($totals['paidThisMonth'] ?? 0);
                    $data['outstanding'] = (float) ($totals['outstanding'] ?? 0);
                    $data['pendingBills'] = (int) ($totals['pendingCount'] ?? 0);
                } else {
                    // Fallback if helper not available
                    $data['paidThisMonth'] = (float) ($billingModel->builder()
                        ->selectSum('final_amount', 'sum')
                        ->where('payment_status', 'paid')
                        ->where('bill_date >=', $monthStart)
                        ->where('bill_date <=', $monthEnd)
                        ->get()->getRow('sum') ?? 0);

                    $data['outstanding'] = (float) ($billingModel->builder()
                        ->selectSum('final_amount', 'sum')
                        ->where('payment_status', 'pending')
                        ->get()->getRow('sum') ?? 0);

                    $data['pendingBills'] = (int) ($billingModel->builder()
                        ->select('COUNT(*) AS c')
                        ->where('payment_status', 'pending')
                        ->get()->getRow('c') ?? 0);
                }
            }

            // Prescriptions pending
            if ($prescriptionModel && in_array($userRole, ['admin', 'pharmacist'])) {
                $data['prescriptionsCount'] = (int) $prescriptionModel->builder()
                    ->select('COUNT(*) AS c')
                    ->where('status', 'pending')
                    ->get()->getRow('c');
            }

            // Laboratory pending stats
            if ($labRequestModel && in_array($userRole, ['admin', 'labstaff'])) {
                $pending = (int) $labRequestModel->builder()
                    ->select('COUNT(*) AS c')
                    ->where('status', 'pending')
                    ->get()->getRow('c');
                $data['labTestsCount'] = $pending; // backward compat
                $data['labStats'] = ['pending' => $pending];
            }

            // User counts by role (for Users Total)
            $db = \Config\Database::connect();
            if ($db->tableExists('users') && $db->tableExists('roles')) {
                $rows = $db->table('users u')
                    ->select('r.name AS role, COUNT(u.id) AS cnt')
                    ->join('roles r', 'r.id = u.role_id', 'left')
                    ->groupBy('r.name')
                    ->get()->getResultArray();
                $counts = [];
                foreach ($rows as $r) { $counts[$r['role'] ?: 'unknown'] = (int)$r['cnt']; }
                $data['userCounts'] = $counts;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error loading dashboard data: ' . $e->getMessage());
        }

        return $data;
    }
}
