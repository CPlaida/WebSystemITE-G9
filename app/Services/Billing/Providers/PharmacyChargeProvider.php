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
        $builder->select('pt.id, pt.transaction_number, pt.date, pt.total_amount, pt.patient_id');
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

        return $items;
    }

}
