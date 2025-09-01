<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\BillingModel;

class Billing extends BaseController
{
    protected $billingModel;

    public function __construct()
    {
        $this->billingModel = new BillingModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        return view('admin/Billing & payment/billingmanagement');
    }

    public function receipt($id = null)
    {
        // In a real application, you would fetch the bill from the database
        // This is a sample bill for demonstration
        $bill = [
            'bill_number' => 'INV-' . strtoupper(uniqid()),
            'patient_name' => 'Juan Dela Cruz',
            'patient_address' => '123 Sample St., Sample City',
            'patient_phone' => '0912-345-6789',
            'date_issued' => date('Y-m-d H:i:s'),
            'status' => 'Paid',
            'items' => [
                [
                    'description' => 'Medical Consultation',
                    'quantity' => 1,
                    'unit_price' => 1000.00,
                    'amount' => 1000.00
                ],
                [
                    'description' => 'Laboratory Test',
                    'quantity' => 1,
                    'unit_price' => 1500.00,
                    'amount' => 1500.00
                ],
                [
                    'description' => 'Medication',
                    'quantity' => 2,
                    'unit_price' => 250.75,
                    'amount' => 501.50
                ]
            ],
            'subtotal' => 3001.50,
            'tax' => 360.18,
            'total' => 3361.68
        ];

        // In a real application, you would fetch the bill from the database like this:
        // $bill = $this->billingModel->find($id);
        // if (!$bill) {
        //     return redirect()->back()->with('error', 'Bill not found');
        // }

        return view('admin/Billing & payment/receipt', ['bill' => $bill]);
    }

    public function save()
    {
        $data = [
            'bill_number' => $this->request->getPost('bill_number'),
            'patient_name' => $this->request->getPost('patient_name'),
            'service' => $this->request->getPost('service'),
            'amount' => $this->request->getPost('amount'),
            'status' => $this->request->getPost('status'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->billingModel->save($data);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Bill added successfully']);
    }

    public function get($id)
    {
        $bill = $this->billingModel->find($id);
        return $this->response->setJSON($bill);
    }

    public function update($id)
    {
        $data = [
            'id' => $id,
            'patient_name' => $this->request->getPost('patient_name'),
            'service' => $this->request->getPost('service'),
            'amount' => $this->request->getPost('amount'),
            'status' => $this->request->getPost('status'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->billingModel->save($data);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Bill updated successfully']);
    }

    public function delete($id)
    {
        $this->billingModel->delete($id);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Bill deleted successfully']);
    }
}
