<?php
namespace App\Services\Billing\Providers;

class PharmacyChargeProvider extends AbstractChargeProvider
{
    public function getCharges(string $patientId): array
    {
        $patientId = trim($patientId);
        if ($patientId === '' || !$this->tableExists('pharmacy_transactions')) {
            return [];
        }

        $builder = $this->db->table('pharmacy_transactions pt');
        // Check if prescription_id field exists in pharmacy_transactions
        $hasPrescriptionId = $this->fieldExists('pharmacy_transactions', 'prescription_id');
        if ($hasPrescriptionId) {
            $builder->select('pt.id, pt.transaction_number, pt.date, pt.total_amount, pt.patient_id, pt.prescription_id');
        } else {
            $builder->select('pt.id, pt.transaction_number, pt.date, pt.total_amount, pt.patient_id');
        }
        $builder->where('pt.patient_id', $patientId);
        if ($this->fieldExists('pharmacy_transactions', 'billed')) {
            $builder->groupStart()
                ->where('pt.billed', 0)
                ->orWhere('pt.billed IS NULL', null, false)
                ->groupEnd();
        }
        $builder->orderBy('pt.date', 'DESC');
        $rows = $builder->get()->getResultArray();

        if (empty($rows)) {
            return [];
        }

        $rows = $this->filterOutAlreadyLinked($rows, 'pharmacy_transactions');

        $items = [];
        foreach ($rows as $row) {
            $amount = (float)($row['total_amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }
            
            // Get medicine details for this transaction
            $medicineItems = $this->getMedicineItemsForTransaction($row);
            
            if (!empty($medicineItems)) {
                // Create a separate billing item for each medicine
                $transactionDate = !empty($row['date']) ? date('M d, Y', strtotime($row['date'])) : '';
                foreach ($medicineItems as $medItem) {
                    $item = $this->defaultItem();
                    $item['service'] = 'Pharmacy: ' . $medItem['name'];
                    if ($transactionDate !== '') {
                        $item['service'] .= ' (' . $transactionDate . ')';
                    }
                    $item['qty'] = $medItem['quantity'];
                    $item['price'] = $medItem['unit_price'];
                    $item['amount'] = $medItem['total_price'];
                    $item['category'] = 'pharmacy';
                    $item['source_table'] = 'pharmacy_transactions';
                    $item['source_id'] = (string)($row['id'] ?? '');
                    $items[] = $item;
                }
            } else {
                // Fallback to transaction number if no medicines found
                $serviceLabel = 'Pharmacy: ' . ($row['transaction_number'] ?? ('Txn ' . $row['id'] ?? ''));
                if (!empty($row['date'])) {
                    $serviceLabel .= ' (' . date('M d, Y', strtotime($row['date'])) . ')';
                }
                
                $item = $this->defaultItem();
                $item['service'] = $serviceLabel;
                $item['price'] = $amount;
                $item['amount'] = $amount;
                $item['category'] = 'pharmacy';
                $item['source_table'] = 'pharmacy_transactions';
                $item['source_id'] = (string)($row['id'] ?? '');
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Get medicine items with details for a pharmacy transaction
     * 
     * @param array $transaction Transaction row data
     * @return array Array of medicine items with name, quantity, unit_price, and total_price
     */
    private function getMedicineItemsForTransaction(array $transaction): array
    {
        $prescriptionId = null;
        
        // Try to get prescription_id from transaction if field exists
        if (isset($transaction['prescription_id']) && !empty($transaction['prescription_id'])) {
            $prescriptionId = (int)$transaction['prescription_id'];
        } else {
            // Try to match by date if prescription_id is not available
            if (!empty($transaction['date']) && $this->tableExists('prescriptions')) {
                $prescription = $this->db->table('prescriptions')
                    ->select('id')
                    ->where('patient_id', $transaction['patient_id'])
                    ->where('date', $transaction['date'])
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                
                if (!empty($prescription['id'])) {
                    $prescriptionId = (int)$prescription['id'];
                }
            }
        }
        
        if (empty($prescriptionId) || !$this->tableExists('prescription_items') || !$this->tableExists('medicines')) {
            return [];
        }
        
        // Get medicine names and prices from prescription_items
        $items = $this->db->table('prescription_items pi')
            ->select('m.name as medicine_name, pi.quantity, pi.price as unit_price, pi.total as total_price')
            ->join('medicines m', 'm.id = pi.medication_id', 'left')
            ->where('pi.prescription_id', $prescriptionId)
            ->get()
            ->getResultArray();
        
        $medicineItems = [];
        foreach ($items as $item) {
            $medicineName = $item['medicine_name'] ?? null;
            $quantity = (int)($item['quantity'] ?? 1);
            $unitPrice = (float)($item['unit_price'] ?? 0);
            $totalPrice = (float)($item['total_price'] ?? ($unitPrice * $quantity));
            
            if (!empty($medicineName)) {
                $medicineItems[] = [
                    'name' => $medicineName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }
        }
        
        return $medicineItems;
    }

}
