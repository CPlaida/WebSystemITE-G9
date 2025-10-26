<?php namespace App\Models;

use CodeIgniter\Model;

class MedicineModel extends Model
{
    protected $table = 'medicines';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'brand', 'category', 'stock', 'price', 'expiry_date'
    ];
}
