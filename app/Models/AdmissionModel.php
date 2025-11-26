<?php

namespace App\Models;

use CodeIgniter\Model;

class AdmissionModel extends Model
{
    protected $table = 'admission_details';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'patient_id',
        'admission_date',
        'admission_time',
        'admission_type',
        'attending_physician',
        'ward',
        'room',
        'bed_id',
        'admitting_diagnosis',
        'reason_admission',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'patient_id' => 'required|string|is_not_unique[patients.id] ',
        'admission_date' => 'required|valid_date',
        'admission_time' => 'permit_empty',
        'admission_type' => 'required|in_list[emergency,elective,transfer]',
        'attending_physician' => 'required|integer|is_not_unique[users.id]',
        'ward' => 'permit_empty|string|max_length[100]',
        'room' => 'permit_empty|string|max_length[100]',
        'bed_id' => 'required|integer|is_not_unique[beds.id]',
        'admitting_diagnosis' => 'required|string',
        'reason_admission' => 'permit_empty|string',
        'status' => 'permit_empty|in_list[admitted,discharged,cancelled]'
    ];

    protected $afterInsert = ['occupyBed'];

    protected function occupyBed(array $data)
    {
        try {
            $id = $data['id'] ?? null;
            if (!$id) return $data;
            $row = $this->find($id);
            if (!$row) return $data;
            $bedId = $row['bed_id'] ?? null;
            if (!$bedId) return $data;

            $bedModel = new BedModel();
            $bedModel->update($bedId, ['status' => 'Occupied']);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to occupy bed on admission: ' . $e->getMessage());
        }
        return $data;
    }
}
