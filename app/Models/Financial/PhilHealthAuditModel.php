<?php

namespace App\Models\Financial;

use CodeIgniter\Model;

class PhilHealthAuditModel extends Model
{
    protected $table = 'bill_philhealth_audits';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'bill_id',
        'patient_id',
        'suggested_amount',
        'approved_amount',
        'officer_user_id',
        'codes_used',
        'rate_ids',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get PhilHealth claims report
     */
    public function getClaimsReport(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['start_date'])) {
            $builder->where('created_at >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $builder->where('created_at <=', $filters['end_date'] . ' 23:59:59');
        }

        $claims = $builder->get()->getResultArray();

        $totalClaims = 0;
        $totalApproved = 0;
        $pending = 0;

        foreach ($claims as $claim) {
            $amount = (float)($claim['approved_amount'] ?? 0);
            $totalClaims++;
            $totalApproved += $amount;
        }

        return [
            'total_claims' => $totalClaims,
            'total_approved_amount' => $totalApproved,
            'pending_claims' => $pending,
            'claims' => $claims,
        ];
    }
}
