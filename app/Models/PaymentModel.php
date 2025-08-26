<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['bill_id', 'amount', 'payment_date', 'method', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function getTodayRevenue()
    {
        return $this->selectSum('amount')
                   ->where('DATE(payment_date)', date('Y-m-d'))
                   ->get()
                   ->getRow()
                   ->amount ?? 0;
    }
}
