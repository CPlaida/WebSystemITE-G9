<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdmissionModel;
use App\Models\PatientModel;
use App\Models\UserModel;
use App\Models\BedModel;

class Admissions extends BaseController
{
    protected $admissionModel;
    protected $patientModel;
    protected $userModel;
    protected $bedModel;

    public function __construct()
    {
        $this->admissionModel = new AdmissionModel();
        $this->patientModel   = new PatientModel();
        $this->userModel      = new UserModel();
        $this->bedModel       = new BedModel();
        helper(['form', 'url']);
    }

    public function create()
    {
        // Doctors list for dropdown
        $doctors = $this->userModel
            ->select("users.id, COALESCE(NULLIF(CONCAT(d.first_name, ' ', d.last_name), ' '), users.username) AS display_name, d.first_name, d.last_name, users.username")
            ->join('roles r', 'users.role_id = r.id', 'left')
            ->join('doctors d', 'd.user_id = users.id', 'left')
            ->where('r.name', 'doctor')
            ->where('users.status', 'active')
            ->orderBy('d.first_name', 'ASC')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        return view('Roles/admin/patients/AdmissionRegister', [
            'title' => 'New Admission',
            'doctors' => $doctors,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        $rules = [
            'patient_id' => 'required|string|is_not_unique[patients.id]',
            'admission_date' => 'required|valid_date',
            'admission_time' => 'permit_empty',
            'admission_type' => 'required|in_list[emergency,elective,transfer]',
            'attending_physician' => 'required|integer|is_not_unique[users.id]',
            'ward' => 'permit_empty|max_length[100]',
            'room' => 'permit_empty|max_length[100]',
            'bed_id' => 'required|integer|is_not_unique[beds.id]',
            'admitting_diagnosis' => 'required|string',
            'reason_admission' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please fix the following errors',
                'errors' => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        // Verify patient exists
        $patientId = (string) $this->request->getPost('patient_id');
        $patient = $this->patientModel->find($patientId);
        if (!$patient) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Patient not found.'
            ])->setStatusCode(404);
        }

        // Verify bed is available
        $bedId = (int) $this->request->getPost('bed_id');
        $bed = $this->bedModel->find($bedId);
        if (!$bed) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected bed does not exist.'
            ])->setStatusCode(422);
        }
        if (isset($bed['status']) && strtolower($bed['status']) !== 'available') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected bed is not available.'
            ])->setStatusCode(409);
        }

        $payload = [
            'patient_id' => $patientId,
            'admission_date' => $this->request->getPost('admission_date'),
            'admission_time' => $this->request->getPost('admission_time') ?: null,
            'admission_type' => $this->request->getPost('admission_type'),
            'attending_physician' => (int) $this->request->getPost('attending_physician'),
            'ward' => $this->request->getPost('ward') ?: null,
            'room' => $this->request->getPost('room') ?: null,
            'bed_id' => $bedId,
            'admitting_diagnosis' => $this->request->getPost('admitting_diagnosis'),
            'reason_admission' => $this->request->getPost('reason_admission') ?: null,
            'status' => 'admitted',
        ];

        if ($this->admissionModel->insert($payload)) {
            // Mark patient as inpatient
            try { $this->patientModel->update($patientId, ['type' => 'inpatient']); } catch (\Throwable $e) {}
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Admission registered successfully.',
                'data' => ['id' => $this->admissionModel->getInsertID()]
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Failed to save admission. Please try again.'
        ])->setStatusCode(500);
    }
}
