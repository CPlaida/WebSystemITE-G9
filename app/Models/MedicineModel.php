<?php namespace App\Models;

use CodeIgniter\Model;

class MedicineModel extends Model
{
    protected $table = 'medicines';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'id', 'barcode', 'name', 'brand', 'category', 'stock', 'unit_price', 'retail_price', 'manufactured_date', 'expiry_date', 'description'
    ];
    
    // Low stock threshold (can be configured)
    protected $lowStockThreshold = 5;
    
    // Dynamically check if image column exists and add it to allowedFields
    protected function initialize()
    {
        parent::initialize();
        try {
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames($this->table);
            if (in_array('image', $fields)) {
                $this->allowedFields[] = 'image';
            }
            // Dynamically add price field if it exists (for backward compatibility)
            if (in_array('price', $fields)) {
                $this->allowedFields[] = 'price';
            }
            // Check and add status column if it doesn't exist
            if (!in_array('status', $fields)) {
                // Add status column with expired_soon
                $db->query("ALTER TABLE `{$this->table}` ADD COLUMN `status` ENUM('available', 'low_stock', 'out_of_stock', 'expired_soon') DEFAULT 'available' AFTER `stock`");
                // Sync all existing statuses
                $this->syncAllStatuses();
            } else {
                // Update existing status column to include 'expired_soon' if missing
                try {
                    $columnInfo = $db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'status'")->getRowArray();
                    if ($columnInfo && isset($columnInfo['Type'])) {
                        $currentType = $columnInfo['Type'];
                        // Check if expired_soon is not in the enum
                        if (strpos($currentType, 'expired_soon') === false) {
                            // Update enum to include expired_soon
                            $db->query("ALTER TABLE `{$this->table}` MODIFY COLUMN `status` ENUM('available', 'low_stock', 'out_of_stock', 'expired_soon') DEFAULT 'available'");
                            // Sync all statuses after adding the new enum value
                            $this->syncAllStatuses();
                        }
                    }
                } catch (\Exception $e) {
                    // If update fails, continue without expired_soon
                }
            }
            // Dynamically add status field to allowedFields
            if (in_array('status', $fields)) {
                $this->allowedFields[] = 'status';
            }
        } catch (\Exception $e) {
            // If table doesn't exist or error, just continue without status field
        }
    }

    protected $beforeInsert = ['generateId', 'validateExpiryDate', 'updateStatus'];
    protected $beforeUpdate = ['validateExpiryDate', 'updateStatus'];
    protected $afterUpdate = ['syncStatusAfterUpdate'];

    protected function generateId(array $data)
    {
        if (!empty($data['data']['id'])) return $data;
        $db = \Config\Database::connect();
        $row = $db->table($this->table)
            ->select('id')
            ->like('id', 'MED-', 'after')
            ->orderBy('id', 'DESC')
            ->get(1)->getRowArray();
        $next = 1;
        if ($row && isset($row['id'])) {
            $num = (int)substr($row['id'], 4);
            if ($num > 0) $next = $num + 1;
        }
        $data['data']['id'] = 'MED-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
        return $data;
    }

    /**
     * Validate expiration date - prevent expired dates and dates less than 3 months from being saved
     */
    protected function validateExpiryDate(array $data)
    {
        $expiryDate = isset($data['data']['expiry_date']) ? $data['data']['expiry_date'] : null;
        
        // Only validate if expiry_date is provided and not empty
        if (!empty($expiryDate)) {
            $today = date('Y-m-d');
            $threeMonthsFromNow = date('Y-m-d', strtotime('+3 months'));
            
            // If expiry date is in the past, throw an exception
            if ($expiryDate < $today) {
                throw new \RuntimeException('Cannot add or update medicine. This batch is already expired.');
            }
            
            // If expiry date is less than 3 months from today, throw an exception
            if ($expiryDate < $threeMonthsFromNow) {
                throw new \RuntimeException('Cannot add or update medicine. Expiration date must be at least 3 months from today. Minimum expiry date: ' . date('Y-m-d', strtotime('+3 months')));
            }
        }
        
        return $data;
    }

    /**
     * Automatically update status based on stock level and expiration date
     */
    protected function updateStatus(array $data)
    {
        // Check if status column exists
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($this->table);
        if (!in_array('status', $fields)) {
            return $data; // Status column doesn't exist, skip
        }

        $stock = isset($data['data']['stock']) ? (int)$data['data']['stock'] : null;
        $expiryDate = isset($data['data']['expiry_date']) ? $data['data']['expiry_date'] : null;
        
        // If stock/expiry is not being updated, get current values from database
        if (isset($data['id'][0])) {
            $existing = $this->find($data['id'][0]);
            if ($existing) {
                if ($stock === null) {
                    $stock = (int)($existing['stock'] ?? 0);
                }
                if ($expiryDate === null) {
                    $expiryDate = $existing['expiry_date'] ?? null;
                }
            } else {
                $stock = $stock ?? 0;
            }
        } elseif ($stock === null) {
            return $data; // No stock info available
        }

        // Check expiration date first (highest priority)
        $status = null;
        if (!empty($expiryDate)) {
            $expiry = new \DateTime($expiryDate);
            $today = new \DateTime();
            $cutoff = clone $today;
            $cutoff->modify('+3 months');
            
            // If expiry is within 3 months, set to expired_soon
            if ($expiry <= $cutoff) {
                $status = 'expired_soon';
            }
        }
        
        // If not expired_soon, determine status based on stock
        if ($status === null) {
            if ($stock <= 0) {
                $status = 'out_of_stock';
            } elseif ($stock <= $this->lowStockThreshold) {
                $status = 'low_stock';
            } else {
                $status = 'available';
            }
        }

        $data['data']['status'] = $status;
        return $data;
    }

    /**
     * Sync status after update (for cases where stock is updated via raw SQL)
     */
    protected function syncStatusAfterUpdate(array $data)
    {
        // Check if status column exists
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($this->table);
        if (!in_array('status', $fields)) {
            return $data; // Status column doesn't exist, skip
        }

        // If stock or expiry was updated, recalculate status
        if (isset($data['id'])) {
            $id = is_array($data['id']) ? $data['id'][0] : $data['id'];
            
            // Get current medicine data
            $medicine = $this->find($id);
            if (!$medicine) {
                return $data;
            }
            
            $stock = isset($data['data']['stock']) ? (int)$data['data']['stock'] : (int)($medicine['stock'] ?? 0);
            $expiryDate = isset($data['data']['expiry_date']) ? $data['data']['expiry_date'] : ($medicine['expiry_date'] ?? null);
            
            // Check expiration date first (highest priority)
            $status = null;
            if (!empty($expiryDate)) {
                $expiry = new \DateTime($expiryDate);
                $today = new \DateTime();
                $cutoff = clone $today;
                $cutoff->modify('+3 months');
                
                // If expiry is within 3 months, set to expired_soon
                if ($expiry <= $cutoff) {
                    $status = 'expired_soon';
                }
            }
            
            // If not expired_soon, determine status based on stock
            if ($status === null) {
                if ($stock <= 0) {
                    $status = 'out_of_stock';
                } elseif ($stock <= $this->lowStockThreshold) {
                    $status = 'low_stock';
                } else {
                    $status = 'available';
                }
            }

            // Update status directly in database
            $db->table($this->table)
                ->where('id', $id)
                ->update(['status' => $status]);
        }

        return $data;
    }

    /**
     * Sync status for all medicines (useful for migration or bulk updates)
     */
    public function syncAllStatuses()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames($this->table);
        if (!in_array('status', $fields)) {
            return false; // Status column doesn't exist
        }

        $medicines = $this->findAll();
        $today = new \DateTime();
        $cutoff = clone $today;
        $cutoff->modify('+3 months');
        
        foreach ($medicines as $medicine) {
            $stock = (int)($medicine['stock'] ?? 0);
            $expiryDate = $medicine['expiry_date'] ?? null;
            
            // Check expiration date first (highest priority)
            $status = null;
            if (!empty($expiryDate)) {
                $expiry = new \DateTime($expiryDate);
                if ($expiry <= $cutoff) {
                    $status = 'expired_soon';
                }
            }
            
            // If not expired_soon, determine status based on stock
            if ($status === null) {
                if ($stock <= 0) {
                    $status = 'out_of_stock';
                } elseif ($stock <= $this->lowStockThreshold) {
                    $status = 'low_stock';
                } else {
                    $status = 'available';
                }
            }

            $this->update($medicine['id'], ['status' => $status]);
        }

        return true;
    }

    /**
     * Get expense report
     * Note: Expenses are calculated from prescription_items (cost of medicines sold)
     */
    public function getExpenseReport(string $startDate, string $endDate): array
    {
        $db = \Config\Database::connect();
        
        try {
            // Check if prescription_items table exists
            $tables = $db->listTables();
            if (!in_array('prescription_items', $tables)) {
                return [
                    'total_expenses' => 0,
                    'transaction_count' => 0,
                ];
            }
            
            // Get medicine expenses from prescription_items (cost of medicines sold)
            $builder = $db->table('prescription_items pi');
            $builder->select('SUM(pi.total) as total_expenses, COUNT(DISTINCT pi.prescription_id) as transaction_count');
            $builder->join('prescriptions p', 'pi.prescription_id = p.id', 'left');
            $builder->where('p.date >=', $startDate);
            $builder->where('p.date <=', $endDate);
            
            $result = $builder->get()->getRowArray();
            
            return [
                'total_expenses' => (float)($result['total_expenses'] ?? 0),
                'transaction_count' => (int)($result['transaction_count'] ?? 0),
            ];
        } catch (\Exception $e) {
            // If table doesn't exist or query fails, return zeros
            return [
                'total_expenses' => 0,
                'transaction_count' => 0,
            ];
        }
    }

    /**
     * Get inventory report
     */
    public function getInventoryReport(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['category'])) {
            $builder->where('category', $filters['category']);
        }

        if (!empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'low_stock') {
                $builder->where('stock <', 10);
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $builder->where('stock', 0);
            }
        }

        $medicines = $builder->get()->getResultArray();

        $lowStock = [];
        $outOfStock = [];
        $expiringSoon = [];
        $totalValue = 0;

        $today = new \DateTime();
        $thirtyDays = clone $today;
        $thirtyDays->modify('+30 days');

        foreach ($medicines as $medicine) {
            $stock = (int)($medicine['stock'] ?? 0);
            $price = (float)($medicine['unit_price'] ?? $medicine['retail_price'] ?? 0);
            $totalValue += $stock * $price;

            if ($stock < 10 && $stock > 0) {
                $lowStock[] = $medicine;
            }

            if ($stock === 0) {
                $outOfStock[] = $medicine;
            }

            if (!empty($medicine['expiry_date'])) {
                $expiry = new \DateTime($medicine['expiry_date']);
                if ($expiry <= $thirtyDays && $expiry >= $today) {
                    $expiringSoon[] = $medicine;
                }
            }
        }

        return [
            'total_medicines' => count($medicines),
            'total_stock_value' => $totalValue,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'expiring_soon' => $expiringSoon,
            'medicines' => $medicines,
        ];
    }

    /**
     * Get sales report
     * Uses prescription_items for medicine sales data
     */
    public function getSalesReport(array $filters = []): array
    {
        $db = \Config\Database::connect();
        
        try {
            // Check if required tables exist
            $tables = $db->listTables();
            if (!in_array('prescription_items', $tables) || !in_array('prescriptions', $tables)) {
                return [
                    'total_sales' => 0,
                    'total_transactions' => 0,
                    'by_category' => [],
                    'top_medicines' => [],
                    'transactions' => [],
                ];
            }
            
            // Get sales from prescription_items (medicine sales)
            $builder = $db->table('prescription_items pi');
            $builder->select('pi.*, p.date, p.patient_id, p.total_amount as prescription_total, m.name as medicine_name, m.category');
            $builder->join('prescriptions p', 'pi.prescription_id = p.id', 'left');
            $builder->join('medicines m', 'pi.medication_id = m.id', 'left');

            if (!empty($filters['start_date'])) {
                $builder->where('p.date >=', $filters['start_date']);
            }

            if (!empty($filters['end_date'])) {
                $builder->where('p.date <=', $filters['end_date']);
            }

            if (!empty($filters['medicine_id'])) {
                $builder->where('pi.medication_id', $filters['medicine_id']);
            }

            $transactions = $builder->get()->getResultArray();

            $totalSales = 0;
            $byCategory = [];
            $byMedicine = [];

            foreach ($transactions as $transaction) {
                $amount = (float)($transaction['total'] ?? 0);
                $totalSales += $amount;

                $category = $transaction['category'] ?? 'Unknown';
                $byCategory[$category] = ($byCategory[$category] ?? 0) + $amount;

                $medicineId = $transaction['medication_id'] ?? '';
                if ($medicineId) {
                    $byMedicine[$medicineId] = ($byMedicine[$medicineId] ?? 0) + $amount;
                }
            }

            arsort($byMedicine);

            return [
                'total_sales' => $totalSales,
                'total_transactions' => count($transactions),
                'by_category' => $byCategory,
                'top_medicines' => array_slice($byMedicine, 0, 10, true),
                'transactions' => $transactions,
            ];
        } catch (\Exception $e) {
            // If tables don't exist or query fails, return empty data
            return [
                'total_sales' => 0,
                'total_transactions' => 0,
                'by_category' => [],
                'top_medicines' => [],
                'transactions' => [],
            ];
        }
    }
}
