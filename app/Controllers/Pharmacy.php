<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\MedicineModel;
use App\Models\PatientModel;

class Pharmacy extends Controller
{
    protected $medicineModel;
    protected $patientModel;
    protected $db;

    public function __construct()
    {
        $this->medicineModel = new MedicineModel();
        $this->patientModel = new PatientModel();
        $this->db = \Config\Database::connect();
    }

    // View Routes
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'pharmacist') {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Pharmacy Dashboard',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('pharmacy/dashboard', $data);
    }

    public function dashboard()
    {
        return $this->index();
    }

    public function medicine()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        // Load the Medicine model
        $medicineModel = new \App\Models\MedicineModel();
        
        // Get all medicines
        $medicines = $medicineModel->findAll();
        
        // Calculate totals
        $total = count($medicines);
        $low_stock = 0;
        $today = date('Y-m-d');
        $threshold = date('Y-m-d', strtotime('+30 days'));
        $expired_count = 0;
        $expiring_soon_count = 0;
        
        foreach ($medicines as $medicine) {
            // Check for low stock
            if (isset($medicine['stock']) && isset($medicine['reorder_level']) && $medicine['stock'] <= $medicine['reorder_level']) {
                $low_stock++;
            }
            
            // Check for expired/expiring medicines
            if (isset($medicine['expiry_date'])) {
                $exp = $medicine['expiry_date'];
                if ($exp < $today) {
                    $expired_count++;
                } elseif ($exp >= $today && $exp <= $threshold) {
                    $expiring_soon_count++;
                }
            }
        }

        $data = [
            'title' => 'Medicine Inventory',
            'user' => session()->get('username'),
            'role' => session()->get('role'),
            'medicines' => $medicines,
            'total' => $total,
            'low_stock' => $low_stock,
            'expired_count' => $expired_count,
            'expiring_soon_count' => $expiring_soon_count
        ];

        return view('Roles/pharmacy/inventory/Medicine', $data);
    }

    public function prescriptionDispensing()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Prescription Dispensing',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Roles/admin/pharmacy/PrescriptionDispencing', $data);
    }

    public function transactions()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Pharmacy Transactions',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Roles/admin/pharmacy/Transaction', $data);
    }

    public function inventory()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Pharmacy Inventory',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('admin/InventoryMan/PharmacyInventory', $data);
    }

    public function viewTransaction($transactionId)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist','admin'])) {
            return redirect()->to('/login')->with('error','Access denied.');
        }

        $builder = $this->db->table('pharmacy_transactions pt');
        $builder->select("pt.id, pt.transaction_number, pt.date, pt.patient_id, pt.total_amount, p.first_name, p.last_name, pr.id as prescription_id, pr.subtotal, pr.tax, pr.total_amount as prescription_total");
        $builder->join('patients p','p.id = pt.patient_id','left');
        $builder->join('prescriptions pr','pr.date = pt.date','left'); // Removed patient_id join
        $builder->where('pt.id', $transactionId);
        $row = $builder->get()->getRowArray();
        if (!$row) return redirect()->back()->with('error','Transaction not found');

        $payload = [
            'id' => (int)$row['id'],
            'transaction_number' => $row['transaction_number'] ?? null,
            'date' => $row['date'] ?? null,
            'patient_id' => $row['patient_id'] ?? null,
            'patient_name' => trim(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')),
            'subtotal' => (float)($row['subtotal'] ?? 0),
            'tax' => (float)($row['tax'] ?? 0),
            'total_amount' => (float)($row['prescription_total'] ?? $row['total_amount'] ?? 0),
            'items' => []
        ];

        $prescriptionId = $row['prescription_id'] ?? null;
        if (empty($prescriptionId)) {
            $latest = $this->db->table('prescriptions')
                ->select('id, subtotal, tax, total_amount')
                ->where('patient_id', $row['patient_id'])
                ->orderBy('date','DESC')->orderBy('id','DESC')
                ->get()->getRowArray();
            if ($latest) {
                $prescriptionId = $latest['id'];
                if (empty($payload['subtotal'])) $payload['subtotal'] = (float)($latest['subtotal'] ?? 0);
                if (empty($payload['tax'])) $payload['tax'] = (float)($latest['tax'] ?? 0);
                if (empty($payload['total_amount'])) $payload['total_amount'] = (float)($latest['total_amount'] ?? 0);
            }
        }

        if (!empty($prescriptionId)) {
            $items = $this->db->table('prescription_items pi')
                ->select('pi.medication_id, pi.quantity, pi.price as unit_price, pi.total as total_price, m.name as medicine_name')
                ->join('medicines m','m.id = pi.medication_id','left')
                ->where('pi.prescription_id', $prescriptionId)
                ->get()->getResultArray();
            $payload['items'] = $items;
        }

        return view('Roles/admin/pharmacy/TransactionDetail', [
            'title' => 'Transaction Details',
            'transactionId' => $transactionId,
            'transaction' => $payload,
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ]);
    }

    public function printTransaction($transactionId)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['pharmacist', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        // Load transaction (no patient connection)
        $builder = $this->db->table('pharmacy_transactions pt');
        $builder->select("pt.id, pt.transaction_number, pt.date, pt.total_amount, pr.id as prescription_id, pr.subtotal, pr.tax, pr.total_amount as prescription_total");
        $builder->join('prescriptions pr', 'pr.date = pt.date', 'left');
        $builder->where('pt.id', $transactionId);
        $row = $builder->get()->getRowArray();

        if (!$row) {
            return redirect()->back()->with('error', 'Transaction not found');
        }

        // Base payload
        $payload = [
            'id' => (int)$row['id'],
            'transaction_number' => $row['transaction_number'] ?? null,
            'date' => $row['date'] ?? null,
            'subtotal' => (float)($row['subtotal'] ?? 0),
            'tax' => (float)($row['tax'] ?? 0),
            'total_amount' => (float)($row['prescription_total'] ?? $row['total_amount'] ?? 0),
            'items' => []
        ];

        // Get prescription by date (no patient connection)
        $prescriptionId = $row['prescription_id'] ?? null;
        if (empty($prescriptionId) && !empty($row['date'])) {
            $latest = $this->db->table('prescriptions')
                ->select('id, subtotal, tax, total_amount')
                ->where('date', $row['date'])
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();
            if ($latest) {
                $prescriptionId = $latest['id'];
                if (empty($payload['subtotal'])) $payload['subtotal'] = (float)($latest['subtotal'] ?? 0);
                if (empty($payload['tax'])) $payload['tax'] = (float)($latest['tax'] ?? 0);
                if (empty($payload['total_amount'])) $payload['total_amount'] = (float)($latest['total_amount'] ?? 0);
            }
        }

        if (!empty($prescriptionId)) {
            $items = $this->db->table('prescription_items pi')
                ->select('pi.medication_id, pi.quantity, pi.price as unit_price, pi.total as total_price, m.name as medicine_name')
                ->join('medicines m', 'm.id = pi.medication_id', 'left')
                ->where('pi.prescription_id', $prescriptionId)
                ->get()
                ->getResultArray();
            $payload['items'] = $items;
        }

        return view('Roles/admin/pharmacy/TransactionPrint', [ 'transaction' => $payload ]);
    }

    // ==================== API ENDPOINTS ====================

    // Get all patients (for autocomplete)
    public function getPatients()
    {
        try {
            $term = trim((string) $this->request->getGet('term'));

            $builder = $this->db->table('patients');
            $builder->select("id, CONCAT(first_name, ' ', last_name) AS name");

            if ($term !== '') {
                $builder->groupStart()
                        ->like('first_name', $term)
                        ->orLike('last_name', $term)
                        ->groupEnd();
            }

            $builder->orderBy('id', 'DESC');
            $builder->limit(15);

            $rows = $builder->get()->getResultArray();

            return $this->response->setJSON($rows);
        } catch (\Throwable $e) {
            // Fail-safe: never break the UI; return an empty list
            return $this->response->setStatusCode(200)->setJSON([]);
        }
    }

    // Get all medications (for autocomplete)
    public function getMedications()
    {
        try {
            $term = (string) $this->request->getGet('term');
            
            // Check if image column exists
            $fields = $this->db->getFieldNames('medicines');
            $hasImageColumn = in_array('image', $fields);
            
            // If column doesn't exist, try to add it automatically (fallback)
            if (!$hasImageColumn) {
                try {
                    $this->db->query("ALTER TABLE medicines ADD COLUMN image VARCHAR(255) NULL AFTER expiry_date");
                    $hasImageColumn = true;
                    // Refresh field list
                    $fields = $this->db->getFieldNames('medicines');
                } catch (\Exception $e) {
                    log_message('error', 'Failed to add image column: ' . $e->getMessage());
                }
            }
            
            $builder = $this->db->table('medicines');
            // Select only columns that exist - check if price column exists for backward compatibility
            $hasPriceColumn = in_array('price', $fields);
            $selectFields = 'id, name, brand, retail_price, unit_price, stock, expiry_date';
            if ($hasPriceColumn) {
                $selectFields .= ', price';
            }
            $builder->select($selectFields);
            if ($hasImageColumn) {
                $builder->select('image', true); // Add image to select
            }
            
            if ($term !== '') {
                $builder->groupStart()
                        ->like('name', $term)
                        ->orLike('brand', $term)
                        ->groupEnd();
            }

            // Exclude only expired medicines (not those expiring soon)
            $today = date('Y-m-d');
            $builder->groupStart()
                    ->where('expiry_date >', $today)
                    ->orWhere('expiry_date IS NULL', null, false)
                    ->groupEnd();
            
            // Only show medicines with stock > 0
            $builder->where('stock >', 0);
            
            $builder->orderBy('id','DESC');
            $builder->limit(50);
            
            $medications = $builder->get()->getResultArray();
        
        // Normalize types and compute days_left until expiry, add image URL
        $today = date('Y-m-d');
        $baseUrl = rtrim(config('App')->baseURL, '/');
        
        foreach ($medications as &$m) {
            // Use retail_price if available, fallback to price for backward compatibility
            if (isset($m['retail_price']) && $m['retail_price'] !== null) {
                $m['price'] = (float)$m['retail_price'];
            } elseif (isset($m['price'])) {
                $m['price'] = (float)$m['price'];
            } else {
                $m['price'] = 0.00;
            }
            if (isset($m['stock'])) $m['stock'] = (int)$m['stock'];
            $m['expiry_date'] = $m['expiry_date'] ?? null;
            if (!empty($m['expiry_date'])) {
                $diff = (strtotime($m['expiry_date']) - strtotime($today)) / 86400;
                $m['days_left'] = (int)ceil($diff);
            } else {
                $m['days_left'] = null;
            }
            
            // Handle image and image_url fields - ALWAYS include these fields
            // Initialize image_url to null
            $m['image_url'] = null;
            
            // Only process if image column exists
            if ($hasImageColumn) {
                // Get the image value from database (don't overwrite it yet)
                $imageValue = $m['image'] ?? null;
                
                // Process image if it exists and is valid
                if (!empty($imageValue) && is_string($imageValue) && trim($imageValue) !== '') {
                    $imageFilename = trim($imageValue);
                    // Check if file exists
                    $imagePath = FCPATH . 'uploads/medicines/' . $imageFilename;
                    if (file_exists($imagePath)) {
                        // Construct the full URL
                        $imageUrl = $baseUrl . '/uploads/medicines/' . $imageFilename;
                        $m['image_url'] = $imageUrl;
                        // Keep the image filename
                        $m['image'] = $imageFilename;
                    } else {
                        // Keep the image value even if file doesn't exist (might be a path issue)
                        $m['image'] = $imageFilename;
                    }
                } else {
                    // No image value in database
                    $m['image'] = null;
                }
            } else {
                // Image column doesn't exist
                $m['image'] = null;
            }
        }
        
        return $this->response->setJSON($medications);
        } catch (\Exception $e) {
            log_message('error', 'Error in getMedications: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Failed to load medications',
                'message' => $e->getMessage()
            ]);
        }
    }

    // Get medication by ID
    public function getMedication($id)
    {
        $medicine = $this->medicineModel->find($id);
        
        if (!$medicine) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Medication not found'
            ])->setStatusCode(404);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $medicine
        ]);
    }

    // Expiring soon medicines within N days (default 30)
    public function getExpiringMedicines()
    {
        $days = (int)($this->request->getGet('days') ?? 30);
        if ($days <= 0) { $days = 30; }

        $today = date('Y-m-d');
        $limit = date('Y-m-d', strtotime("+{$days} days"));

        $rows = $this->db->table('medicines')
            ->select('id, name, brand, category, stock, price, expiry_date')
            ->where('expiry_date >=', $today)
            ->where('expiry_date <=', $limit)
            ->orderBy('expiry_date', 'ASC')
            ->get()->getResultArray();

        $list = array_map(function($r) use ($today) {
            $diff = (strtotime($r['expiry_date']) - strtotime($today)) / 86400;
            $r['days_left'] = (int)ceil($diff);
            $r['price'] = isset($r['price']) ? (float)$r['price'] : 0.0;
            $r['stock'] = isset($r['stock']) ? (int)$r['stock'] : 0;
            $r['id'] = (int)$r['id'];
            return $r;
        }, $rows);

        return $this->response->setJSON([
            'success' => true,
            'data' => $list,
            'days' => $days
        ]);
    }

    // Already expired medicines as of today
    public function getExpiredMedicines()
    {
        $today = date('Y-m-d');
        $rows = $this->db->table('medicines')
            ->select('id, name, brand, category, stock, price, expiry_date')
            ->where('expiry_date <', $today)
            ->orderBy('expiry_date', 'ASC')
            ->get()->getResultArray();

        $list = array_map(function($r) use ($today) {
            $diff = (strtotime($today) - strtotime($r['expiry_date'])) / 86400;
            $r['days_overdue'] = (int)floor($diff);
            $r['price'] = isset($r['price']) ? (float)$r['price'] : 0.0;
            $r['stock'] = isset($r['stock']) ? (int)$r['stock'] : 0;
            $r['id'] = (int)$r['id'];
            return $r;
        }, $rows);

        return $this->response->setJSON([
            'success' => true,
            'data' => $list
        ]);
    }

    // Create transaction (checkout)
    public function createTransaction()
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'patient_id' => 'permit_empty',
            'patient_name' => 'permit_empty',
            'date' => 'required',
            'payment_method' => 'required',
            'items' => 'required'
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validation->getErrors()
            ])->setStatusCode(400);
        }
        
        $data = $this->request->getJSON(true);

        // Patient is optional - set to null if not provided
        $pid = trim((string)($data['patient_id'] ?? ''));
        if ($pid === '') {
            $data['patient_id'] = null;
        }

        // Validate stock availability and expiry BEFORE starting transaction
        $itemIds = array_map(fn($it) => (string)$it['medicine_id'], $data['items']);
        $uniqueIds = array_values(array_unique($itemIds));
        $info = [];
        if (!empty($uniqueIds)) {
            $rows = $this->db->table('medicines')
                ->select('id, stock, name, expiry_date')
                ->whereIn('id', $uniqueIds)
                ->get()->getResultArray();
            foreach ($rows as $r) {
                $info[(string)$r['id']] = [
                    'stock' => (int)($r['stock'] ?? 0),
                    'name' => $r['name'] ?? (string)$r['id'],
                    'expiry_date' => $r['expiry_date'] ?? null,
                ];
            }
        }
        $insufficient = [];
        $expiryViolations = [];
        $requestedPerMed = [];
        foreach ($data['items'] as $it) {
            $mid = (string)$it['medicine_id'];
            $qty = (int)$it['quantity'];
            if (!isset($requestedPerMed[$mid])) $requestedPerMed[$mid] = 0;
            $requestedPerMed[$mid] += $qty;
        }
        foreach ($requestedPerMed as $mid => $qty) {
            $available = isset($info[$mid]) ? (int)$info[$mid]['stock'] : 0;
            if ($qty <= 0 || $available <= 0 || $qty > $available) {
                $insufficient[] = [
                    'medicine_id' => $mid,
                    'medicine_name' => $info[$mid]['name'] ?? (string)$mid,
                    'requested' => $qty,
                    'available' => $available
                ];
            }
            // Expiry rule: cannot dispense if expired or expiring within 30 days
            $today = date('Y-m-d');
            $limit = date('Y-m-d', strtotime('+30 days'));
            $exp = $info[$mid]['expiry_date'] ?? null;
            if ($exp && $exp <= $limit) {
                $expiryViolations[] = [
                    'medicine_id' => $mid,
                    'medicine_name' => $info[$mid]['name'] ?? (string)$mid,
                    'expiry_date' => $exp,
                ];
            }
        }
        if (!empty($insufficient)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Insufficient stock for one or more medicines',
                'details' => $insufficient
            ])->setStatusCode(400);
        }
        if (!empty($expiryViolations)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot dispense medicines that are expired or expiring within 30 days',
                'details' => $expiryViolations
            ])->setStatusCode(400);
        }
        
        try {
            $this->db->transStart();
            
            // Use totals from request if provided, otherwise calculate
            if (isset($data['subtotal']) && isset($data['tax']) && isset($data['total'])) {
                $subtotal = (float)($data['subtotal'] ?? 0);
                $tax = (float)($data['tax'] ?? 0);
                $total = (float)($data['total'] ?? 0);
            } else {
                // Calculate totals (fallback if not provided)
                $subtotal = 0;
                foreach ($data['items'] as $item) { $subtotal += $item['price'] * $item['quantity']; }
                $tax = $subtotal * 0.12;
                $total = $subtotal + $tax;
            }

            // Insert prescription
            $prescriptionData = [
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $total,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            $this->db->table('prescriptions')->insert($prescriptionData);
            $prescriptionId = $this->db->insertID();

            // Insert items (stock was already decreased when items were added to cart)
            $totalItems = 0;
            foreach ($data['items'] as $item) {
                $lineTotal = $item['price'] * $item['quantity'];
                $this->db->table('prescription_items')->insert([
                    'prescription_id' => $prescriptionId,
                    'medication_id' => (string)$item['medicine_id'],
                    'quantity' => (int)$item['quantity'],
                    'price' => $item['price'],
                    'total' => $lineTotal,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $totalItems += (int)$item['quantity'];
                // Note: Stock was already decreased when items were added to cart via reserveStock API
                // No need to decrease again here
            }

            // Transaction number and record
            $transactionNumber = $this->generateTransactionNumber();
            $transactionData = [
                'transaction_number' => $transactionNumber,
                'date' => $data['date'],
                'total_items' => $totalItems,
                'total_amount' => $total,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Only add patient_id if it's not null/empty
            if (!empty($data['patient_id'])) {
                $transactionData['patient_id'] = (string)$data['patient_id'];
            }
            
            $this->db->table('pharmacy_transactions')->insert($transactionData);
            $transactionId = $this->db->insertID();
            
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction failed'
                ])->setStatusCode(500);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transaction created successfully',
                'transaction_number' => $transactionNumber,
                'transaction_id' => $transactionId
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error creating transaction: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    // Get all transactions
    public function getAllTransactions()
    {
        $search = $this->request->getGet('search');
        
        $builder = $this->db->table('pharmacy_transactions pt');
        $builder->select("pt.id, pt.transaction_number, pt.date, pt.total_amount, pt.total_items as items_count, pt.date as transaction_date, pt.created_at");
        
        if ($search) {
            $builder->like('pt.transaction_number', $search);
        }
        
        $builder->orderBy('pt.created_at', 'DESC');
        
        $transactions = $builder->get()->getResultArray();
        
        // Calculate actual total from items (without tax) for each transaction
        foreach ($transactions as &$trx) {
            // Get transaction's created_at timestamp
            $trxCreatedAt = $trx['created_at'] ?? null;
            
            $calculatedTotal = 0;
            
            if ($trxCreatedAt) {
                // Find prescription created just before or at the same time as this transaction
                // Prescriptions are created before transactions in the same transaction flow
                $prescription = $this->db->table('prescriptions pr')
                    ->select('pr.id as prescription_id')
                    ->where('pr.date', $trx['transaction_date'])
                    ->where('pr.created_at <=', $trxCreatedAt)
                    ->orderBy('pr.created_at', 'DESC')
                    ->orderBy('pr.id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if (!empty($prescription['prescription_id'])) {
                    // Sum all item totals for this prescription
                    $itemSum = $this->db->table('prescription_items')
                        ->selectSum('total', 'item_total')
                        ->where('prescription_id', $prescription['prescription_id'])
                        ->get()
                        ->getRowArray();
                    $calculatedTotal = (float)($itemSum['item_total'] ?? 0);
                }
            }
            
            // If no match by timestamp, try matching by date and order (fallback)
            if ($calculatedTotal == 0) {
                // Get all prescriptions for this date, ordered by creation
                $allPrescriptions = $this->db->table('prescriptions pr')
                    ->select('pr.id as prescription_id, pr.created_at')
                    ->where('pr.date', $trx['transaction_date'])
                    ->orderBy('pr.created_at', 'ASC')
                    ->orderBy('pr.id', 'ASC')
                    ->get()
                    ->getResultArray();
                
                // Get all transactions for this date, ordered by creation
                $allTransactions = $this->db->table('pharmacy_transactions')
                    ->select('id, created_at')
                    ->where('date', $trx['transaction_date'])
                    ->orderBy('created_at', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();
                
                // Find this transaction's index
                $trxIndex = -1;
                foreach ($allTransactions as $idx => $t) {
                    if ($t['id'] == $trx['id']) {
                        $trxIndex = $idx;
                        break;
                    }
                }
                
                // Match by index (prescription and transaction created in same order)
                if ($trxIndex >= 0 && isset($allPrescriptions[$trxIndex])) {
                    $prescriptionId = $allPrescriptions[$trxIndex]['prescription_id'];
                    $itemSum = $this->db->table('prescription_items')
                        ->selectSum('total', 'item_total')
                        ->where('prescription_id', $prescriptionId)
                        ->get()
                        ->getRowArray();
                    $calculatedTotal = (float)($itemSum['item_total'] ?? 0);
                }
            }
            
            // Use calculated total from items if available, otherwise use stored total
            $trx['total_amount'] = $calculatedTotal > 0 ? $calculatedTotal : (float)($trx['total_amount'] ?? 0);
        }
        unset($trx);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $transactions
        ]);
    }

    // Get transaction details
    public function getTransactionDetails($transactionId)
    {
        // Load transaction
        $builder = $this->db->table('pharmacy_transactions pt');
        $builder->select("pt.id, pt.transaction_number, pt.date, pt.total_amount, pt.created_at as transaction_created_at");
        $builder->where('pt.id', $transactionId);
        $row = $builder->get()->getRowArray();

        if (!$row) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Transaction not found'
            ])->setStatusCode(404);
        }

        // Find the correct prescription for this transaction
        $prescriptionId = null;
        $trxCreatedAt = $row['transaction_created_at'] ?? null;
        
        if ($trxCreatedAt && !empty($row['date'])) {
            // Try matching by timestamp first
            $prescription = $this->db->table('prescriptions pr')
                ->select('pr.id as prescription_id, pr.subtotal, pr.tax, pr.total_amount')
                ->where('pr.date', $row['date'])
                ->where('pr.created_at <=', $trxCreatedAt)
                ->orderBy('pr.created_at', 'DESC')
                ->orderBy('pr.id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();
            
            if (!empty($prescription['prescription_id'])) {
                $prescriptionId = $prescription['prescription_id'];
            }
        }
        
        // Fallback: match by creation order if timestamp matching didn't work
        if (empty($prescriptionId) && !empty($row['date'])) {
            $allPrescriptions = $this->db->table('prescriptions')
                ->select('id, created_at')
                ->where('date', $row['date'])
                ->orderBy('created_at', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
            
            $allTransactions = $this->db->table('pharmacy_transactions')
                ->select('id, created_at')
                ->where('date', $row['date'])
                ->orderBy('created_at', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();
            
            $trxIndex = -1;
            foreach ($allTransactions as $idx => $t) {
                if ($t['id'] == $transactionId) {
                    $trxIndex = $idx;
                    break;
                }
            }
            
            if ($trxIndex >= 0 && isset($allPrescriptions[$trxIndex])) {
                $prescriptionId = $allPrescriptions[$trxIndex]['id'];
            }
        }

        // Prepare base payload
        $payload = [
            'id' => (int)$row['id'],
            'transaction_number' => $row['transaction_number'] ?? null,
            'date' => $row['date'] ?? null,
            'subtotal' => 0,
            'tax' => 0,
            'total_amount' => 0,
            'items' => []
        ];
        
        // Get prescription details if found
        if (!empty($prescriptionId)) {
            $prescription = $this->db->table('prescriptions')
                ->select('subtotal, tax, total_amount')
                ->where('id', $prescriptionId)
                ->get()
                ->getRowArray();
            
            if ($prescription) {
                $payload['subtotal'] = (float)($prescription['subtotal'] ?? 0);
                $payload['tax'] = (float)($prescription['tax'] ?? 0);
                $payload['total_amount'] = (float)($prescription['total_amount'] ?? 0);
            }
        }

        if (!empty($prescriptionId)) {
            $items = $this->db->table('prescription_items pi')
                ->select('pi.medication_id, pi.quantity, pi.price as unit_price, pi.total as total_price, m.name as medicine_name')
                ->join('medicines m', 'm.id = pi.medication_id', 'left')
                ->where('pi.prescription_id', $prescriptionId)
                ->get()
                ->getResultArray();
            $payload['items'] = array_map(function($it){
                return [
                    'medicine_id' => (string)$it['medication_id'],
                    'medicine_name' => $it['medicine_name'] ?? $it['medication_id'],
                    'quantity' => (int)$it['quantity'],
                    'unit_price' => (float)$it['unit_price'],
                    'total_price' => (float)$it['total_price'],
                ];
            }, $items);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $payload
        ]);
    }

    // Get transaction statistics
    public function getStats()
    {
        $today = date('Y-m-d');
        
        // Total transactions
        $totalTransactions = $this->db->table('pharmacy_transactions')
            ->countAll();
        
        // Today's transactions
        $todayTransactions = $this->db->table('pharmacy_transactions')
            ->where('DATE(date)', $today)
            ->countAllResults();
        
        // Today's revenue
        $todayRevenue = $this->db->table('pharmacy_transactions')
            ->selectSum('total_amount')
            ->where('DATE(date)', $today)
            ->where('status', 'completed')
            ->get()
            ->getRow()
            ->total_amount ?? 0;
        
        // Low stock medicines
        $lowStock = $this->db->table('medicines')
            ->where('stock <=', 10)
            ->countAllResults();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'total_transactions' => $totalTransactions,
                'today_transactions' => $todayTransactions,
                'today_revenue' => number_format($todayRevenue, 2),
                'low_stock_count' => $lowStock
            ]
        ]);
    }

    // Reserve stock when adding to cart
    public function reserveStock()
    {
        $data = $this->request->getJSON(true);
        
        if (!isset($data['medicine_id']) || !isset($data['quantity'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields: medicine_id and quantity'
            ])->setStatusCode(400);
        }
        
        $medicineId = (string)$data['medicine_id'];
        $quantity = (int)$data['quantity'];
        
        if ($quantity <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Quantity must be greater than 0'
            ])->setStatusCode(400);
        }
        
        try {
            // Check current stock
            $medicine = $this->db->table('medicines')
                ->select('id, stock, name')
                ->where('id', $medicineId)
                ->get()
                ->getRowArray();
            
            if (!$medicine) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Medicine not found'
                ])->setStatusCode(404);
            }
            
            $currentStock = (int)($medicine['stock'] ?? 0);
            
            if ($currentStock < $quantity) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Insufficient stock. Available: ' . $currentStock,
                    'available_stock' => $currentStock
                ])->setStatusCode(400);
            }
            
            // Decrease stock
            $this->db->table('medicines')
                ->set('stock', 'stock - ' . $quantity, false)
                ->where('id', $medicineId)
                ->where('stock >=', $quantity)
                ->update();
            
            if ($this->db->affectedRows() === 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to reserve stock. Stock may have changed.'
                ])->setStatusCode(400);
            }
            
            // Get updated stock
            $updated = $this->db->table('medicines')
                ->select('stock')
                ->where('id', $medicineId)
                ->get()
                ->getRowArray();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Stock reserved successfully',
                'remaining_stock' => (int)($updated['stock'] ?? 0)
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error reserving stock: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
    
    // Restore stock when removing from cart
    public function restoreStock()
    {
        $data = $this->request->getJSON(true);
        
        if (!isset($data['medicine_id']) || !isset($data['quantity'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields: medicine_id and quantity'
            ])->setStatusCode(400);
        }
        
        $medicineId = (string)$data['medicine_id'];
        $quantity = (int)$data['quantity'];
        
        if ($quantity <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Quantity must be greater than 0'
            ])->setStatusCode(400);
        }
        
        try {
            // Check if medicine exists
            $medicine = $this->db->table('medicines')
                ->select('id, stock, name')
                ->where('id', $medicineId)
                ->get()
                ->getRowArray();
            
            if (!$medicine) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Medicine not found'
                ])->setStatusCode(404);
            }
            
            // Increase stock
            $this->db->table('medicines')
                ->set('stock', 'stock + ' . $quantity, false)
                ->where('id', $medicineId)
                ->update();
            
            // Get updated stock
            $updated = $this->db->table('medicines')
                ->select('stock')
                ->where('id', $medicineId)
                ->get()
                ->getRowArray();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Stock restored successfully',
                'remaining_stock' => (int)($updated['stock'] ?? 0)
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error restoring stock: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    // Helper function to generate transaction number
    private function generateTransactionNumber()
    {
        $prefix = 'TRX-';
        $lastTransaction = $this->db->table('pharmacy_transactions')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
        
        if ($lastTransaction && isset($lastTransaction['transaction_number'])) {
            $lastNumber = intval(substr($lastTransaction['transaction_number'], 4));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . $newNumber;
    }
}
