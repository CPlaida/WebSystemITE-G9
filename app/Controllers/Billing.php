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
        $term = $this->request->getGet('q');
        $bills = $this->billingModel->getList($term);
        $totals = $this->billingModel->getTotals();
        return view('Roles/admin/Billing & payment/billingmanagement', [
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
        return $this->response->setJSON(['status' => 'success', 'message' => 'Bill updated successfully']);
    }

    public function delete($id)
    {
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
