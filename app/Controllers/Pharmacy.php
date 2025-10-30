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

        $data = [
            'title' => 'Medicine Inventory',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Roles/admin/inventory/Medicine', $data);
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
        $builder->join('prescriptions pr','pr.patient_id = pt.patient_id AND pr.date = pt.date','left');
        $builder->where('pt.id', $transactionId);
        $row = $builder->get()->getRowArray();
        if (!$row) return redirect()->back()->with('error','Transaction not found');

        $payload = [
            'id' => (int)$row['id'],
            'transaction_number' => $row['transaction_number'] ?? null,
            'date' => $row['date'] ?? null,
            'patient_id' => (int)($row['patient_id'] ?? 0),
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
                ->where('patient_id', (int)$row['patient_id'])
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

        // Load transaction + patient
        $builder = $this->db->table('pharmacy_transactions pt');
        $builder->select("pt.id, pt.transaction_number, pt.date, pt.patient_id, pt.total_amount, p.first_name, p.last_name, pr.id as prescription_id, pr.subtotal, pr.tax, pr.total_amount as prescription_total");
        $builder->join('patients p', 'p.id = pt.patient_id', 'left');
        $builder->join('prescriptions pr', 'pr.patient_id = pt.patient_id AND pr.date = pt.date', 'left');
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
            'patient_id' => (int)($row['patient_id'] ?? 0),
            'patient_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            'subtotal' => (float)($row['subtotal'] ?? 0),
            'tax' => (float)($row['tax'] ?? 0),
            'total_amount' => (float)($row['prescription_total'] ?? $row['total_amount'] ?? 0),
            'items' => []
        ];

        // Determine prescription (fallback to latest for patient if needed)
        $prescriptionId = $row['prescription_id'] ?? null;
        if (empty($prescriptionId)) {
            $latest = $this->db->table('prescriptions')
                ->select('id, subtotal, tax, total_amount')
                ->where('patient_id', (int)$row['patient_id'])
                ->orderBy('date', 'DESC')
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
        $term = (string) $this->request->getGet('term');
        
        $builder = $this->db->table('medicines');
        $builder->select('id, name, brand, price, stock');
        
        if ($term !== '') {
            $builder->groupStart()
                    ->like('name', $term)
                    ->orLike('brand', $term)
                    ->groupEnd();
        }
        
        // Show even if stock is 0 so user understands availability
        $builder->orderBy('id','DESC');
        $builder->limit(50);
        
        $medications = $builder->get()->getResultArray();
        
        // Normalize types
        foreach ($medications as &$m) {
            if (isset($m['price'])) $m['price'] = (float)$m['price'];
            if (isset($m['stock'])) $m['stock'] = (int)$m['stock'];
            if (isset($m['id'])) $m['id'] = (int)$m['id'];
        }
        
        return $this->response->setJSON($medications);
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
            'patient_id' => 'required|numeric',
            'patient_name' => 'required',
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

        // Validate stock availability BEFORE starting transaction
        $itemIds = array_map(fn($it) => (int)$it['medicine_id'], $data['items']);
        $uniqueIds = array_values(array_unique($itemIds));
        $stocks = [];
        if (!empty($uniqueIds)) {
            $rows = $this->db->table('medicines')
                ->select('id, stock, name')
                ->whereIn('id', $uniqueIds)
                ->get()->getResultArray();
            foreach ($rows as $r) { $stocks[(int)$r['id']] = ['stock' => (int)$r['stock'], 'name' => $r['name']]; }
        }
        $insufficient = [];
        $requestedPerMed = [];
        foreach ($data['items'] as $it) {
            $mid = (int)$it['medicine_id'];
            $qty = (int)$it['quantity'];
            if (!isset($requestedPerMed[$mid])) $requestedPerMed[$mid] = 0;
            $requestedPerMed[$mid] += $qty;
        }
        foreach ($requestedPerMed as $mid => $qty) {
            $available = isset($stocks[$mid]) ? (int)$stocks[$mid]['stock'] : 0;
            if ($qty <= 0 || $available <= 0 || $qty > $available) {
                $insufficient[] = [
                    'medicine_id' => $mid,
                    'medicine_name' => $stocks[$mid]['name'] ?? (string)$mid,
                    'requested' => $qty,
                    'available' => $available
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
        
        try {
            $this->db->transStart();
            
            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) { $subtotal += $item['price'] * $item['quantity']; }
            $tax = $subtotal * 0.12;
            $total = $subtotal + $tax;

            // Insert prescription
            $this->db->table('prescriptions')->insert([
                'patient_id' => (int)$data['patient_id'],
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $total,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $prescriptionId = $this->db->insertID();

            // Insert items and update stock
            $totalItems = 0;
            foreach ($data['items'] as $item) {
                $lineTotal = $item['price'] * $item['quantity'];
                $this->db->table('prescription_items')->insert([
                    'prescription_id' => $prescriptionId,
                    'medication_id' => (int)$item['medicine_id'],
                    'quantity' => (int)$item['quantity'],
                    'price' => $item['price'],
                    'total' => $lineTotal,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $totalItems += (int)$item['quantity'];
                // Safe stock deduction: never allow negative stock
                $deductQty = (int)$item['quantity'];
                $medId = (int)$item['medicine_id'];
                $this->db->table('medicines')
                    ->set('stock', 'stock - ' . $deductQty, false)
                    ->where('id', $medId)
                    ->where('stock >=', $deductQty)
                    ->update();
                if ($this->db->affectedRows() === 0) {
                    throw new \RuntimeException('Stock update failed for medicine ID ' . $medId . ' due to insufficient stock.');
                }
            }

            // Transaction number and record
            $transactionNumber = $this->generateTransactionNumber();
            $this->db->table('pharmacy_transactions')->insert([
                'transaction_number' => $transactionNumber,
                'patient_id' => (int)$data['patient_id'],
                'date' => $data['date'],
                'total_items' => $totalItems,
                'total_amount' => $total,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
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
        $builder->select("pt.id, pt.transaction_number, pt.date, pt.total_amount, COUNT(pi.id) as items_count, CONCAT(p.first_name, ' ', p.last_name) as patient_name");
        $builder->join('prescriptions pr', 'pr.patient_id = pt.patient_id AND pr.date = pt.date', 'left');
        $builder->join('prescription_items pi', 'pi.prescription_id = pr.id', 'left');
        $builder->join('patients p', 'p.id = pt.patient_id', 'left');
        
        if ($search) {
            $builder->groupStart();
            $builder->like('pt.transaction_number', $search);
            $builder->orLike("CONCAT(p.first_name, ' ', p.last_name)", $search, 'both', null, true);
            $builder->groupEnd();
        }
        
        $builder->groupBy('pt.id');
        $builder->orderBy('pt.created_at', 'DESC');
        
        $transactions = $builder->get()->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $transactions
        ]);
    }

    // Get transaction details
    public function getTransactionDetails($transactionId)
    {
        // Load transaction joined with patient and matching prescription (by patient and date)
        $builder = $this->db->table('pharmacy_transactions pt');
        $builder->select("pt.id, pt.transaction_number, pt.date, pt.patient_id, pt.total_amount, p.first_name, p.last_name, pr.id as prescription_id, pr.subtotal, pr.tax, pr.total_amount as prescription_total");
        $builder->join('patients p', 'p.id = pt.patient_id', 'left');
        $builder->join('prescriptions pr', 'pr.patient_id = pt.patient_id AND pr.date = pt.date', 'left');
        $builder->where('pt.id', $transactionId);
        $row = $builder->get()->getRowArray();

        if (!$row) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Transaction not found'
            ])->setStatusCode(404);
        }

        // Prepare base payload
        $payload = [
            'id' => (int)$row['id'],
            'transaction_number' => $row['transaction_number'] ?? null,
            'date' => $row['date'] ?? null,
            'patient_id' => (int)($row['patient_id'] ?? 0),
            'patient_name' => trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')),
            'subtotal' => (float)($row['subtotal'] ?? 0),
            'tax' => (float)($row['tax'] ?? 0),
            'total_amount' => (float)($row['prescription_total'] ?? $row['total_amount'] ?? 0),
            'items' => []
        ];

        // Load items from prescription_items. If no prescription matched by date, fallback to latest for the patient.
        $prescriptionId = $row['prescription_id'] ?? null;
        if (empty($prescriptionId)) {
            $latest = $this->db->table('prescriptions')
                ->select('id, subtotal, tax, total_amount')
                ->where('patient_id', (int)$row['patient_id'])
                ->orderBy('date', 'DESC')
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();
            if ($latest) {
                $prescriptionId = $latest['id'];
                // If totals were missing on the primary row, use latest
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
            $payload['items'] = array_map(function($it){
                return [
                    'medicine_id' => (int)$it['medication_id'],
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
