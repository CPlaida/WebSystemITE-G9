<?php

namespace App\Controllers;

use App\Models\AdmissionModel;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Models\BedModel;
use App\Services\Billing\BillingChargeAggregator;

class Admissions extends BaseController
{
    protected $admissionModel;
    protected $patientModel;
    protected $userModel;
    protected $bedModel;

    public function __construct()
    {
        $this->admissionModel = new AdmissionModel();
        $this->patientModel   = new PatientModel();
        $this->userModel      = new UserModel();
        $this->bedModel       = new BedModel();
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
            'nurse' => 'admin', // Nurses use admin views (unified)
            'receptionist' => 'admin',
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/patients/{$viewName}";
    }

    public function create()
    {
        // Only admin, nurse, and receptionist can create admissions
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
        // Doctors list for dropdown (doctor_id used as FK to staff_profiles)
        $doctors = $this->userModel
            ->select("sp.id AS doctor_id, users.id AS user_id, COALESCE(NULLIF(CONCAT(sp.first_name, ' ', sp.last_name), ' '), users.username) AS display_name, sp.first_name, sp.last_name, users.username")
            ->join('roles r', 'users.role_id = r.id', 'left')
            ->join('staff_profiles sp', 'sp.user_id = users.id', 'left')
            ->where('r.name', 'doctor')
            ->where('users.status', 'active')
            ->where('sp.id IS NOT NULL', null, false)
            ->orderBy('sp.first_name', 'ASC')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        return view($this->getRoleViewPath('AdmissionRegister'), [
            'title' => 'New Admission',
            'doctors' => $doctors,
            'validation' => \Config\Services::validation(),
        ]);
    }

    /**
     * Check if a patient is already admitted
     */
    public function checkAdmission()
    {
        // Only admin, nurse, and receptionist can check admission status
        $this->requireRole(['admin', 'nurse', 'receptionist']);

        $patientId = $this->request->getGet('patient_id');
        if (!$patientId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Patient ID is required'
            ])->setStatusCode(400);
        }

        $existingAdmission = $this->admissionModel
            ->where('patient_id', $patientId)
            ->where('status', 'admitted')
            ->first();

        return $this->response->setJSON([
            'success' => true,
            'is_admitted' => !empty($existingAdmission),
            'admission' => $existingAdmission ? [
                'id' => $existingAdmission['id'],
                'admission_date' => $existingAdmission['admission_date'],
                'ward' => $existingAdmission['ward'],
                'room' => $existingAdmission['room'],
            ] : null
        ]);
    }

    public function store()
    {
        // Only admin, nurse, and receptionist can create admissions
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        $rules = [
            'patient_id' => 'required|string|is_not_unique[patients.id]',
            'admission_date' => 'required|valid_date',
            'admission_time' => 'permit_empty',
            'admission_type' => 'required|in_list[emergency,elective,transfer]',
            'attending_doctor_id' => 'required|is_not_unique[staff_profiles.id]',
            'ward' => 'permit_empty|max_length[100]',
            'room' => 'permit_empty|max_length[100]',
            'bed_id' => 'required|integer|is_not_unique[beds.id]',
            'admitting_diagnosis' => 'required|string',
            'reason_admission' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please fix the following errors',
                'errors' => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        // Verify patient exists
        $patientId = (string) $this->request->getPost('patient_id');
        $patient = $this->patientModel->find($patientId);
        if (!$patient) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Patient not found.'
            ])->setStatusCode(404);
        }

        // Check if patient is already admitted (prevent double admission)
        $existingAdmission = $this->admissionModel
            ->where('patient_id', $patientId)
            ->where('status', 'admitted')
            ->first();
        
        if ($existingAdmission) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This patient is already admitted and has not been discharged. Please discharge the patient first before creating a new admission.'
            ])->setStatusCode(409);
        }

        // Verify bed is available
        $bedId = (int) $this->request->getPost('bed_id');
        $bed = $this->bedModel->find($bedId);
        if (!$bed) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected bed does not exist.'
            ])->setStatusCode(422);
        }
        if (isset($bed['status']) && strtolower($bed['status']) !== 'available') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected bed is not available.'
            ])->setStatusCode(409);
        }

        $payload = [
            'patient_id' => $patientId,
            'admission_date' => $this->request->getPost('admission_date'),
            'admission_time' => $this->request->getPost('admission_time') ?: null,
            'admission_type' => $this->request->getPost('admission_type'),
            'attending_doctor_id' => (int) $this->request->getPost('attending_doctor_id'),
            'ward' => $this->request->getPost('ward') ?: null,
            'room' => $this->request->getPost('room') ?: null,
            'bed_id' => $bedId,
            'admitting_diagnosis' => $this->request->getPost('admitting_diagnosis'),
            'reason_admission' => $this->request->getPost('reason_admission') ?: null,
            'status' => 'admitted',
        ];

        if ($this->admissionModel->insert($payload)) {
            // Mark patient as inpatient and persist bed assignment
            try {
                $this->patientModel->update($patientId, [
                    'type' => 'inpatient',
                    'bed_id' => $bedId,
                ]);
            } catch (\Throwable $e) {
            }
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Admission registered successfully.',
                'data' => ['id' => $this->admissionModel->getInsertID()]
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to save admission. Please try again.'
        ])->setStatusCode(500);
    }

    public function discharge($id = null)
    {
        // Only admin and nurse can discharge patients
        $this->requireRole(['admin', 'nurse']);
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        $admissionId = (int) ($id ?? $this->request->getPost('admission_id'));
        if ($admissionId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid admission ID.'
            ])->setStatusCode(422);
        }

        $admission = $this->admissionModel->find($admissionId);
        if (!$admission) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admission record not found.'
            ])->setStatusCode(404);
        }

        if (($admission['status'] ?? '') !== 'admitted') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Patient is not currently admitted.'
            ])->setStatusCode(409);
        }

        // Check if patient has unpaid bills or unbilled charges before allowing discharge
        $patientId = $admission['patient_id'] ?? null;
        if (!empty($patientId)) {
            $db = db_connect();
            $hasUnpaidBills = false;
            $hasUnbilledCharges = false;
            $totalUnpaid = 0;
            $billCount = 0;
            $unbilledItemsCount = 0;
            $unbilledTotal = 0;
            
            // Check if billing table exists and has payment_status column
            if ($db->tableExists('billing')) {
                // Check for unpaid bills - bills where payment_status is NOT 'paid'
                // This includes 'pending', 'partial', 'overdue', or any other status
                $unpaidBills = $db->table('billing')
                    ->where('patient_id', $patientId)
                    ->where('payment_status !=', 'paid')
                    ->get()
                    ->getResultArray();
                
                if (!empty($unpaidBills)) {
                    $hasUnpaidBills = true;
                    // Calculate total unpaid amount
                    foreach ($unpaidBills as $bill) {
                        $finalAmount = (float)($bill['final_amount'] ?? 0);
                        $totalUnpaid += $finalAmount;
                    }
                    $billCount = count($unpaidBills);
                }
            }
            
            // Check for unbilled charges (items that should be billed but haven't been saved yet)
            // Always check for unbilled charges, regardless of bill payment status
            try {
                $aggregator = new BillingChargeAggregator();
                $result = $aggregator->collect($patientId);
                $allUnbilledItems = $result['items'] ?? [];
                
                if (!empty($allUnbilledItems)) {
                    // Filter out items that are already linked to ANY bills (paid or unpaid)
                    // Items in bills are already billed, so they shouldn't block discharge
                    if ($db->tableExists('billing_items') && $db->tableExists('billing')) {
                        // Get all billing_items that belong to ANY bills for this patient
                        $allBillIds = $db->table('billing')
                            ->select('id')
                            ->where('patient_id', $patientId)
                            ->get()
                            ->getResultArray();
                        
                        $allBillIdList = array_column($allBillIds, 'id');
                        
                        if (!empty($allBillIdList)) {
                            // Get all items linked to any bills (paid or unpaid)
                            $allBillItems = $db->table('billing_items')
                                ->whereIn('billing_id', $allBillIdList)
                                ->get()
                                ->getResultArray();
                            
                            // Build a set of items already in any bills
                            // Normalize source_id to string to ensure consistent comparison
                            $billedItemKeys = [];
                            $hasBilledBedFee = false; // Track if any bed fee is already billed
                            foreach ($allBillItems as $bi) {
                                $key = '';
                                if (!empty($bi['lab_id'])) {
                                    $key = 'lab_' . trim((string)$bi['lab_id']);
                                } elseif (!empty($bi['source_table']) && !empty($bi['source_id'])) {
                                    $key = trim((string)$bi['source_table']) . '_' . trim((string)$bi['source_id']);
                                }
                                if ($key !== '') {
                                    $billedItemKeys[$key] = true;
                                }
                                
                                // Check if this is a bed/room charge
                                $sourceTable = $bi['source_table'] ?? '';
                                $serviceName = strtolower($bi['service'] ?? '');
                                if ($sourceTable === 'admission_details' || $sourceTable === 'patients' || 
                                    stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false) {
                                    $hasBilledBedFee = true;
                                }
                            }
                            
                            // Filter out items that are already in any bills
                            // Normalize source_id to string to ensure consistent comparison
                            $unbilledItems = [];
                            foreach ($allUnbilledItems as $item) {
                                $key = '';
                                if (!empty($item['lab_id'])) {
                                    $key = 'lab_' . trim((string)$item['lab_id']);
                                } elseif (!empty($item['source_table']) && !empty($item['source_id'])) {
                                    $key = trim((string)$item['source_table']) . '_' . trim((string)$item['source_id']);
                                }
                                
                                // Check if this is a bed/room charge
                                $category = $item['category'] ?? 'general';
                                $sourceTable = $item['source_table'] ?? '';
                                $serviceName = strtolower($item['service'] ?? '');
                                $isRoomCharge = ($category === 'room') || 
                                               ($sourceTable === 'admission_details' || $sourceTable === 'patients') ||
                                               (stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false);
                                
                                // If any bed fee is already billed, exclude ALL bed fees from unbilled check
                                if ($isRoomCharge && $hasBilledBedFee) {
                                    continue; // Skip this bed fee - one is already billed
                                }
                                
                                // Only include if not already in any bill
                                if ($key === '' || !isset($billedItemKeys[$key])) {
                                    $unbilledItems[] = $item;
                                }
                            }
                        } else {
                            // No bills at all, so all unbilled items are truly unbilled
                            $unbilledItems = $allUnbilledItems;
                        }
                    } else {
                        // If tables don't exist, use all items (fallback)
                        $unbilledItems = $allUnbilledItems;
                    }
                    
                    // Apply same "one bed fee only" logic as in billing interface
                    // Limit bed/room charges to only one per patient (keep highest amount)
                    if (!empty($unbilledItems)) {
                        $roomItems = [];
                        $otherItems = [];
                        foreach ($unbilledItems as $item) {
                            $category = $item['category'] ?? 'general';
                            $sourceTable = $item['source_table'] ?? '';
                            $serviceName = strtolower($item['service'] ?? '');
                            
                            // Check if it's a room/bed charge
                            $isRoomCharge = false;
                            if ($category === 'room') {
                                $isRoomCharge = true;
                            } elseif ($sourceTable === 'admission_details' || $sourceTable === 'patients') {
                                if (stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false) {
                                    $isRoomCharge = true;
                                }
                            } elseif (stripos($serviceName, 'room') !== false || stripos($serviceName, 'bed') !== false) {
                                $isRoomCharge = true;
                            }
                            
                            if ($isRoomCharge) {
                                $roomItems[] = $item;
                            } else {
                                $otherItems[] = $item;
                            }
                        }
                        
                        // If multiple room charges, keep only the one with the highest amount
                        if (count($roomItems) > 1) {
                            // Sort by amount descending and keep only the first one
                            usort($roomItems, function($a, $b) {
                                $amountA = (float)($a['amount'] ?? 0);
                                $amountB = (float)($b['amount'] ?? 0);
                                return $amountB <=> $amountA; // Descending order
                            });
                            $roomItems = [array_shift($roomItems)]; // Keep only the first (highest amount)
                        }
                        
                        // Combine: room items first (if any), then other items
                        $unbilledItems = array_merge($roomItems, $otherItems);
                    }
                    
                    if (!empty($unbilledItems)) {
                        $hasUnbilledCharges = true;
                        $unbilledItemsCount = count($unbilledItems);
                        foreach ($unbilledItems as $item) {
                            $amount = (float)($item['amount'] ?? 0);
                            $unbilledTotal += $amount;
                        }
                    }
                }
            } catch (\Exception $e) {
                // If aggregator fails, log but don't block discharge
                log_message('error', 'Error checking unbilled charges during discharge: ' . $e->getMessage());
            }
            
            // Block discharge if there are unpaid bills OR unbilled charges
            if ($hasUnpaidBills || $hasUnbilledCharges) {
                $messages = [];
                
                if ($hasUnpaidBills) {
                    $messages[] = sprintf(
                        '%d unpaid bill(s) with a total balance of ₱%s',
                        $billCount,
                        number_format($totalUnpaid, 2)
                    );
                }
                
                if ($hasUnbilledCharges) {
                    $messages[] = sprintf(
                        '%d unbilled charge(s) with a total amount of ₱%s',
                        $unbilledItemsCount,
                        number_format($unbilledTotal, 2)
                    );
                }
                
                $message = 'Cannot discharge patient. Patient has ' . implode(' and ', $messages) . '. Please ensure all bills are paid and all charges are billed before discharging.';
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $message,
                    'unpaid_bills_count' => $billCount,
                    'total_unpaid' => $totalUnpaid,
                    'unbilled_items_count' => $unbilledItemsCount,
                    'unbilled_total' => $unbilledTotal,
                ])->setStatusCode(409);
            }
        }

        $db = db_connect();
        $db->transException(true)->transStart();

        try {
            $this->admissionModel->update($admissionId, [
                'status' => 'discharged',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $patientId = $admission['patient_id'] ?? null;
            if (!empty($patientId)) {
                $this->patientModel->update($patientId, [
                    'type' => 'outpatient',
                    'bed_id' => null,
                ]);
            }

            $bedId = $admission['bed_id'] ?? null;
            if (!empty($bedId)) {
                $this->bedModel->update($bedId, ['status' => 'Available']);
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to discharge patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to discharge patient. Please try again.'
            ])->setStatusCode(500);
        }

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to discharge patient. Please try again.'
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Patient discharged successfully.'
        ]);
    }
}

