<?php

namespace App\Models\Financial;

use CodeIgniter\Model;

class PhilHealthCaseRateModel extends Model
{
    protected $table = 'philhealth_case_rates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'code_type',
        'code',
        'description',
        'case_type',
        'rate_total',
        'facility_share',
        'professional_share',
        'effective_from',
        'effective_to',
        'active',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
