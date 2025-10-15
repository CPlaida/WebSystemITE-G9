<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;

class BillingModel extends Model
{
    protected $table = 'billing';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    
    private function tableHas(string $table, string $field = null): bool
    {
        $db = \Config\Database::connect();
        try {
            $fields = $db->getFieldNames($table);
            if ($field === null) return !empty($fields);
            return in_array($field, $fields, true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    // Keep original columns from existing migration. We will map normalized names at the controller/view level.
    protected $allowedFields = [
        'patient_id',
        'appointment_id',
        'consultation_fee',
        'medication_cost',
        'lab_tests_cost',
        'other_charges',
        'total_amount',
        'discount',
        'tax',
        'final_amount',      // amount equivalent
        'payment_status',    // status equivalent (pending/partial/paid/overdue)
        'payment_method',
        'bill_date',         // billing_date equivalent
        'due_date',
        'notes',
        'service_id',        // added via migration to link to services
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Base query joining patients and services for listing/search.
     */
    public function withRelations(): BaseBuilder
    {
        $builder = $this->db->table($this->table . ' b')
            ->select("b.*, CONCAT(p.first_name, ' ', p.last_name) AS patient_name, p.address AS patient_address, p.phone AS patient_phone")
            ->join('patients p', 'p.id = b.patient_id', 'left');

        // Optional services join if schema exists
        if ($this->tableHas('billing', 'service_id') && $this->tableHas('services')) {
            $builder->select('s.name AS service_name, s.price AS service_price')
                    ->join('services s', 's.id = b.service_id', 'left');
        }
        return $builder;
    }

    /**
     * Search by invoice number (bill_id) or patient name.
     */
    public function search(?string $term): array
    {
        $builder = $this->withRelations();
        if ($term) {
            $builder->groupStart()
                ->like('b.id', $term)
                ->orLike('p.first_name', $term)
                ->orLike('p.last_name', $term)
            ->groupEnd();
        }
        $builder->orderBy('b.created_at', 'DESC');
        return $builder->get()->getResultArray();
    }

    /**
     * List helper for index view with optional term.
     */
    public function getList(?string $term = null): array
    {
        return $this->search($term);
    }

    /**
     * Find a single bill with relations by numeric ID.
     */
    public function findWithRelations(int $id): ?array
    {
        $row = $this->withRelations()
            ->where('b.id', $id)
            ->get()->getRowArray();
        return $row ?: null;
    }

    /**
     * Dashboard totals helpers.
     * - totalRevenue: sum of final_amount for paid bills
     * - pendingCount: count of pending bills
     * - paidThisMonth: sum of final_amount for paid in current month
     * - outstanding: sum of final_amount for pending bills
     */
    public function getTotals(): array
    {
        $now = date('Y-m-01');
        $end = date('Y-m-t');

        // Total Revenue (paid)
        $totalRevenue = $this->builder()
            ->selectSum('final_amount', 'sum')
            ->where('payment_status', 'paid')
            ->get()->getRow('sum') ?? 0;

        // Pending Bills count
        $pendingCount = $this->builder()
            ->select('COUNT(*) AS cnt')
            ->where('payment_status', 'pending')
            ->get()->getRow('cnt') ?? 0;

        // Paid This Month (paid in current month by bill_date)
        $paidThisMonth = $this->builder()
            ->selectSum('final_amount', 'sum')
            ->where('payment_status', 'paid')
            ->where('bill_date >=', $now)
            ->where('bill_date <=', $end)
            ->get()->getRow('sum') ?? 0;

        // Outstanding (pending sum)
        $outstanding = $this->builder()
            ->selectSum('final_amount', 'sum')
            ->where('payment_status', 'pending')
            ->get()->getRow('sum') ?? 0;

        return [
            'totalRevenue' => (float) ($totalRevenue ?: 0),
            'pendingCount' => (int) ($pendingCount ?: 0),
            'paidThisMonth' => (float) ($paidThisMonth ?: 0),
            'outstanding' => (float) ($outstanding ?: 0),
        ];
    }
}
