<?php

namespace App\Controllers\Doctor;

use App\Controllers\BaseController;
use App\Models\PrescriptionModel;

class PrescriptionController extends BaseController
{
    protected $prescriptionModel;

    public function __construct()
    {
        $this->prescriptionModel = new PrescriptionModel();
    }

    /**
     * GET /doctor/prescription?patient_id=...
     * Return latest prescription note for the given patient.
     */
    public function show()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        // Allow clinical roles to read notes
        if (!in_array(session()->get('role'), ['doctor', 'nurse', 'admin', 'receptionist'])) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $patientId = (string) $this->request->getGet('patient_id');
        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        $record = $this->prescriptionModel
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $this->response->setJSON([
            'success' => true,
            'note'    => $record['note'] ?? '',
        ]);
    }

    /**
     * POST /doctor/prescription/save
     * Body: patient_id, note
     */
    public function save()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        // Only doctors (and optionally admins) can write notes
        if (!in_array(session()->get('role'), ['doctor', 'admin'])) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $patientId = (string) $this->request->getPost('patient_id');
        $note      = (string) $this->request->getPost('note');

        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        $data = [
            'patient_id'     => $patientId,
            'date'           => date('Y-m-d'),
            'payment_method' => 'cash',
            'subtotal'       => 0,
            'tax'            => 0,
            'total_amount'   => 0,
            'note'           => $note,
        ];

        $this->prescriptionModel->skipValidation(true);
        $insertId = $this->prescriptionModel->insert($data);
        $this->prescriptionModel->skipValidation(false);

        if (!$insertId) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Failed to save prescription note',
                ]);
        }

        $latest = $this->prescriptionModel
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'DESC')
            ->first();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Prescription note saved successfully',
            'note'    => $latest['note'] ?? $note,
        ]);
    }
}


