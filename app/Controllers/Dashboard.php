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

    private function buildDashboardData(string $userRole, string $username): array
    {
        $data = [
            'userRole' => $userRole,
            'username' => $username,
            'appointmentsCount' => 0,
            'patientsCount' => 0,
            'activeCases' => 0,
            'todayRevenue' => 0,
            'pendingBills' => 0,
            'prescriptionsCount' => 0,
            'labTestsCount' => 0,
            'systemStatus' => 'Online',
            'roles' => [
                'admin' => 'Administrator',
                'doctor' => 'Doctor',
                'nurse' => 'Nurse',
                'accounting' => 'Accounting',
                'itstaff' => 'IT Staff',
                'labstaff' => 'Laboratory Staff',
                'pharmacist' => 'Pharmacist',
                'receptionist' => 'Receptionist'
            ]
        ];

        $appointmentModel = new \App\Models\AppointmentModel();
        $patientModel = new \App\Models\PatientModel();
        $billingModel = new \App\Models\BillingModel();
        $prescriptionModel = new \App\Models\PrescriptionModel();
        $labTestModel = new \App\Models\LabRequestModel();

        try {
            if (in_array($userRole, ['admin', 'receptionist', 'nurse', 'doctor'])) {
                $data['appointmentsCount'] = $appointmentModel->where('date', date('Y-m-d'))->countAllResults();
                $data['patientsCount'] = $patientModel->countAllResults();
            }

            if (in_array($userRole, ['admin', 'doctor', 'nurse'])) {
                $data['activeCases'] = $appointmentModel->where('status', 'active')->countAllResults();
            }

            if (in_array($userRole, ['admin', 'accounting'])) {
                $data['todayRevenue'] = $billingModel->selectSum('amount')
                    ->where('payment_date', date('Y-m-d'))
                    ->where('status', 'paid')
                    ->get()
                    ->getRow()
                    ->amount ?? 0;

                $data['pendingBills'] = $billingModel->where('status', 'pending')->countAllResults();
            }

            if (in_array($userRole, ['admin', 'pharmacist'])) {
                $data['prescriptionsCount'] = $prescriptionModel->where('status', 'pending')->countAllResults();
            }

            if (in_array($userRole, ['admin', 'labstaff'])) {
                $data['labTestsCount'] = $labTestModel->where('status', 'pending')->countAllResults();
            }
        } catch (\Exception $e) {
            log_message('error', 'Error loading dashboard data: ' . $e->getMessage());
        }

        return $data;
    }
}
