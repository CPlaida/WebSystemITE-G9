<?php
namespace App\Models;

use CodeIgniter\Model;

class PrescriptionItemModel extends Model
{
    protected $table = 'prescription_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['prescription_id','medication_id','quantity','price','total','created_at'];
    protected $useTimestamps = false;
}