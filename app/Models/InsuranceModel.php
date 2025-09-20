<?php

namespace App\Models;

use CodeIgniter\Model;

class InsuranceModel extends Model
{
    protected $table = 'insurance';
    protected $primaryKey = 'id';
    protected $allowedFields = ['patient_id', 'claim_number', 'amount', 'status', 'provider', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getPendingClaims()
    {
        return $this->where('status', 'pending')->findAll();
    }
}
