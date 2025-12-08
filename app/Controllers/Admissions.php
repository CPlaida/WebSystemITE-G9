<?php

namespace App\Controllers;

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

    /**
     * Get role-based view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleMap = [
            'admin' => 'admin',
            'nurse' => 'admin', // Nurses use admin views (unified)
            'receptionist' => 'admin',
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/patients/{$viewName}";
    }

    public function create()
    {
        // Only admin, nurse, and receptionist can create admissions
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
        // Doctors list for dropdown (doctor_id used as FK)
        $doctors = $this->userModel
            ->select("d.id AS doctor_id, users.id AS user_id, COALESCE(NULLIF(CONCAT(d.first_name, ' ', d.last_name), ' '), users.username) AS display_name, d.first_name, d.last_name, users.username")
            ->join('roles r', 'users.role_id = r.id', 'left')
            ->join('doctors d', 'd.user_id = users.id', 'left')
            ->where('r.name', 'doctor')
            ->where('users.status', 'active')
            ->where('d.id IS NOT NULL', null, false)
            ->orderBy('d.first_name', 'ASC')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        return view($this->getRoleViewPath('AdmissionRegister'), [
            'title' => 'New Admission',
            'doctors' => $doctors,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function store()
    {
        // Only admin, nurse, and receptionist can create admissions
        $this->requireRole(['admin', 'nurse', 'receptionist']);
        
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
            'attending_doctor_id' => 'required|is_not_unique[doctors.id]',
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
            'attending_doctor_id' => (int) $this->request->getPost('attending_doctor_id'),
            'ward' => $this->request->getPost('ward') ?: null,
            'room' => $this->request->getPost('room') ?: null,
            'bed_id' => $bedId,
            'admitting_diagnosis' => $this->request->getPost('admitting_diagnosis'),
            'reason_admission' => $this->request->getPost('reason_admission') ?: null,
            'status' => 'admitted',
        ];

        if ($this->admissionModel->insert($payload)) {
            // Mark patient as inpatient and persist bed assignment
            try {
                $this->patientModel->update($patientId, [
                    'type' => 'inpatient',
                    'bed_id' => $bedId,
                ]);
            } catch (\Throwable $e) {
            }
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

    public function discharge($id = null)
    {
        // Only admin and nurse can discharge patients
        $this->requireRole(['admin', 'nurse']);
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method'
            ])->setStatusCode(400);
        }

        $admissionId = (int) ($id ?? $this->request->getPost('admission_id'));
        if ($admissionId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid admission ID.'
            ])->setStatusCode(422);
        }

        $admission = $this->admissionModel->find($admissionId);
        if (!$admission) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admission record not found.'
            ])->setStatusCode(404);
        }

        if (($admission['status'] ?? '') !== 'admitted') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Patient is not currently admitted.'
            ])->setStatusCode(409);
        }

        $db = db_connect();
        $db->transException(true)->transStart();

        try {
            $this->admissionModel->update($admissionId, [
                'status' => 'discharged',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $patientId = $admission['patient_id'] ?? null;
            if (!empty($patientId)) {
                $this->patientModel->update($patientId, [
                    'type' => 'outpatient',
                    'bed_id' => null,
                ]);
            }

            $bedId = $admission['bed_id'] ?? null;
            if (!empty($bedId)) {
                $this->bedModel->update($bedId, ['status' => 'Available']);
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Failed to discharge patient: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to discharge patient. Please try again.'
            ])->setStatusCode(500);
        }

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to discharge patient. Please try again.'
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Patient discharged successfully.'
        ]);
    }
}

