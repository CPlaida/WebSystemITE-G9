<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\BillingModel;
use App\Models\LaboratoryModel;
use App\Models\ServiceModel;
use App\Models\Financial\PhilHealthAuditModel;
use App\Models\Financial\PhilHealthCaseRateModel;
use App\Models\Financial\HmoProviderModel;
use App\Models\Financial\HmoAuthorizationModel;
use App\Models\PatientModel;
use App\Services\PhilHealthCaseRateService;
use App\Services\Billing\BillingChargeAggregator;

class Billing extends BaseController
{
    protected $billingModel;

    public function __construct()
    {
        $this->billingModel = new BillingModel();
        helper(['form', 'url']);
    }

    /**
     * Get role-based view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleMap = [
            'admin' => 'admin',
            'accounting' => 'admin', // Accountants use admin views (unified)
            'accountant' => 'admin', // Accountants use admin views (unified)
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/Billing & payment/{$viewName}";
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
            try {
                $fields = array_map('strtolower', $db->getFieldNames('billing_items'));
                if (!in_array('service_id', $fields, true)) {
                    $db->query("ALTER TABLE billing_items ADD COLUMN service_id INT UNSIGNED NULL AFTER billing_id");
                    $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_service_id ON billing_items(service_id)");
                }
                if (!in_array('lab_id', $fields, true)) {
                    $db->query("ALTER TABLE billing_items ADD COLUMN lab_id VARCHAR(20) NULL AFTER service_id");
                    $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_lab_id ON billing_items(lab_id)");
                }
                if (!in_array('source_table', $fields, true)) {
                    $db->query("ALTER TABLE billing_items ADD COLUMN source_table VARCHAR(64) NULL AFTER lab_id");
                    $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_source_table ON billing_items(source_table)");
                }
                if (!in_array('source_id', $fields, true)) {
                    $db->query("ALTER TABLE billing_items ADD COLUMN source_id VARCHAR(64) NULL AFTER source_table");
                    $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_source_ref ON billing_items(source_table, source_id)");
                }
            } catch (\Throwable $e) { /* ignore */ }
            return;
        }
        $db->query("CREATE TABLE IF NOT EXISTS billing_items (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            billing_id INT UNSIGNED NOT NULL,
            service_id INT UNSIGNED NULL,
            lab_id VARCHAR(20) NULL,
            source_table VARCHAR(64) NULL,
            source_id VARCHAR(64) NULL,
            service VARCHAR(255) NOT NULL,
            qty INT UNSIGNED NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            INDEX idx_billing_items_billing_id (billing_id),
            INDEX idx_billing_items_service_id (service_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        try { $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_lab_id ON billing_items(lab_id)"); } catch (\Throwable $e) { /* ignore */ }
        try { $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_source_table ON billing_items(source_table)"); } catch (\Throwable $e) { /* ignore */ }
        try { $db->query("CREATE INDEX IF NOT EXISTS idx_billing_items_source_ref ON billing_items(source_table, source_id)"); } catch (\Throwable $e) { /* ignore */ }
    }

    /**
     * Mark source items as billed only if payment status is 'paid'
     * 
     * @param array $items Billing items with source_table and source_id
     * @param string|null $paymentStatus Payment status ('paid', 'pending', 'partial', 'overdue')
     */
    private function markSourcesAsBilled(array $items, ?string $paymentStatus = null): void
    {
        // Only mark as billed if payment status is 'paid'
        if (strtolower(trim($paymentStatus ?? '')) !== 'paid') {
            return;
        }
        
        if (empty($items)) {
            return;
        }
        $db = \Config\Database::connect();
        $grouped = [];
        foreach ($items as $item) {
            if (!empty($item['lab_id'])) {
                $grouped['laboratory'][] = (string)$item['lab_id'];
            }
            $table = $item['source_table'] ?? null;
            $sourceId = $item['source_id'] ?? null;
            if ($table && $sourceId) {
                $tableKey = strtolower($table);
                $grouped[$tableKey][] = (string)$sourceId;
            }
        }
        foreach ($grouped as $table => $ids) {
            $ids = array_values(array_unique(array_filter(array_map('strval', $ids))));
            if (empty($ids)) {
                continue;
            }
            if (!$db->tableExists($table) || !$this->hasField($table, 'billed')) {
                continue;
            }
            try {
                $db->table($table)->whereIn('id', $ids)->set('billed', 1)->update();
            } catch (\Throwable $e) { /* ignore */ }
        }
    }

    private function syncHmoAuthorization(int $billId, ?string $patientId, array $payload): void
    {
        $authModel = new HmoAuthorizationModel();
        $fields = [
            'hmo_provider_id', 'hmo_loa_number', 'hmo_coverage_limit',
            'hmo_approved_amount', 'hmo_patient_share', 'hmo_status', 'hmo_notes'
        ];
        $hasData = false;
        foreach ($fields as $field) {
            if (!empty($payload[$field])) { $hasData = true; break; }
        }

        if (!$hasData) {
            $authModel->where('billing_id', $billId)->delete();
            return;
        }

        $record = [
            'billing_id' => $billId,
            'patient_id' => $patientId,
            'provider_id' => $payload['hmo_provider_id'] ?: null,
            'loa_number' => $payload['hmo_loa_number'] ?: null,
            'coverage_limit' => $payload['hmo_coverage_limit'] !== null ? (float)$payload['hmo_coverage_limit'] : null,
            'approved_amount' => $payload['hmo_approved_amount'] !== null ? (float)$payload['hmo_approved_amount'] : null,
            'patient_share' => $payload['hmo_patient_share'] !== null ? (float)$payload['hmo_patient_share'] : null,
            'status' => $payload['hmo_status'] ?: null,
            'notes' => $payload['hmo_notes'] ?: null,
        ];

        $existing = $authModel->where('billing_id', $billId)->first();
        if ($existing) {
            $record['id'] = $existing['id'];
        }
        $authModel->save($record);
    }

    private function normalizeCoverageSplits(array $payload, array $existing = []): array
    {
        $final = $payload['final_amount'] ?? ($existing['final_amount'] ?? null);
        if ($final === null) {
            return $payload;
        }
        $final = (float)$final;
        $payload['final_amount'] = $final;

        $philhealthApproved = $payload['philhealth_approved_amount'] ?? ($existing['philhealth_approved_amount'] ?? null);
        if ($philhealthApproved === null || $philhealthApproved === '') {
            $philhealthApproved = 0.0;
        }
        $philhealthApproved = max(0.0, min((float)$philhealthApproved, $final));
        $payload['philhealth_approved_amount'] = $philhealthApproved;

        $remainingAfterPhilhealth = max($final - $philhealthApproved, 0.0);

        $hmoApproved = $payload['hmo_approved_amount'] ?? ($existing['hmo_approved_amount'] ?? null);
        if ($hmoApproved === null || $hmoApproved === '') {
            $hmoApproved = 0.0;
        }
        $hmoApproved = max(0.0, min((float)$hmoApproved, $remainingAfterPhilhealth));
        $payload['hmo_approved_amount'] = $hmoApproved;

        $payload['hmo_patient_share'] = max($remainingAfterPhilhealth - $hmoApproved, 0.0);

        return $payload;
    }

    public function index()
    {
        // Only admin and accounting can access billing
        $this->requireRole(['admin', 'accounting']);
        
        $term = $this->request->getGet('q');
        $bills = $this->billingModel->getList($term);
        $totals = $this->billingModel->getTotals();
        return view($this->getRoleViewPath('billingmanagement'), [
            'bills' => $bills,
            'totals' => $totals,
            'query' => (string)($term ?? ''),
            'hmoProviders' => (new HmoProviderModel())
                ->where('active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ]);
    }

    public function process($id = null)
    {
        // Only admin and accounting can process bills
        $this->requireRole(['admin', 'accounting']);
        
        $data = [
            'title' => 'Bill Process',
            'active_menu' => 'billing',
            'validation' => \Config\Services::validation(),
            'bill' => null,
            'billItems' => [],
            'hmoProviders' => (new HmoProviderModel())
                ->where('active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
        ];

        if ($id) {
            $bill = $this->billingModel->findWithRelations((int)$id);
            if ($bill) {
                $data['bill'] = $bill;
                // Load existing items for edit mode
                $db = \Config\Database::connect();
                $this->ensureBillingItemsTable();
                if ($db->tableExists('billing_items')) {
                    $rows = $db->table('billing_items')->where('billing_id', (int)$id)->get()->getResultArray();
                    foreach ($rows as $ri) {
                        $data['billItems'][] = [
                            'service' => $ri['service'] ?? '',
                            'qty' => (int)($ri['qty'] ?? 1),
                            'price' => (float)($ri['price'] ?? 0),
                            'amount' => (float)($ri['amount'] ?? 0),
                            'lab_id' => $ri['lab_id'] ?? null,
                        ];
                    }
                }
            }
        }

        return view($this->getRoleViewPath('bill_process'), $data);
    }
    
    public function save()
    {
        // Only admin and accounting can save bills
        $this->requireRole(['admin', 'accounting']);
        // Backward-compat shim. Delegate to store().
        return $this->store();
    }

    public function create()
    {
        // Only admin and accounting can create bills
        $this->requireRole(['admin', 'accounting']);
        // Return view for accountant/admin, JSON for others
        $role = session('role');
        if ($role === 'accountant' || $role === 'accounting' || $role === 'admin') {
            $data = [
                'title' => 'Create Bill',
                'active_menu' => 'billing',
                'validation' => \Config\Services::validation(),
                'hmoProviders' => (new HmoProviderModel())
                    ->where('active', 1)
                    ->orderBy('name', 'ASC')
                    ->findAll(),
            ];
            return view($this->getRoleViewPath('bill_process'), $data);
        }
        return $this->response->setJSON(['ok' => true]);
    }

    public function store()
    {
        // Only admin and accounting can store bills
        $this->requireRole(['admin', 'accounting']);
        
        $rules = [
            'patient_id' => 'required',
            'final_amount' => 'required|numeric',
            'payment_status' => 'required|in_list[pending,partial,paid,overdue]',
            'payment_method' => 'required|in_list[cash,credit,debit]',
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
            'philhealth_member' => $this->request->getPost('philhealth_member') ? 1 : 0,
            'philhealth_approved_amount' => $this->request->getPost('philhealth_approved_amount') !== null ? (float)$this->request->getPost('philhealth_approved_amount') : 0.0,
            'primary_icd10_code' => $this->request->getPost('primary_icd10_code') ?: null,
            'primary_rvs_code' => $this->request->getPost('primary_rvs_code') ?: null,
            'admission_date' => $this->request->getPost('admission_date') ?: null,
            'hmo_provider_id' => $this->request->getPost('hmo_provider_id') ?: null,
            'hmo_member_no' => $this->request->getPost('hmo_member_no') ?: null,
            'hmo_valid_from' => $this->request->getPost('hmo_valid_from') ?: null,
            'hmo_valid_to' => $this->request->getPost('hmo_valid_to') ?: null,
            'hmo_loa_number' => $this->request->getPost('hmo_loa_number') ?: null,
            'hmo_coverage_limit' => $this->request->getPost('hmo_coverage_limit') ?: null,
            'hmo_approved_amount' => $this->request->getPost('hmo_approved_amount') ?: null,
            'hmo_patient_share' => $this->request->getPost('hmo_patient_share') ?: null,
            'hmo_status' => $this->request->getPost('hmo_status') ?: null,
            'hmo_notes' => $this->request->getPost('hmo_notes') ?: null,
            'bill_date' => $this->request->getPost('bill_date'),
            'notes' => $this->request->getPost('notes'),
        ];

        $payload = $this->normalizeCoverageSplits($payload);

        $id = $this->billingModel->insert($payload, true);
        $this->syncHmoAuthorization($id, $payload['patient_id'], $payload);

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
                // Determine category from source_table or service name
                $category = 'general';
                $sourceTable = $ri['source_table'] ?? '';
                $serviceName = strtolower($ri['service'] ?? '');
                
                if ($sourceTable === 'laboratory') {
                    $category = 'laboratory';
                } elseif ($sourceTable === 'pharmacy_transactions') {
                    $category = 'pharmacy';
                } elseif ($sourceTable === 'appointments') {
                    $category = 'consultation';
                } elseif ($sourceTable === 'admission_details' || $sourceTable === 'patients') {
                    // Check if it's a room charge by service name
                    if (stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false) {
                        $category = 'room';
                    } else {
                        $category = 'general';
                    }
                } elseif (stripos($serviceName, 'laboratory') !== false || stripos($serviceName, 'lab') !== false) {
                    $category = 'laboratory';
                } elseif (stripos($serviceName, 'pharmacy') !== false) {
                    $category = 'pharmacy';
                } elseif (stripos($serviceName, 'appointment') !== false || stripos($serviceName, 'consultation') !== false) {
                    $category = 'consultation';
                } elseif (stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false) {
                    $category = 'room';
                }
                
                $items[] = [
                    'description' => $ri['service'] ?? '',
                    'quantity' => (int)($ri['qty'] ?? 0),
                    'unit_price' => (float)($ri['price'] ?? 0),
                    'amount' => (float)($ri['amount'] ?? 0),
                    'category' => $category,
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

        // Load HMO provider name if hmo_provider_id exists
        if (!empty($bill['hmo_provider_id']) && empty($bill['hmo_provider_name'])) {
            try {
                $hmoProvider = (new HmoProviderModel())->find((int)$bill['hmo_provider_id']);
                if ($hmoProvider) {
                    $bill['hmo_provider_name'] = $hmoProvider['name'] ?? '';
                }
            } catch (\Throwable $e) {
                // Ignore if provider not found
            }
        }

        // Load attending physician from admission_details or appointments
        $attendingPhysician = null;
        if (!empty($bill['patient_id'])) {
            $patientId = $bill['patient_id'];
            
            // First, try to get from admission_details (most recent admission)
            if ($db->tableExists('admission_details')) {
                try {
                    $admission = $db->table('admission_details ad')
                        ->select('sp.first_name, sp.last_name, u.username, ad.attending_doctor_id')
                        ->join('staff_profiles sp', 'sp.id = ad.attending_doctor_id', 'left')
                        ->join('users u', 'u.id = sp.user_id', 'left')
                        ->where('ad.patient_id', $patientId)
                        ->whereIn('ad.status', ['admitted', 'discharged'])
                        ->orderBy('ad.admission_date', 'DESC')
                        ->orderBy('ad.created_at', 'DESC')
                        ->limit(1)
                        ->get()
                        ->getRowArray();
                    
                    if ($admission && !empty($admission['attending_doctor_id'])) {
                        $firstName = trim($admission['first_name'] ?? '');
                        $lastName = trim($admission['last_name'] ?? '');
                        $username = $admission['username'] ?? '';
                        
                        if (!empty($firstName) || !empty($lastName)) {
                            $attendingPhysician = trim($firstName . ' ' . $lastName);
                            // Remove "User" word if present
                            $attendingPhysician = preg_replace('/\s+User\s*$/i', '', $attendingPhysician);
                            $attendingPhysician = trim($attendingPhysician);
                        } elseif (!empty($username)) {
                            $attendingPhysician = preg_replace('/\s+User\s*$/i', '', $username);
                            $attendingPhysician = trim($attendingPhysician);
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore errors
                }
            }
            
            // If no admission found, try to get from most recent appointment
            if (empty($attendingPhysician) && $db->tableExists('appointments')) {
                try {
                    $appointment = $db->table('appointments a')
                        ->select('sp.first_name, sp.last_name, u.username, a.doctor_id')
                        ->join('staff_profiles sp', 'sp.id = a.doctor_id', 'left')
                        ->join('users u', 'u.id = sp.user_id', 'left')
                        ->join('roles r', 'r.id = sp.role_id', 'left')
                        ->where('r.name', 'doctor')
                        ->where('a.patient_id', $patientId)
                        ->whereIn('a.status', ['completed', 'confirmed', 'in_progress'])
                        ->orderBy('a.appointment_date', 'DESC')
                        ->orderBy('a.created_at', 'DESC')
                        ->limit(1)
                        ->get()
                        ->getRowArray();
                    
                    if ($appointment && !empty($appointment['doctor_id'])) {
                        $firstName = trim($appointment['first_name'] ?? '');
                        $lastName = trim($appointment['last_name'] ?? '');
                        $username = $appointment['username'] ?? '';
                        
                        if (!empty($firstName) || !empty($lastName)) {
                            $attendingPhysician = trim($firstName . ' ' . $lastName);
                            // Remove "User" word if present
                            $attendingPhysician = preg_replace('/\s+User\s*$/i', '', $attendingPhysician);
                            $attendingPhysician = trim($attendingPhysician);
                        } elseif (!empty($username)) {
                            $attendingPhysician = preg_replace('/\s+User\s*$/i', '', $username);
                            $attendingPhysician = trim($attendingPhysician);
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore errors
                }
            }
        }
        
        // Set consulting_doctor if we found an attending physician
        if (!empty($attendingPhysician)) {
            $bill['consulting_doctor'] = $attendingPhysician;
        }

        // Provide aliases used by receipt view (derived from numeric id)
        $bill['bill_number'] = 'INV-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT);
        $bill['date_issued'] = $bill['bill_date'] ?? date('Y-m-d');
        $bill['status'] = $bill['payment_status'] ?? 'pending';
        $bill['items'] = $items;
        $bill['subtotal'] = $subtotal;
        $bill['tax'] = $tax;
        $bill['total'] = $total;

        return view($this->getRoleViewPath('receipt'), ['bill' => $bill]);
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
        if (!empty($bill['patient_id'])) {
            $needsHydration = empty($bill['hmo_provider_id']) && empty($bill['hmo_member_no']);
            if ($needsHydration) {
                $patient = (new PatientModel())->find($bill['patient_id']);
                if ($patient) {
                    foreach ([
                        'hmo_provider_id',
                        'hmo_member_no',
                        'hmo_valid_from',
                        'hmo_valid_to',
                        'hmo_loa_number',
                        'hmo_coverage_limit',
                        'hmo_status',
                        'hmo_notes'
                    ] as $field) {
                        if (empty($bill[$field]) && isset($patient[$field])) {
                            $bill[$field] = $patient[$field];
                        }
                    }
                }
            }
        }
        try {
            $svc = new PhilHealthCaseRateService();
            $adate = $bill['admission_date'] ?? ($bill['bill_date'] ?? date('Y-m-d'));
            $res = $svc->suggest($bill['primary_rvs_code'] ?? null, $bill['primary_icd10_code'] ?? null, $adate ?: null);
            $gross = (float)($bill['final_amount'] ?? ($bill['total_amount'] ?? 0));
            $suggested = min((float)($res['suggested_amount'] ?? 0), max($gross, 0));
            $bill['philhealth_suggested_amount_calc'] = $suggested;
            $bill['philhealth_codes_used_calc'] = $res['codes_used'] ?? null;
            $bill['philhealth_rate_ids_calc'] = $res['rate_ids'] ?? [];
        } catch (\Throwable $e) { /* ignore suggestion errors */ }
        return $this->response->setJSON($bill);
    }

    public function update($id)
    {
        // Only admin and accounting can update bills
        $this->requireRole(['admin', 'accounting']);
        $rules = [
            'patient_id' => 'permit_empty',
            'service_id' => 'permit_empty|integer',
            'final_amount' => 'permit_empty|numeric',
            'payment_status' => 'permit_empty|in_list[pending,partial,paid,overdue]',
            'bill_date' => 'permit_empty|valid_date',
            'payment_method' => 'permit_empty|in_list[cash,credit,debit]',
            'philhealth_member' => 'permit_empty|in_list[0,1]',
            'philhealth_approved_amount' => 'permit_empty|numeric',
            'primary_icd10_code' => 'permit_empty|string',
            'primary_rvs_code' => 'permit_empty|string',
            'admission_date' => 'permit_empty|valid_date',
        ];
        $useHmoToggle = (string)$this->request->getPost('use_hmo') === '1';
        $requiresHmoFields = $useHmoToggle;
        if ($requiresHmoFields) {
            $rules['hmo_provider_id'] = 'required';
            $rules['hmo_loa_number'] = 'required|min_length[3]';
        }
        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON(['errors' => $this->validator->getErrors()]);
        }

        $existingBill = $this->billingModel->find((int)$id) ?: [];

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
            'hmo_provider_id' => $this->request->getPost('hmo_provider_id') ?: null,
            'hmo_member_no' => $this->request->getPost('hmo_member_no') ?: null,
            'hmo_loa_number' => $this->request->getPost('hmo_loa_number') ?: null,
            'hmo_coverage_limit' => $this->request->getPost('hmo_coverage_limit') ?: null,
            'hmo_approved_amount' => $this->request->getPost('hmo_approved_amount') ?: null,
            'hmo_patient_share' => $this->request->getPost('hmo_patient_share') ?: null,
            'hmo_status' => $this->request->getPost('hmo_status') ?: null,
            'hmo_notes' => $this->request->getPost('hmo_notes') ?: null,
            'bill_date' => $this->request->getPost('bill_date') ?? null,
            'notes' => $this->request->getPost('notes') ?? null,
            'philhealth_member' => $this->request->getPost('philhealth_member') !== null ? (int)$this->request->getPost('philhealth_member') : null,
            'philhealth_approved_amount' => $this->request->getPost('philhealth_approved_amount') !== null ? (float)$this->request->getPost('philhealth_approved_amount') : null,
            'primary_icd10_code' => $this->request->getPost('primary_icd10_code') !== null ? (string)$this->request->getPost('primary_icd10_code') : null,
            'primary_rvs_code' => $this->request->getPost('primary_rvs_code') !== null ? (string)$this->request->getPost('primary_rvs_code') : null,
            'admission_date' => $this->request->getPost('admission_date') ?? null,
        ];
        $forceNullKeys = [];
        if (!$requiresHmoFields) {
            foreach ([
                'hmo_provider_id','hmo_member_no','hmo_valid_from','hmo_valid_to',
                'hmo_loa_number','hmo_coverage_limit','hmo_status','hmo_notes'
            ] as $field) {
                $data[$field] = null;
                $forceNullKeys[] = $field;
            }
            $data['hmo_approved_amount'] = 0.0;
            $data['hmo_patient_share'] = null; // normalizeCoverageSplits recalculates
        }

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

        // PhilHealth validation and audit (Dropdown-driven)
        $isMember = (int)($this->request->getPost('philhealth_member') ?? 0) === 1;
        $approved = $this->request->getPost('philhealth_approved_amount');
        $approved = $approved !== null ? (float)$approved : null;
        if ($isMember) {
            $gross = null;
            if (isset($data['final_amount'])) { $gross = (float)$data['final_amount']; }
            else if (isset($existingBill['final_amount'])) { $gross = (float)$existingBill['final_amount']; }
            else { $gross = 0.0; }

            $selectedRateRaw = $this->request->getPost('philhealth_selected_rate_id');
            $selectedRateIds = [];
            if (is_array($selectedRateRaw)) {
                foreach ($selectedRateRaw as $rid) {
                    $rid = trim((string)$rid);
                    if ($rid !== '') { $selectedRateIds[] = $rid; }
                }
            } elseif ($selectedRateRaw !== null && $selectedRateRaw !== '') {
                $decoded = json_decode((string)$selectedRateRaw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    foreach ($decoded as $rid) {
                        $rid = trim((string)$rid);
                        if ($rid !== '') { $selectedRateIds[] = $rid; }
                    }
                } else {
                    $rid = trim((string)$selectedRateRaw);
                    if ($rid !== '') { $selectedRateIds[] = $rid; }
                }
            }

            $selectedAmountInput = $this->request->getPost('philhealth_selected_amount');
            $selectedAmount = $selectedAmountInput !== null && $selectedAmountInput !== ''
                ? max((float)$selectedAmountInput, 0.0)
                : null;

            if ($approved === null) {
                return $this->response->setStatusCode(422)->setJSON(['errors' => ['philhealth_approved_amount' => 'PhilHealth Approved Deduction Amount is required.']]);
            }

            $data['philhealth_approved_amount'] = $approved;
            $codesUsed = [];
            $rateIds = [];
            $suggested = 0.0;
            if (!empty($selectedRateIds)) {
                $phModel = new PhilHealthCaseRateModel();
                foreach ($selectedRateIds as $rid) {
                    $rateIds[] = $rid;
                    try {
                        $rate = $phModel->find($rid);
                    } catch (\Throwable $e) {
                        $rate = null;
                    }
                    if ($rate) {
                        $amount = (float)(($rate['professional_share'] ?? 0) + ($rate['facility_share'] ?? 0));
                        $suggested += $amount;
                        $codesUsed[] = [$rate['code_type'] ?? null, $rate['code'] ?? null];
                    }
                }
                if ($suggested <= 0 && $selectedAmount !== null) {
                    $suggested = $selectedAmount;
                }
            } elseif ($selectedAmount !== null) {
                $suggested = $selectedAmount;
            }

            if ($approved > $gross) {
                $approved = $gross;
            }

            // Cap by combined case rate amount
            $suggested = min($suggested, max($gross, 0));

            if ($suggested > 0 && $approved > $suggested) {
                $approved = $suggested;
            }

            $data['philhealth_approved_amount'] = $approved;
            $data['philhealth_suggested_amount'] = $suggested;
            $data['philhealth_codes_used'] = !empty($codesUsed) ? json_encode($codesUsed) : ($data['philhealth_codes_used'] ?? null);
            $data['philhealth_rate_ids'] = !empty($rateIds) ? json_encode($rateIds) : ($data['philhealth_rate_ids'] ?? null);
            $data['philhealth_verified_by'] = (string)(session()->get('id') ?? session()->get('user_id') ?? session()->get('username') ?? 'unknown');
            $data['philhealth_verified_at'] = date('Y-m-d H:i:s');

            $audit = new PhilHealthAuditModel();
            $audit->insert([
                'bill_id' => (int)$id,
                'patient_id' => (string)($data['patient_id'] ?? ($existingBill['patient_id'] ?? '')),
                'suggested_amount' => $suggested,
                'approved_amount' => $approved,
                'officer_user_id' => (string)($data['philhealth_verified_by'] ?? ''),
                'codes_used' => !empty($codesUsed) ? json_encode($codesUsed) : null,
                'rate_ids' => !empty($rateIds) ? json_encode($rateIds) : null,
                'notes' => (string)($this->request->getPost('philhealth_note') ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $data = $this->normalizeCoverageSplits($data, $existingBill);

        // Remove nulls to avoid overwriting
        if (!empty($forceNullKeys)) {
            $data = array_filter(
                $data,
                function ($v, $k) use ($forceNullKeys) {
                    if (in_array($k, $forceNullKeys, true)) {
                        return true; // keep explicit nulls so fields get cleared
                    }
                    return $v !== null;
                },
                ARRAY_FILTER_USE_BOTH
            );
        } else {
            $data = array_filter($data, fn($v) => $v !== null);
        }

        $this->billingModel->save($data);
        $targetPatient = $data['patient_id'] ?? ($existingBill['patient_id'] ?? null);
        $hmoPayload = array_merge($existingBill, $data);
        $this->syncHmoAuthorization((int)$id, $targetPatient, $hmoPayload);
        
        // If payment status changed to 'paid', mark billing items as billed
        $newPaymentStatus = $data['payment_status'] ?? null;
        $oldPaymentStatus = $existingBill['payment_status'] ?? null;
        if ($newPaymentStatus && strtolower($newPaymentStatus) === 'paid' && strtolower($oldPaymentStatus ?? '') !== 'paid') {
            // Get billing items for this bill
            $db = \Config\Database::connect();
            $this->ensureBillingItemsTable();
            if ($db->tableExists('billing_items')) {
                $billingItems = $db->table('billing_items')
                    ->where('billing_id', (int)$id)
                    ->get()
                    ->getResultArray();
                
                $items = [];
                foreach ($billingItems as $bi) {
                    $items[] = [
                        'lab_id' => $bi['lab_id'] ?? null,
                        'source_table' => $bi['source_table'] ?? null,
                        'source_id' => $bi['source_id'] ?? null,
                    ];
                }
                
                if (!empty($items)) {
                    $this->markSourcesAsBilled($items, 'paid');
                }
            }
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Bill updated successfully']);
        }
        return redirect()->to(base_url('billing'))->with('message', 'Bill updated successfully');
    }

    public function delete($id)
    {
        // Only admin and accounting can delete bills
        $this->requireRole(['admin', 'accounting']);
        
        $this->billingModel->delete($id);
        (new HmoAuthorizationModel())->where('billing_id', (int)$id)->delete();
        return $this->response->setJSON(['status' => 'success', 'message' => 'Bill deleted successfully']);
    }

    public function storeWithItems()
    {
        $rules = [
            'patient_id' => 'required',
            'bill_date' => 'required|valid_date',
            'payment_method' => 'permit_empty|in_list[cash,credit,debit]'
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
        $sourceTables = (array) $this->request->getPost('source_table');
        $sourceIds = (array) $this->request->getPost('source_id');

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
                'source_table' => isset($sourceTables[$i]) ? trim((string)$sourceTables[$i]) : null,
                'source_id' => isset($sourceIds[$i]) ? trim((string)$sourceIds[$i]) : null,
            ];
            $subtotal += $amt;
        }

        $tax = round($subtotal * 0.12, 2);
        $total = round($subtotal + $tax, 2);

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $patientId = (string)$this->request->getPost('patient_id');
            $paymentStatus = $this->request->getPost('payment_status') ?: 'pending';
            
            // Check if there's an existing unpaid/pending bill for this patient
            $existingBill = $this->billingModel
                ->where('patient_id', $patientId)
                ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
                ->orderBy('created_at', 'DESC')
                ->first();
            
            $billId = null;
            $isUpdate = false;
            
            if ($existingBill) {
                // Update existing bill
                $billId = (int)$existingBill['id'];
                $isUpdate = true;
                
                // Get existing items to merge with new items
                $this->ensureBillingItemsTable();
                $existingItems = [];
                if ($db->tableExists('billing_items')) {
                    $existingItems = $db->table('billing_items')
                        ->where('billing_id', $billId)
                        ->get()
                        ->getResultArray();
                }
                
                // Build a set of existing item identifiers to filter duplicates
                $existingItemKeys = [];
                foreach ($existingItems as $ei) {
                    $key = '';
                    if (!empty($ei['lab_id'])) {
                        $key = 'lab_' . $ei['lab_id'];
                    } elseif (!empty($ei['source_table']) && !empty($ei['source_id'])) {
                        $key = $ei['source_table'] . '_' . $ei['source_id'];
                    }
                    if ($key !== '') {
                        $existingItemKeys[$key] = true;
                    }
                }
                
                // Filter out items that already exist to get only new items
                $newItemsOnly = [];
                $newItemsSubtotal = 0.0;
                foreach ($items as $it) {
                    $key = '';
                    if (!empty($it['lab_id'])) {
                        $key = 'lab_' . $it['lab_id'];
                    } elseif (!empty($it['source_table']) && !empty($it['source_id'])) {
                        $key = $it['source_table'] . '_' . $it['source_id'];
                    }
                    
                    // Only count if not already in existing bill
                    if ($key === '' || !isset($existingItemKeys[$key])) {
                        $newItemsOnly[] = $it;
                        $newItemsSubtotal += $it['amount'];
                    }
                }
                
                // Calculate existing subtotal
                $existingSubtotal = 0.0;
                foreach ($existingItems as $ei) {
                    $existingSubtotal += (float)($ei['amount'] ?? 0);
                }
                
                // Merge subtotals (existing + only new items)
                $mergedSubtotal = $existingSubtotal + $newItemsSubtotal;
                $mergedTax = round($mergedSubtotal * 0.12, 2);
                $mergedTotal = round($mergedSubtotal + $mergedTax, 2);
                
                // Update items array to only include new items for insertion
                $items = $newItemsOnly;
                
                // Update bill totals
                $updatePayload = [
                    'total_amount' => $mergedSubtotal,
                    'tax' => $mergedTax,
                    'final_amount' => $mergedTotal,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $this->request->getPost('payment_method') ?: ($existingBill['payment_method'] ?? 'cash'),
                    'bill_date' => $this->request->getPost('bill_date') ?: ($existingBill['bill_date'] ?? date('Y-m-d')),
                    'notes' => $this->request->getPost('notes') ?: ($existingBill['notes'] ?? null),
                ];
                
                // Update HMO and PhilHealth fields if provided
                if ($this->request->getPost('philhealth_member') !== null) {
                    $updatePayload['philhealth_member'] = $this->request->getPost('philhealth_member') ? 1 : 0;
                }
                if ($this->request->getPost('philhealth_approved_amount') !== null) {
                    $updatePayload['philhealth_approved_amount'] = (float)$this->request->getPost('philhealth_approved_amount');
                }
                if ($this->request->getPost('primary_icd10_code') !== null) {
                    $updatePayload['primary_icd10_code'] = $this->request->getPost('primary_icd10_code');
                }
                if ($this->request->getPost('primary_rvs_code') !== null) {
                    $updatePayload['primary_rvs_code'] = $this->request->getPost('primary_rvs_code');
                }
                if ($this->request->getPost('admission_date') !== null) {
                    $updatePayload['admission_date'] = $this->request->getPost('admission_date');
                }
                if ($this->request->getPost('hmo_provider_id') !== null) {
                    $updatePayload['hmo_provider_id'] = $this->request->getPost('hmo_provider_id');
                }
                if ($this->request->getPost('hmo_member_no') !== null) {
                    $updatePayload['hmo_member_no'] = $this->request->getPost('hmo_member_no');
                }
                if ($this->request->getPost('hmo_valid_from') !== null) {
                    $updatePayload['hmo_valid_from'] = $this->request->getPost('hmo_valid_from');
                }
                if ($this->request->getPost('hmo_valid_to') !== null) {
                    $updatePayload['hmo_valid_to'] = $this->request->getPost('hmo_valid_to');
                }
                if ($this->request->getPost('hmo_loa_number') !== null) {
                    $updatePayload['hmo_loa_number'] = $this->request->getPost('hmo_loa_number');
                }
                if ($this->request->getPost('hmo_coverage_limit') !== null) {
                    $updatePayload['hmo_coverage_limit'] = $this->request->getPost('hmo_coverage_limit');
                }
                if ($this->request->getPost('hmo_approved_amount') !== null) {
                    $updatePayload['hmo_approved_amount'] = $this->request->getPost('hmo_approved_amount');
                }
                if ($this->request->getPost('hmo_patient_share') !== null) {
                    $updatePayload['hmo_patient_share'] = $this->request->getPost('hmo_patient_share');
                }
                if ($this->request->getPost('hmo_status') !== null) {
                    $updatePayload['hmo_status'] = $this->request->getPost('hmo_status');
                }
                if ($this->request->getPost('hmo_notes') !== null) {
                    $updatePayload['hmo_notes'] = $this->request->getPost('hmo_notes');
                }
                
                $updatePayload = $this->normalizeCoverageSplits($updatePayload);
                $this->billingModel->update($billId, $updatePayload);
                $this->syncHmoAuthorization($billId, $patientId, array_merge($existingBill, $updatePayload));
                
                // Add new items to existing bill (filter out items that already exist)
                if (!empty($items)) {
                    $this->ensureBillingItemsTable();
                    if ($db->tableExists('billing_items')) {
                        // Build a set of existing item identifiers to avoid duplicates
                        $existingItemKeys = [];
                        foreach ($existingItems as $ei) {
                            $key = '';
                            if (!empty($ei['lab_id'])) {
                                $key = 'lab_' . $ei['lab_id'];
                            } elseif (!empty($ei['source_table']) && !empty($ei['source_id'])) {
                                $key = $ei['source_table'] . '_' . $ei['source_id'];
                            }
                            if ($key !== '') {
                                $existingItemKeys[$key] = true;
                            }
                        }
                        
                        // Filter out items that already exist in the bill
                        $newItems = [];
                        foreach ($items as $it) {
                            $key = '';
                            if (!empty($it['lab_id'])) {
                                $key = 'lab_' . $it['lab_id'];
                            } elseif (!empty($it['source_table']) && !empty($it['source_id'])) {
                                $key = $it['source_table'] . '_' . $it['source_id'];
                            }
                            
                            // Only add if not already in existing bill
                            if ($key === '' || !isset($existingItemKeys[$key])) {
                                $newItems[] = $it;
                            }
                        }
                        
                        // Add only new items
                        if (!empty($newItems)) {
                            $now = date('Y-m-d H:i:s');
                            $rows = [];
                            foreach ($newItems as $it) {
                                $rows[] = [
                                    'billing_id' => $billId,
                                    'service_id' => isset($it['service_id']) ? (int)$it['service_id'] : null,
                                    'lab_id' => isset($it['lab_id']) ? (string)$it['lab_id'] : null,
                                    'source_table' => isset($it['source_table']) ? (string)$it['source_table'] : null,
                                    'source_id' => isset($it['source_id']) ? (string)$it['source_id'] : null,
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
                                $inserted = $db->table('billing_items')->where('billing_id', $billId)->countAllResults();
                                $expectedCount = count($existingItems) + count($rows);
                                if ($inserted < $expectedCount) {
                                    foreach ($rows as $r) {
                                        $db->table('billing_items')->insert($r);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // Create new bill
                $payload = [
                    'patient_id' => $patientId,
                    'total_amount' => $subtotal,
                    'tax' => $tax,
                    'final_amount' => $total,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $this->request->getPost('payment_method') ?: 'cash',
                    'philhealth_member' => $this->request->getPost('philhealth_member') ? 1 : 0,
                    'philhealth_approved_amount' => $this->request->getPost('philhealth_approved_amount') !== null ? (float)$this->request->getPost('philhealth_approved_amount') : 0.0,
                    'primary_icd10_code' => $this->request->getPost('primary_icd10_code') ?: null,
                    'primary_rvs_code' => $this->request->getPost('primary_rvs_code') ?: null,
                    'admission_date' => $this->request->getPost('admission_date') ?: null,
                    'hmo_provider_id' => $this->request->getPost('hmo_provider_id') ?: null,
                    'hmo_member_no' => $this->request->getPost('hmo_member_no') ?: null,
                    'hmo_valid_from' => $this->request->getPost('hmo_valid_from') ?: null,
                    'hmo_valid_to' => $this->request->getPost('hmo_valid_to') ?: null,
                    'hmo_loa_number' => $this->request->getPost('hmo_loa_number') ?: null,
                    'hmo_coverage_limit' => $this->request->getPost('hmo_coverage_limit') ?: null,
                    'hmo_approved_amount' => $this->request->getPost('hmo_approved_amount') ?: null,
                    'hmo_patient_share' => $this->request->getPost('hmo_patient_share') ?: null,
                    'hmo_status' => $this->request->getPost('hmo_status') ?: null,
                    'hmo_notes' => $this->request->getPost('hmo_notes') ?: null,
                    'bill_date' => $this->request->getPost('bill_date'),
                    'notes' => $this->request->getPost('notes'),
                ];
                $payload = $this->normalizeCoverageSplits($payload);
                $billId = $this->billingModel->insert($payload, true);
                $this->syncHmoAuthorization((int)$billId, $payload['patient_id'], $payload);

                if ($billId && !empty($items)) {
                    $this->ensureBillingItemsTable();
                    if ($db->tableExists('billing_items')) {
                        $now = date('Y-m-d H:i:s');
                        $rows = [];
                        foreach ($items as $it) {
                            $rows[] = [
                                'billing_id' => (int)$billId,
                                'service_id' => isset($it['service_id']) ? (int)$it['service_id'] : null,
                                'lab_id' => isset($it['lab_id']) ? (string)$it['lab_id'] : null,
                                'source_table' => isset($it['source_table']) ? (string)$it['source_table'] : null,
                                'source_id' => isset($it['source_id']) ? (string)$it['source_id'] : null,
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
            }

            // Only mark items as billed if payment status is 'paid'
            if (!empty($items)) {
                $this->markSourcesAsBilled($items, $paymentStatus);
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
        return redirect()->to(base_url('billing'))->with('message', 'Bill saved successfully');
    }

    /**
     * Return a list of billable services for a patient aggregated across modules.
     * Response format: { items: [...], breakdown: {...}, errors: {...} }
     */
    public function patientServices()
    {
        $patientId = trim((string)$this->request->getGet('patient_id'));
        if ($patientId === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'patient_id is required']);
        }

        $aggregator = new BillingChargeAggregator();
        $result = $aggregator->collect($patientId);
        
        // Check if there's an existing unpaid/pending bill for this patient
        $existingBill = $this->billingModel
            ->where('patient_id', $patientId)
            ->whereIn('payment_status', ['pending', 'partial', 'overdue'])
            ->orderBy('created_at', 'DESC')
            ->first();
        
        // If there's an existing bill, include its items and filter out duplicates from new charges
        if ($existingBill) {
            $db = \Config\Database::connect();
            $this->ensureBillingItemsTable();
            if ($db->tableExists('billing_items')) {
                $existingItems = $db->table('billing_items')
                    ->where('billing_id', (int)$existingBill['id'])
                    ->get()
                    ->getResultArray();
                
                // Build a set of existing item identifiers to filter duplicates
                $existingItemKeys = [];
                foreach ($existingItems as $ei) {
                    $key = '';
                    if (!empty($ei['lab_id'])) {
                        $key = 'lab_' . $ei['lab_id'];
                    } elseif (!empty($ei['source_table']) && !empty($ei['source_id'])) {
                        $key = $ei['source_table'] . '_' . $ei['source_id'];
                    }
                    if ($key !== '') {
                        $existingItemKeys[$key] = true;
                    }
                }
                
                // Convert existing items to the format expected by the frontend
                $existingItemsFormatted = [];
                foreach ($existingItems as $ei) {
                    // Determine category from source_table or service name
                    $category = 'general';
                    $sourceTable = $ei['source_table'] ?? '';
                    $serviceName = strtolower($ei['service'] ?? '');
                    
                    if ($sourceTable === 'laboratory') {
                        $category = 'laboratory';
                    } elseif ($sourceTable === 'pharmacy_transactions') {
                        $category = 'pharmacy';
                    } elseif ($sourceTable === 'appointments') {
                        $category = 'consultation';
                    } elseif ($sourceTable === 'admission_details' || $sourceTable === 'patients') {
                        if (stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false) {
                            $category = 'room';
                        }
                    } elseif (stripos($serviceName, 'laboratory') !== false || stripos($serviceName, 'lab') !== false) {
                        $category = 'laboratory';
                    } elseif (stripos($serviceName, 'pharmacy') !== false) {
                        $category = 'pharmacy';
                    } elseif (stripos($serviceName, 'appointment') !== false || stripos($serviceName, 'consultation') !== false) {
                        $category = 'consultation';
                    } elseif (stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false) {
                        $category = 'room';
                    }
                    
                    $existingItemsFormatted[] = [
                        'service' => $ei['service'] ?? '',
                        'qty' => (int)($ei['qty'] ?? 1),
                        'price' => (float)($ei['price'] ?? 0),
                        'amount' => (float)($ei['amount'] ?? 0),
                        'lab_id' => $ei['lab_id'] ?? null,
                        'source_table' => $ei['source_table'] ?? null,
                        'source_id' => $ei['source_id'] ?? null,
                        'category' => $category,
                        'is_existing' => true, // Mark as existing so frontend can display differently if needed
                    ];
                }
                
                // Filter out new charges that are already in the existing bill
                $newItems = $result['items'] ?? [];
                $filteredNewItems = [];
                foreach ($newItems as $item) {
                    $key = '';
                    if (!empty($item['lab_id'])) {
                        $key = 'lab_' . $item['lab_id'];
                    } elseif (!empty($item['source_table']) && !empty($item['source_id'])) {
                        $key = $item['source_table'] . '_' . $item['source_id'];
                    }
                    
                    // Only include if not already in existing bill
                    if ($key === '' || !isset($existingItemKeys[$key])) {
                        $filteredNewItems[] = $item;
                    }
                }
                
                // Merge: existing items first, then new items
                $result['items'] = array_merge($existingItemsFormatted, $filteredNewItems);
                
                // Add existing bill ID to result
                $result['existing_bill_id'] = (int)$existingBill['id'];
            }
        }

        return $this->response->setJSON($result);
    }

    /**
     * Return case rates filtered by RVS/ICD and admission date.
     * GET params: rvs, icd, admission (YYYY-MM-DD)
     * Response: { rates: [{ id, label, amount, code_type, code, case_type }] }
     */
    public function caseRates()
    {
        $rvs = trim((string)$this->request->getGet('rvs')) ?: null;
        $icd = trim((string)$this->request->getGet('icd')) ?: null;
        $admission = trim((string)$this->request->getGet('admission')) ?: date('Y-m-d'); // Default to today if not provided

        log_message('debug', 'caseRates called with params: ' . json_encode([
            'rvs' => $rvs,
            'icd' => $icd,
            'admission' => $admission
        ]));

        $model = new PhilHealthCaseRateModel();
        $builder = $model->builder();
        $builder->select('*');
        
        // If no codes provided, return all active rates for the admission date
        if (!$rvs && !$icd) {
            log_message('debug', 'No RVS or ICD code provided, returning all active rates');
        } else {
            $builder->groupStart();
            if ($rvs) { 
                log_message('debug', 'Filtering by RVS code: ' . $rvs);
                $builder->orGroupStart()
                    ->where('code_type', 'RVS')
                    ->like('code', $rvs, 'both')  // Use LIKE with wildcards for partial matching
                    ->groupEnd(); 
            }
            if ($icd) { 
                log_message('debug', 'Filtering by ICD code: ' . $icd);
                $builder->orGroupStart()
                    ->where('code_type', 'ICD')
                    ->like('code', $icd, 'both')  // Use LIKE with wildcards for partial matching
                    ->groupEnd(); 
            }
            $builder->groupEnd();
        }
        
        // Always filter by active status if the column exists
        if ($this->hasField('philhealth_case_rates', 'active')) {
            $builder->where('active', 1);
        }
        
        // Filter by effective date range
        log_message('debug', 'Filtering by admission date: ' . $admission);
        $builder->groupStart()
            ->where('effective_from IS NULL')
            ->orWhere("effective_from <= '" . $admission . "'");
        $builder->groupEnd();
        
        $builder->groupStart()
            ->where('effective_to IS NULL')
            ->orWhere("effective_to >= '" . $admission . "'");
        $builder->groupEnd();
        
        // Get the final query
        $query = $builder->getCompiledSelect();
        log_message('debug', 'Executing query: ' . $query);
        
        // Execute the query
        $rows = $builder->orderBy('case_type', 'DESC')->get()->getResultArray();
        log_message('debug', 'Found ' . count($rows) . ' matching case rates');

        $rates = array_map(function($r){
            $facility = (float)($r['facility_share'] ?? 0);
            $professional = (float)($r['professional_share'] ?? 0);
            $amount = $facility + $professional;
            if ($amount <= 0 && isset($r['rate_total'])) {
                $amount = (float)$r['rate_total'];
            }
            $label = sprintf('%s %s - %s (%s) - ₱%s', 
                $r['code_type'], 
                $r['code'], 
                $r['description'] ?? '', 
                strtoupper($r['case_type'] ?? ''), 
                number_format($amount, 2)
            );
            $rateData = [
                'id' => (string)$r['id'],
                'label' => $label,
                'amount' => $amount,
                'code_type' => $r['code_type'] ?? null,
                'code' => $r['code'] ?? null,
                'case_type' => $r['case_type'] ?? null,
            ];
            log_message('debug', 'Mapped rate: ' . json_encode($rateData));
            return $rateData;
        }, $rows);

        $response = ['rates' => $rates];
        log_message('debug', 'Returning response: ' . json_encode($response));
        
        return $this->response->setJSON($response);
    }
}
