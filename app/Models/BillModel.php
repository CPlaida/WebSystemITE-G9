<?php

namespace App\Models;

use CodeIgniter\Model;

class BillModel extends Model
{
    protected $table = 'bills';
    protected $primaryKey = 'id';
    protected $allowedFields = ['patient_id', 'amount', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getPendingBills()
    {
        return $this->where('status', 'pending')->findAll();
    }

    public function getOutstandingBalance()
    {
        return $this->selectSum('amount')
                   ->where('status', 'pending')
                   ->get()
                   ->getRow()
                   ->amount ?? 0;
    }
}
