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


    private function hasField(string $table, string $field): bool
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($table);
        return in_array($field, $fields, true);
    }

    private function ensureBillingItemsTable(): void
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('billing_items')) {
            return;
        }
        // Minimal safe DDL to support itemized receipts
        $db->query("CREATE TABLE IF NOT EXISTS billing_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            billing_id INT UNSIGNED NOT NULL,
            service VARCHAR(255) NOT NULL,
            qty INT UNSIGNED NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            INDEX idx_billing_items_billing_id (billing_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function index()
    {
        $term = $this->request->getGet('q');
        $bills = $this->billingModel->getList($term);
        $totals = $this->billingModel->getTotals();
        return view('Roles/admin/Billing & payment/billingmanagement', [
            'bills' => $bills,
            'totals' => $totals,
            'query' => (string)($term ?? ''),
        ]);
    }

    public function process()
    {
        $data = [
            'title' => 'Bill Process',
            'active_menu' => 'billing',
            'validation' => \Config\Services::validation()
        ];
        
        return view('Roles/admin/Billing & payment/bill_process', $data);
    }
    
    public function save()
    {
        // Backward-compat shim. Delegate to store().
        return $this->store();
    }

    public function create()
    {
        // Optional: return a small create form view if needed; most flows use a modal
        return $this->response->setJSON(['ok' => true]);
    }

    public function store()
    {
        $rules = [
            'patient_id' => 'required|integer',
            'final_amount' => 'required|numeric',
            'payment_status' => 'required|in_list[pending,partial,paid,overdue]',
            'bill_date' => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Derive totals from components if provided
        $consultation = (float) ($this->request->getPost('consultation_fee') ?? 0);
        $medication   = (float) ($this->request->getPost('medication_cost') ?? 0);
        $labtests     = (float) ($this->request->getPost('lab_tests_cost') ?? 0);
        $other        = (float) ($this->request->getPost('other_charges') ?? 0);
        $discount     = (float) ($this->request->getPost('discount') ?? 0);
        $tax          = (float) ($this->request->getPost('tax') ?? 0);
        $computedTotal = $consultation + $medication + $labtests + $other;
        $computedFinal = ($computedTotal - $discount) + $tax;

        $payload = [
            'patient_id' => (int) $this->request->getPost('patient_id'),
            'service_id' => $this->request->getPost('service_id') ? (int) $this->request->getPost('service_id') : null,
            'consultation_fee' => $consultation,
            'medication_cost' => $medication,
            'lab_tests_cost' => $labtests,
            'other_charges' => $other,
            'total_amount' => $computedTotal,
            'discount' => $discount,
            'tax' => $tax,
            'final_amount' => $computedFinal,
            'payment_status' => $this->request->getPost('payment_status'),
            'payment_method' => $this->request->getPost('payment_method'),
            'bill_date' => $this->request->getPost('bill_date'),
            'notes' => $this->request->getPost('notes'),
        ];

        $id = $this->billingModel->insert($payload, true);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'id' => $id]);
        }
        return redirect()->to(base_url('billing/show/' . $id))->with('message', 'Bill created successfully');
    }

    public function receipt($id = null)
    {
        // Legacy placeholder: delegate to show() for consistency
        return $this->show($id);
    }

    public function show($id = null)
    {
        if (!$id) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Bill not specified');
        }
        $bill = $this->billingModel->findWithRelations((int)$id);
        if (!$bill) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Bill not found');
        }

        // Load items if table exists
        $db = \Config\Database::connect();
        $this->ensureBillingItemsTable();
        $items = [];
        if ($db->tableExists('billing_items')) {
            $rawItems = $db->table('billing_items')->where('billing_id', (int)$id)->get()->getResultArray();
            foreach ($rawItems as $ri) {
                $items[] = [
                    'description' => $ri['service'] ?? '',
                    'quantity' => (int)($ri['qty'] ?? 0),
                    'unit_price' => (float)($ri['price'] ?? 0),
                    'amount' => (float)($ri['amount'] ?? 0),
                ];
            }
        }

        // Fallback: if no item rows saved, map component fields to items for display
        if (empty($items)) {
            $componentMap = [
                'Consultation Fee' => (float)($bill['consultation_fee'] ?? 0),
                'Medication Cost'  => (float)($bill['medication_cost'] ?? 0),
                'Lab Tests Cost'   => (float)($bill['lab_tests_cost'] ?? 0),
                'Other Charges'    => (float)($bill['other_charges'] ?? 0),
            ];
            foreach ($componentMap as $label => $val) {
                if ($val > 0) {
                    $items[] = [
                        'description' => $label,
                        'quantity' => 1,
                        'unit_price' => $val,
                        'amount' => $val,
                    ];
                }
            }
        }

        // Compute/Map fields for receipt view
        $subtotal = 0.0;
        foreach ($items as $it) { $subtotal += (float)$it['amount']; }
        if ($subtotal <= 0 && isset($bill['total_amount'])) {
            $subtotal = (float)$bill['total_amount'];
        }
        $tax = isset($bill['tax']) ? (float)$bill['tax'] : round($subtotal * 0.12, 2);
        $total = isset($bill['final_amount']) ? (float)$bill['final_amount'] : ($subtotal + $tax);

        // Provide aliases used by receipt view (derived from numeric id)
        $bill['bill_number'] = 'INV-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
        $bill['date_issued'] = $bill['bill_date'] ?? date('Y-m-d');
        $bill['status'] = $bill['payment_status'] ?? 'pending';
        $bill['items'] = $items;
        $bill['subtotal'] = $subtotal;
        $bill['tax'] = $tax;
        $bill['total'] = $total;

        return view('Roles/admin/Billing & payment/receipt', ['bill' => $bill]);
    }

    public function get($id)
    {
        $bill = $this->billingModel->findWithRelations((int)$id);
        return $this->response->setJSON($bill);
    }

    public function edit($id)
    {
        // Return JSON for modal editing
        $bill = $this->billingModel->findWithRelations((int)$id);
        if (!$bill) return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        return $this->response->setJSON($bill);
    }

    public function update($id)
    {
        $rules = [
            'patient_id' => 'permit_empty|integer',
            'service_id' => 'permit_empty|integer',
            'final_amount' => 'permit_empty|numeric',
            'payment_status' => 'permit_empty|in_list[pending,partial,paid,overdue]',
            'bill_date' => 'permit_empty|valid_date',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        // Recompute totals if any component fields are present; else allow direct final_amount update
        $hasComponents = $this->request->getPost('consultation_fee') !== null
            || $this->request->getPost('medication_cost') !== null
            || $this->request->getPost('lab_tests_cost') !== null
            || $this->request->getPost('other_charges') !== null
            || $this->request->getPost('discount') !== null
            || $this->request->getPost('tax') !== null;

        $data = [
            'id' => (int)$id,
            'patient_id' => $this->request->getPost('patient_id') !== null ? (int)$this->request->getPost('patient_id') : null,
            'service_id' => $this->request->getPost('service_id') !== null ? (int)$this->request->getPost('service_id') : null,
            'payment_status' => $this->request->getPost('payment_status') ?? null,
            'payment_method' => $this->request->getPost('payment_method') ?? null,
            'bill_date' => $this->request->getPost('bill_date') ?? null,
            'notes' => $this->request->getPost('notes') ?? null,
        ];

        if ($hasComponents) {
            $consultation = (float) ($this->request->getPost('consultation_fee') ?? 0);
            $medication   = (float) ($this->request->getPost('medication_cost') ?? 0);
            $labtests     = (float) ($this->request->getPost('lab_tests_cost') ?? 0);
            $other        = (float) ($this->request->getPost('other_charges') ?? 0);
            $discount     = (float) ($this->request->getPost('discount') ?? 0);
            $tax          = (float) ($this->request->getPost('tax') ?? 0);
            $computedTotal = $consultation + $medication + $labtests + $other;
            $computedFinal = ($computedTotal - $discount) + $tax;
            $data['consultation_fee'] = $consultation;
            $data['medication_cost'] = $medication;
            $data['lab_tests_cost'] = $labtests;
            $data['other_charges'] = $other;
            $data['total_amount'] = $computedTotal;
            $data['discount'] = $discount;
            $data['tax'] = $tax;
            $data['final_amount'] = $computedFinal;
        } else if ($this->request->getPost('final_amount') !== null) {
            $data['final_amount'] = (float)$this->request->getPost('final_amount');
        }

        // Remove nulls to avoid overwriting
        $data = array_filter($data, fn($v) => $v !== null);

        $this->billingModel->save($data);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Bill updated successfully']);
    }

    public function delete($id)
    {
        $this->billingModel->delete($id);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Bill deleted successfully']);
    }

    public function storeWithItems()
    {
        $rules = [
            'patient_id' => 'required|integer',
            'bill_date' => 'required|valid_date',
            'payment_method' => 'permit_empty|string'
        ];
        if (!$this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $services = (array) $this->request->getPost('service');
        $qtys = (array) $this->request->getPost('qty');
        $prices = (array) $this->request->getPost('price');
        $amounts = (array) $this->request->getPost('amount');

        $items = [];
        $subtotal = 0.0;
        $count = max(count($services), count($qtys), count($prices));
        for ($i = 0; $i < $count; $i++) {
            $svc = trim((string)($services[$i] ?? ''));
            $q = (float)($qtys[$i] ?? 0);
            $p = (float)($prices[$i] ?? 0);
            $amt = $q * $p;
            if (isset($amounts[$i]) && is_numeric($amounts[$i])) {
                $amt = (float)$amounts[$i];
            }
            if ($svc === '' || $q <= 0) {
                continue;
            }
            $items[] = [
                'service' => $svc,
                'qty' => $q,
                'price' => $p,
                'amount' => $amt,
            ];
            $subtotal += $amt;
        }

        $tax = round($subtotal * 0.12, 2);
        $total = round($subtotal + $tax, 2);

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $payload = [
                'patient_id' => (int)$this->request->getPost('patient_id'),
                'total_amount' => $subtotal,
                'tax' => $tax,
                'final_amount' => $total,
                'payment_status' => $this->request->getPost('payment_status') ?: 'pending',
                'payment_method' => $this->request->getPost('payment_method'),
                'bill_date' => $this->request->getPost('bill_date'),
                'notes' => $this->request->getPost('notes'),
            ];
            $billId = $this->billingModel->insert($payload, true);

            if ($billId && !empty($items)) {
                $this->ensureBillingItemsTable();
                if ($db->tableExists('billing_items')) {
                    $now = date('Y-m-d H:i:s');
                    $rows = [];
                    foreach ($items as $it) {
                        $rows[] = [
                            'billing_id' => (int)$billId,
                            'service' => $it['service'],
                            'qty' => $it['qty'],
                            'price' => $it['price'],
                            'amount' => $it['amount'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    if (!empty($rows)) {
                        $db->table('billing_items')->insertBatch($rows);
                        // Some drivers may report 0 for batch inserts; ensure rows exist, else fall back to row-by-row insert
                        $inserted = $db->table('billing_items')->where('billing_id', (int)$billId)->countAllResults();
                        if ($inserted === 0) {
                            foreach ($rows as $r) {
                                $db->table('billing_items')->insert($r);
                            }
                        }
                    }
                }
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to save bill']);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to save bill');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'id' => $billId]);
        }
        return redirect()->to(base_url('billing/show/' . $billId))->with('message', 'Bill created successfully');
    }
}
