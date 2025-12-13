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
        'amount_paid',       // cumulative amount paid
        'last_payment_date', // date of last payment
        'payment_status',    // status equivalent (pending/partial/paid)
        'payment_method',
        // Normalized HMO reference (new)
        'hmo_authorization_id',
        // Legacy HMO fields (kept for backward compatibility, will be removed later)
        'hmo_provider_id',
        'hmo_member_no',
        'hmo_valid_from',
        'hmo_valid_to',
        'hmo_loa_number',
        'hmo_coverage_limit',
        'hmo_approved_amount',
        'hmo_patient_share',
        'hmo_status',
        'hmo_notes',
        'bill_date',         // billing_date equivalent
        'due_date',
        'notes',
        'service_id',        // added via migration to link to services
        'created_at',
        'updated_at',
        // Normalized PhilHealth reference (new)
        'philhealth_audit_id',
        // Legacy PhilHealth fields (kept for backward compatibility, will be removed later)
        'philhealth_member',
        'philhealth_suggested_amount',
        'philhealth_approved_amount',
        'philhealth_codes_used',
        'philhealth_rate_ids',
        'philhealth_verified_by',
        'philhealth_verified_at',
        // Optional source fields captured on bill
        'primary_icd10_code',
        'primary_rvs_code',
        'admission_date',
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
            $builder->select('s.name AS service_name, s.base_price AS service_price')
                    ->join('services s', 's.id = b.service_id', 'left');
        }
        
        // Join HMO authorization data using billing_id (the actual relationship used by syncHmoAuthorization)
        if ($this->tableHas('hmo_authorizations')) {
            // Join hmo_authorizations table and select fields, preferring hmo_authorizations data over legacy billing fields
            $builder->select('COALESCE(ha.loa_number, b.hmo_loa_number) AS hmo_loa_number, 
                            COALESCE(ha.coverage_limit, b.hmo_coverage_limit) AS hmo_coverage_limit, 
                            COALESCE(ha.approved_amount, b.hmo_approved_amount) AS hmo_approved_amount, 
                            COALESCE(ha.patient_share, b.hmo_patient_share) AS hmo_patient_share, 
                            COALESCE(ha.status, b.hmo_status) AS hmo_status, 
                            COALESCE(ha.notes, b.hmo_notes) AS hmo_notes,
                            COALESCE(ha.provider_id, b.hmo_provider_id) AS hmo_provider_id')
                    ->join('hmo_authorizations ha', 'ha.billing_id = b.id', 'left');
        }
        
        // Join HMO providers
        if ($this->tableHas('hmo_providers')) {
            if ($this->tableHas('hmo_authorizations')) {
                // Join using provider_id from hmo_authorizations first, then fallback to billing table
                $builder->join('hmo_providers hp_auth', 'hp_auth.id = ha.provider_id', 'left')
                        ->join('hmo_providers hp', 'hp.id = b.hmo_provider_id', 'left')
                        ->select('COALESCE(hp_auth.name, hp.name) AS hmo_provider_name');
            } else {
                // Fallback to legacy field in billing table
                $builder->select('hp.name AS hmo_provider_name')
                        ->join('hmo_providers hp', 'hp.id = b.hmo_provider_id', 'left');
            }
        }
        
        // Join normalized PhilHealth audit (preferred)
        if ($this->tableHas('billing', 'philhealth_audit_id') && $this->tableHas('bill_philhealth_audits')) {
            $builder->select('pha.suggested_amount AS philhealth_suggested_amount, 
                            pha.approved_amount AS philhealth_approved_amount, 
                            pha.codes_used AS philhealth_codes_used, 
                            pha.officer_user_id AS philhealth_verified_by, 
                            pha.created_at AS philhealth_verified_at,
                            pha.case_rate_id AS philhealth_case_rate_id')
                    ->join('bill_philhealth_audits pha', 'pha.id = b.philhealth_audit_id', 'left')
                    ->join('philhealth_case_rates pcr', 'pcr.id = pha.case_rate_id', 'left')
                    ->select('pcr.code AS philhealth_code, pcr.description AS philhealth_description, 
                            pcr.rate_total AS philhealth_rate_total');
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

    /**
     * Get revenue report data
     */
    public function getRevenueReport(string $startDate, string $endDate, array $filters = []): array
    {
        $builder = $this->withRelations();
        $builder->where('b.bill_date >=', $startDate)
                ->where('b.bill_date <=', $endDate);

        if (!empty($filters['payment_method'])) {
            $builder->where('b.payment_method', $filters['payment_method']);
        }

        if (!empty($filters['payment_status'])) {
            $builder->where('b.payment_status', $filters['payment_status']);
        }

        $bills = $builder->get()->getResultArray();

        $totalRevenue = 0;
        $byPaymentMethod = [];
        $byServiceType = [
            'consultation' => 0,
            'medication' => 0,
            'lab_tests' => 0,
            'other' => 0,
        ];

        foreach ($bills as $bill) {
            $amount = (float)($bill['final_amount'] ?? 0);
            $totalRevenue += $amount;

            $method = $bill['payment_method'] ?? 'cash';
            $byPaymentMethod[$method] = ($byPaymentMethod[$method] ?? 0) + $amount;

            $byServiceType['consultation'] += (float)($bill['consultation_fee'] ?? 0);
            $byServiceType['medication'] += (float)($bill['medication_cost'] ?? 0);
            $byServiceType['lab_tests'] += (float)($bill['lab_tests_cost'] ?? 0);
            $byServiceType['other'] += (float)($bill['other_charges'] ?? 0);
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_bills' => count($bills),
            'by_payment_method' => $byPaymentMethod,
            'by_service_type' => $byServiceType,
            'bills' => $bills,
        ];
    }

    /**
     * Get outstanding payments
     */
    public function getOutstandingPayments(array $filters = []): array
    {
        $builder = $this->withRelations();
        $builder->whereIn('b.payment_status', ['pending', 'partial', 'overdue']);

        if (!empty($filters['payment_status'])) {
            $builder->where('b.payment_status', $filters['payment_status']);
        }

        if (!empty($filters['patient_id'])) {
            $builder->where('b.patient_id', $filters['patient_id']);
        }

        if (!empty($filters['start_date'])) {
            $builder->where('b.bill_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('b.bill_date <=', $filters['end_date']);
        }

        $bills = $builder->orderBy('b.bill_date', 'ASC')->get()->getResultArray();

        $totalOutstanding = 0;
        $aging = [
            '0-30' => 0,
            '31-60' => 0,
            '61-90' => 0,
            '90+' => 0,
        ];

        $today = new \DateTime();
        foreach ($bills as &$bill) {
            $amount = (float)($bill['final_amount'] ?? 0);
            $totalOutstanding += $amount;

            $billDate = new \DateTime($bill['bill_date'] ?? date('Y-m-d'));
            $days = $today->diff($billDate)->days;

            if ($days <= 30) {
                $aging['0-30'] += $amount;
            } elseif ($days <= 60) {
                $aging['31-60'] += $amount;
            } elseif ($days <= 90) {
                $aging['61-90'] += $amount;
            } else {
                $aging['90+'] += $amount;
            }

            $bill['days_overdue'] = $days;
        }

        return [
            'total_outstanding' => $totalOutstanding,
            'total_bills' => count($bills),
            'aging_analysis' => $aging,
            'bills' => $bills,
        ];
    }
}
