<?php

namespace App\Controllers\Doctor;

use App\Controllers\BaseController;
use App\Models\PatientVitalModel;

class VitalsController extends BaseController
{
    protected $vitalModel;

    public function __construct()
    {
        $this->vitalModel = new PatientVitalModel();
    }

    /**
     * GET /doctor/vitals?patient_id=...
     * Returns latest vitals for the given patient.
     */
    public function show()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        // Allow doctors, nurses, and admins to view vitals
        if (!in_array(session()->get('role'), ['doctor', 'nurse', 'admin'])) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $patientId = (string) $this->request->getGet('patient_id');
        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        $vitals = $this->vitalModel->getLatestForPatient($patientId);

        return $this->response->setJSON([
            'success' => true,
            'vitals'  => $vitals,
        ]);
    }

    /**
     * POST /doctor/vitals/save
     * Body: patient_id, blood_pressure, heart_rate, temperature
     */
    public function save()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        // Allow doctors and nurses to record vitals
        if (!in_array(session()->get('role'), ['doctor', 'nurse', 'admin'])) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $data = [
            'patient_id'     => (string) $this->request->getPost('patient_id'),
            'blood_pressure' => trim((string) $this->request->getPost('blood_pressure')),
            'heart_rate'     => $this->request->getPost('heart_rate'),
            'temperature'    => $this->request->getPost('temperature'),
            'recorded_by'    => (int) session()->get('user_id'),
        ];

        if ($data['patient_id'] === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        if (!$this->vitalModel->save($data)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Failed to save vitals',
                    'errors'  => $this->vitalModel->errors(),
                ]);
        }

        $latest = $this->vitalModel->getLatestForPatient($data['patient_id']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Vitals saved successfully',
            'vitals'  => $latest,
        ]);
    }
}


