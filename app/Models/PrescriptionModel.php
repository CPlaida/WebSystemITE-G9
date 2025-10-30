<?php

namespace App\Models;

use CodeIgniter\Model;

class PrescriptionModel extends Model
{
    protected $table = 'prescriptions';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'patient_id',
        'date',
        'payment_method',
        'subtotal',
        'tax',
        'total_amount',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'required|numeric',
        'date' => 'required|valid_date',
        'payment_method' => 'required|in_list[cash,insurance]',
        'subtotal' => 'required|decimal',
        'tax' => 'required|decimal',
        'total_amount' => 'required|decimal'
    ];
}