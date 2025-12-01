<?php

namespace App\Controllers\Doctor;

use App\Controllers\BaseController;
use App\Models\AdmissionModel;

class MedicalRecordsController extends BaseController
{
    protected AdmissionModel $admissionModel;

    public function __construct()
    {
        $this->admissionModel = new AdmissionModel();
    }

    /**
     * GET /doctor/medical-records?patient_id=
     * Returns admission history for a patient so EHR can display medical records.
     */
    public function admissions()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setStatusCode(401)
                ->setJSON(['success' => false, 'message' => 'Not authenticated']);
        }

        if (!in_array(session()->get('role'), ['doctor', 'nurse', 'admin', 'receptionist'], true)) {
            return $this->response->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $patientId = (string) $this->request->getGet('patient_id');
        if ($patientId === '') {
            return $this->response->setStatusCode(400)
                ->setJSON(['success' => false, 'message' => 'patient_id is required']);
        }

        $rows = $this->admissionModel
            ->select([
                'admission_details.id AS admission_id',
                'admission_details.patient_id',
                'admission_details.admission_date',
                'admission_details.admission_time',
                'admission_details.admission_type',
                'admission_details.status AS admission_status',
                'admission_details.admitting_diagnosis',
                'admission_details.reason_admission',
                'admission_details.ward AS admission_ward',
                'admission_details.room AS admission_room',
                'admission_details.updated_at AS update_timestamp',
                'admission_details.attending_doctor_id',
                'beds.ward AS bed_ward',
                'beds.room AS bed_room',
                'beds.bed AS bed_label',
                "COALESCE(NULLIF(CONCAT(TRIM(doctors.first_name), ' ', TRIM(doctors.last_name)), ' '), users.username, CONCAT('Doctor #', doctors.id)) AS physician_name",
                'users.username AS physician_username',
            ])
            ->join('beds', 'beds.id = admission_details.bed_id', 'left')
            ->join('doctors', 'doctors.id = admission_details.attending_doctor_id', 'left')
            ->join('users', 'users.id = doctors.user_id', 'left')
            ->where('admission_details.patient_id', $patientId)
            ->groupBy('admission_details.id')
            ->orderBy('admission_details.admission_date', 'DESC')
            ->orderBy('admission_details.created_at', 'DESC')
            ->findAll();

        $records = array_map(static function (array $row) {
            $dischargeDate = null;
            if (($row['admission_status'] ?? '') === 'discharged') {
                $dischargeDate = $row['update_timestamp'] ?? null;
            }

            return [
                'id' => $row['admission_id'],
                'admission_date' => $row['admission_date'],
                'admission_time' => $row['admission_time'],
                'admission_type' => $row['admission_type'],
                'status' => $row['admission_status'],
                'discharge_date' => $dischargeDate,
                'ward' => $row['admission_ward'] ?? $row['bed_ward'] ?? null,
                'room' => $row['admission_room'] ?? $row['bed_room'] ?? null,
                'bed' => $row['bed_label'] ?? null,
                'physician' => $row['physician_name'] ?? $row['physician_username'] ?? '—',
                'diagnosis' => $row['admitting_diagnosis'] ?? '—',
                'reason' => $row['reason_admission'] ?? null,
            ];
        }, $rows ?? []);

        return $this->response->setJSON([
            'success' => true,
            'records' => $records,
        ]);
    }
}
