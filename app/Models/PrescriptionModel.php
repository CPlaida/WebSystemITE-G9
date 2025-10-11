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
        'doctor_id',
        'medication',
        'dosage',
        'instructions',
        'date_prescribed',
        'status'
    ];
    
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
    
    protected $validationRules = [
        'patient_id' => 'required|numeric',
        'doctor_id' => 'required|numeric',
        'medication' => 'required',
        'dosage' => 'required',
        'status' => 'required|in_list[pending,completed,cancelled]'
    ];
}
