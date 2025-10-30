<?php
namespace App\Models;

use CodeIgniter\Model;

class PharmacyTransactionModel extends Model
{
    protected $table = 'pharmacy_transactions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['transaction_number','patient_id','date','total_items','total_amount','created_at','updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function generateNumber(): string
    {
        $last = $this->orderBy('id','DESC')->first();
        $n = $last ? (int)substr($last['transaction_number'], 4) + 1 : 1;
        return 'TRX-' . str_pad((string)$n, 3, '0', STR_PAD_LEFT);
    }
}