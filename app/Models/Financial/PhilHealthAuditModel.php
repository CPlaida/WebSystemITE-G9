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
}
