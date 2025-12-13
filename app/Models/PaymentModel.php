<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'billing_id',
        'patient_id',
        'amount',
        'payment_method',
        'payment_date',
        'notes',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'billing_id' => 'required|integer|is_not_unique[billing.id]',
        'patient_id' => 'required|string|is_not_unique[patients.id]',
        'amount' => 'required|decimal|greater_than[0]',
        'payment_method' => 'permit_empty|in_list[cash,credit,debit]',
        'payment_date' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'billing_id' => [
            'required' => 'Billing ID is required',
            'is_not_unique' => 'Invalid billing ID',
        ],
        'patient_id' => [
            'required' => 'Patient ID is required',
            'is_not_unique' => 'Invalid patient ID',
        ],
        'amount' => [
            'required' => 'Payment amount is required',
            'greater_than' => 'Payment amount must be greater than 0',
        ],
    ];

    /**
     * Get all payments for a specific bill
     */
    public function getPaymentsByBill(int $billingId): array
    {
        return $this->where('billing_id', $billingId)
            ->orderBy('payment_date', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get total amount paid for a bill
     */
    public function getTotalPaid(int $billingId): float
    {
        $result = $this->selectSum('amount', 'total')
            ->where('billing_id', $billingId)
            ->first();
        
        // Handle both 'amount' and 'total' keys (CodeIgniter may return either)
        $total = (float)($result['total'] ?? $result['amount'] ?? 0);
        
        return $total;
    }

    /**
     * Get all payments for a patient
     */
    public function getPaymentsByPatient(string $patientId): array
    {
        return $this->where('patient_id', $patientId)
            ->orderBy('payment_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}

