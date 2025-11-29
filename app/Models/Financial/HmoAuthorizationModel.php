<?php

namespace App\Models\Financial;

use CodeIgniter\Model;

class HmoAuthorizationModel extends Model
{
    protected $table = 'hmo_authorizations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'billing_id',
        'patient_id',
        'provider_id',
        'loa_number',
        'coverage_limit',
        'approved_amount',
        'patient_share',
        'status',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get HMO claims report
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

        if (!empty($filters['hmo_provider_id'])) {
            $builder->where('provider_id', $filters['hmo_provider_id']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        $claims = $builder->get()->getResultArray();

        $totalClaims = 0;
        $totalApproved = 0;
        $byProvider = [];

        foreach ($claims as $claim) {
            $totalClaims++;
            $amount = (float)($claim['approved_amount'] ?? 0);
            $totalApproved += $amount;

            $providerId = $claim['provider_id'] ?? '';
            if ($providerId) {
                $byProvider[$providerId] = ($byProvider[$providerId] ?? 0) + $amount;
            }
        }

        return [
            'total_claims' => $totalClaims,
            'total_approved_amount' => $totalApproved,
            'by_provider' => $byProvider,
            'claims' => $claims,
        ];
    }
}
