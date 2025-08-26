<?php

namespace App\Models;

use CodeIgniter\Model;

class InsuranceModel extends Model
{
    protected $table = 'insurance_claims';
    protected $primaryKey = 'id';
    protected $allowedFields = ['patient_id', 'claim_amount', 'status', 'claim_date', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getPendingClaims()
    {
        return $this->where('status', 'pending')->findAll();
    }
}
