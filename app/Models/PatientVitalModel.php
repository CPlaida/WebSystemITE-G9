<?php

namespace App\Models;

use CodeIgniter\Model;

class PatientVitalModel extends Model
{
    protected $table            = 'patient_vitals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'patient_id',
        'blood_pressure',
        'heart_rate',
        'temperature',
        'recorded_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'patient_id'     => 'required|min_length[1]|max_length[20]',
        'blood_pressure' => 'permit_empty|max_length[15]',
        'heart_rate'     => 'permit_empty|integer',
        'temperature'    => 'permit_empty|decimal',
    ];

    /**
     * Get the latest vitals record for a patient.
     */
    public function getLatestForPatient(string $patientId): ?array
    {
        return $this->where('patient_id', $patientId)
                    ->orderBy('created_at', 'DESC')
                    ->first();
    }
}


