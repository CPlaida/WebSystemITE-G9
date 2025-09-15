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

    public function process()
    {
        $data = [
            'title' => 'Bill Process',
            'active_menu' => 'billing',
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/Billing & payment/bill_process', $data);
    }
    
    public function save()
    {
        // Validate the form
        $rules = [
            'patient_id' => 'required',
            'patient_name' => 'required',
            'date' => 'required|valid_date',
            'items' => 'required',
            'payment_method' => 'required'
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Process the form data
        $data = [
            'patient_id' => $this->request->getPost('patient_id'),
            'patient_name' => $this->request->getPost('patient_name'),
            'date' => $this->request->getPost('date'),
            'items' => $this->request->getPost('items'),
            'subtotal' => $this->request->getPost('subtotal'),
            'tax' => $this->request->getPost('tax'),
            'total' => $this->request->getPost('total'),
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_details' => [
                'insurance_provider' => $this->request->getPost('insurance_provider'),
                'card_number' => $this->request->getPost('card_number'),
                'expiry_date' => $this->request->getPost('expiry_date')
            ],
            'notes' => $this->request->getPost('notes'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => session()->get('user_id') // Assuming you store user_id in session
        ];
        
        // Save to database (uncomment when model is ready)
        // $billId = $this->billingModel->insertBill($data);
        
        // For now, just redirect to receipt with a temporary ID
        return redirect()->to('/billing/receipt/temp_' . time())->with('message', 'Bill created successfully');
    }

    public function receipt($id = null)
    {
        // In a real application, you would fetch the bill from the database
        // This is a sample bill for demonstration
        $bill = [
            'bill_number' => $id ?? 'INV-' . strtoupper(uniqid()),
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

        // Make sure the view path is correct
        $viewPath = 'admin/Billing & payment/receipt';
        
        // Check if the view file exists
        if (!is_file(APPPATH . 'Views/' . $viewPath . '.php')) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Receipt view not found');
        }

        return view($viewPath, ['bill' => $bill]);
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
