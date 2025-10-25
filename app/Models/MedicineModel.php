<?php namespace App\Models;

use CodeIgniter\Model;

class MedicineModel extends Model
{
    protected $table = 'medicines';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'medicine_id', 'name', 'brand', 'category', 'stock', 'price', 'expiry_date'
    ];
}
