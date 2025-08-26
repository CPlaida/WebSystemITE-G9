<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\BillModel;
use App\Models\PaymentModel;
use App\Models\InsuranceModel;

class Accountant extends Controller
{
    public function index()
    {
        // Check if user is logged in and has accounting role
        if (!session()->get('logged_in') || session()->get('role') !== 'accounting') {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }
        // Load dashboard data
        $billModel = new BillModel();
        $paymentModel = new PaymentModel();
        $insuranceModel = new InsuranceModel();

        $data = [
            'todayRevenue' => $paymentModel->getTodayRevenue(),
            'pendingBills' => $billModel->getPendingBills(),
            'insuranceClaims' => $insuranceModel->getPendingClaims(),
            'outstandingBalance' => $billModel->getOutstandingBalance()
        ];

        return view('Accountant/accountant', $data);
    }

    public function dashboard()
    {
        return $this->index();
    }
}