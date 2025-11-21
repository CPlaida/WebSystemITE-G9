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
}
