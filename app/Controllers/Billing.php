<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\BillingModel;
use App\Models\LaboratoryModel;
use App\Models\ServiceModel;

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
            // Ensure lab_id column exists for cross-linking to laboratory rows
            try {
                $fields = array_map('strtolower', $db->getFieldNames('billing_items'));
                if (!in_array('lab_id', $fields, true)) {
                    $db->query("ALTER TABLE billing_items ADD COLUMN lab_id VARCHAR(20) NULL AFTER billing_id");
                    $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_lab_id ON billing_items(lab_id)");
                }
            } catch (\Throwable $e) { /* ignore */ }
            return;
        }
        // Minimal safe DDL to support itemized receipts
        $db->query("CREATE TABLE IF NOT EXISTS billing_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            billing_id INT UNSIGNED NOT NULL,
            lab_id VARCHAR(20) NULL,
            service VARCHAR(255) NOT NULL,
            qty INT UNSIGNED NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            INDEX idx_billing_items_billing_id (billing_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        // Add lab_id index
        try { $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_lab_id ON billing_items(lab_id)"); } catch (\Throwable $e) { /* ignore */ }
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
            'patient_id' => 'required',
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
            'patient_id' => (string) $this->request->getPost('patient_id'),
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
            'patient_id' => 'permit_empty',
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
            'patient_id' => $this->request->getPost('patient_id') !== null ? (string)$this->request->getPost('patient_id') : null,
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
            'patient_id' => 'required',
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
        $labIds = (array) $this->request->getPost('lab_id');

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
                'lab_id' => isset($labIds[$i]) ? trim((string)$labIds[$i]) : null,
            ];
            $subtotal += $amt;
        }

        $tax = round($subtotal * 0.12, 2);
        $total = round($subtotal + $tax, 2);

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $payload = [
                'patient_id' => (string)$this->request->getPost('patient_id'),
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
                            'lab_id' => isset($it['lab_id']) ? (string)$it['lab_id'] : null,
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

                // Mark any attached laboratory rows as billed
                try {
                    $labIdsToMark = array_values(array_filter(array_map(function($it){
                        return isset($it['lab_id']) && $it['lab_id'] !== '' ? $it['lab_id'] : null;
                    }, $items)));
                    if (!empty($labIdsToMark) && $db->tableExists('laboratory')) {
                        $db->table('laboratory')->whereIn('id', $labIdsToMark)->set('billed', 1)->update();
                    }
                } catch (\Throwable $e) { /* ignore */ }
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

    /**
     * Return a list of billable services for a patient, currently sourced from completed lab tests.
     * Response format: [{ service, qty, price, amount }]
     */
    public function patientServices()
    {
        $patientId = trim((string)$this->request->getGet('patient_id'));
        if ($patientId === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'patient_id is required']);
        }

        $db = \Config\Database::connect();
        $labModel = new LaboratoryModel();
        $svcModel = new ServiceModel();

        // Determine patient full name for legacy lab rows without patient_id
        $patientName = '';
        if ($db->tableExists('patients')) {
            try {
                $pfields = array_map('strtolower', $db->getFieldNames('patients'));
                $colFirst = in_array('first_name', $pfields) ? 'first_name' : (in_array('firstname', $pfields) ? 'firstname' : null);
                $colLast  = in_array('last_name', $pfields) ? 'last_name'  : (in_array('lastname', $pfields)  ? 'lastname'  : null);
                $colName  = in_array('name', $pfields) ? 'name' : (in_array('full_name', $pfields) ? 'full_name' : null);
                $sel = null;
                if ($colName) {
                    $sel = "$colName AS name";
                } else {
                    $parts = [];
                    if ($colFirst) { $parts[] = "$colFirst AS first_name"; }
                    if ($colLast)  { $parts[] = "$colLast AS last_name"; }
                    if (!empty($parts)) { $sel = implode(', ', $parts); }
                }
            } catch (\Throwable $e) { $sel = null; }
            if ($sel) {
                $prow = $db->table('patients')->select($sel, false)->where('id', $patientId)->get()->getRowArray();
                if ($prow) {
                    if (!empty($prow['name'])) {
                        $patientName = trim($prow['name']);
                    } else {
                        $patientName = trim(((string)($prow['first_name'] ?? '')) . ' ' . ((string)($prow['last_name'] ?? '')));
                    }
                }
            }
        }

        // Build a query that matches either by patient_id or by legacy test_name ~ patient name (tokens)
        $builder = $db->table('laboratory');
        $builder->select('*');
        $builder->groupStart()
                ->where('status', 'completed')
                ->orWhere('status', 'in_progress')
            ->groupEnd();
        $builder->groupStart();
        $builder->where('patient_id', $patientId);
        if ($patientName !== '') {
            $builder->orLike('test_name', $patientName, 'both');
            $tokens = preg_split('/\s+/', $patientName);
            if (is_array($tokens)) {
                foreach ($tokens as $tk) {
                    $tk = trim($tk);
                    if ($tk !== '' && strlen($tk) >= 2) {
                        $builder->orLike('test_name', $tk, 'both');
                    }
                }
            }
        }
        $builder->groupEnd();
        // Exclude already billed (only if the column exists)
        if ($db->tableExists('laboratory')) {
            try {
                $labFields = array_map('strtolower', $db->getFieldNames('laboratory'));
                if (in_array('billed', $labFields, true)) {
                    $builder->where('(billed = 0 OR billed IS NULL)');
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
        $builder->orderBy('test_date', 'DESC');
        $labs = $builder->get()->getResultArray();
        // Exclude labs that already appear in billing_items.lab_id when possible
        try {
            $biFields = array_map('strtolower', $db->getFieldNames('billing_items'));
            if (in_array('lab_id', $biFields, true)) {
                $labIds = array_values(array_filter(array_map(fn($r)=>$r['id']??null, $labs)));
                if (!empty($labIds)) {
                    $billedSet = [];
                    $q = $db->table('billing_items')->select('lab_id')->whereIn('lab_id', $labIds)->get();
                    foreach ($q->getResultArray() as $r) { if (!empty($r['lab_id'])) { $billedSet[$r['lab_id']] = true; } }
                    if (!empty($billedSet)) {
                        $labs = array_values(array_filter($labs, function($r) use ($billedSet){
                            $id = $r['id'] ?? null;
                            return !$id || !isset($billedSet[$id]);
                        }));
                    }
                }
            }
        } catch (\Throwable $e) { /* ignore */ }

        $items = [];
        foreach ($labs as $row) {
            $desc = 'Lab: ' . ($row['test_type'] ?? ($row['test_name'] ?? 'Laboratory Test'));
            $price = (float)($row['cost'] ?? 0);
            // Fallback to services.base_price when lab cost is missing/zero
            if ($price <= 0) {
                $svc = null;
                // helper normalizer
                $norm = function($s){
                    $s = strtolower(trim((string)$s));
                    // remove non-alphanumeric
                    $s = preg_replace('/[^a-z0-9]+/','', $s);
                    return $s ?: '';
                };
                // alias map from normalized keys to service codes
                $alias = [
                    'bloodtest'   => 'LAB-BLOOD',
                    'urinetest'   => 'LAB-URINE',
                    'xray'        => 'IMG-XRAY',
                    'mri'         => 'IMG-MRI',
                    'mriscan'     => 'IMG-MRI',
                    'ct'          => 'IMG-CT',
                    'ctscan'      => 'IMG-CT',
                    'ultrasound'  => 'IMG-US',
                    'ecg'         => 'CARD-ECG',
                    // common variants
                    'xrayplain'   => 'IMG-XRAY',
                    'xrayexam'    => 'IMG-XRAY',
                ];

                $candidates = [];
                if (!empty($row['test_type'])) { $candidates[] = $row['test_type']; }
                if (!empty($row['test_name'])) { $candidates[] = $row['test_name']; }

                foreach ($candidates as $cand) {
                    $code = $alias[$norm($cand)] ?? null;
                    if ($code) {
                        $svc = $svcModel->where('code', $code)->where('active', 1)->get()->getRowArray();
                        if ($svc) break;
                    }
                    // try exact code/name first
                    $svc = $svcModel->findByCodeOrName($cand);
                    if ($svc) break;
                    // case-insensitive name equals
                    $svc = $svcModel->where('LOWER(name)', strtolower($cand))->where('active', 1)->get()->getRowArray();
                    if ($svc) break;
                    // loose LIKE on name
                    $svc = $svcModel->like('name', $cand, 'both')->where('active', 1)->get()->getRowArray();
                    if ($svc) break;
                }
                if ($svc) { $price = (float)($svc['base_price'] ?? 0); }
            }
            if ($price <= 0) continue;
            $items[] = [
                'service' => $desc,
                'qty' => 1,
                'price' => $price,
                'amount' => $price,
                'lab_id' => $row['id'] ?? null,
            ];
        }

        // Optional debug payload
        $debug = (int)($this->request->getGet('debug') ?? 0);
        if ($debug === 1) {
            return $this->response->setJSON([
                'items' => $items,
                'debug' => [
                    'patient_id' => $patientId,
                    'patient_name' => $patientName,
                    'labs_found' => $labs,
                ]
            ]);
        }

        return $this->response->setJSON(['items' => $items]);
    }
}
