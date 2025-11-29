<?php namespace App\Models;

use CodeIgniter\Model;

class MedicineModel extends Model
{
    protected $table = 'medicines';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false;
    protected $allowedFields = [
        'id', 'barcode', 'name', 'brand', 'category', 'stock', 'unit_price', 'retail_price', 'manufactured_date', 'expiry_date'
    ];
    
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
        } catch (\Exception $e) {
            // If table doesn't exist or error, just continue without image field
        }
    }

    protected $beforeInsert = ['generateId'];

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
