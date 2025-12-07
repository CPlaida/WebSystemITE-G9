<?php

namespace App\Controllers\Doctor;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\AdmissionModel;

class PatientController extends BaseController
{
    protected $patientModel;
    protected $admissionModel;
    
    public function __construct()
    {
        $this->patientModel = new PatientModel();
        $this->admissionModel = new AdmissionModel();
        helper(['form', 'url', 'auth']);
    }

    /**
     * Show patient list for doctors (read-only) - unified with Admin design
     */
    public function view()
    {
        // Require logged-in doctor or admin
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $userRole = session()->get('role');
        if (!in_array($userRole, ['doctor', 'admin'])) {
            return redirect()->to('/dashboard')
                ->with('error', 'Access denied. Doctor or Admin only.');
        }

        $filter = $this->request->getGet('filter') ?? 'inpatient';
        $allowedFilters = ['inpatient', 'outpatient', 'admitted', 'discharged'];
        $filter = in_array($filter, $allowedFilters, true) ? $filter : 'inpatient';

        if ($filter === 'admitted') {
            $patients = $this->getAdmittedPatients();
        } elseif ($filter === 'discharged') {
            $patients = $this->getDischargedPatients();
        } else {
            $patients = $this->patientModel
                ->where('type', $filter === 'outpatient' ? 'outpatient' : 'inpatient')
                ->orderBy('last_name', 'ASC')
                ->orderBy('first_name', 'ASC')
                ->findAll();
        }

        return view('Roles/doctor/patients/view', [
            'title' => 'Patient Records',
            'patients' => $patients,
            'currentFilter' => $filter,
        ]);
    }

    protected function getAdmittedPatients(): array
    {
        $rows = $this->admissionModel
            ->select([
                'admission_details.id AS admission_id',
                'admission_details.patient_id AS admission_patient_id',
                'admission_details.admission_date',
                'admission_details.admission_time',
                'admission_details.admission_type',
                'admission_details.status AS admission_status',
                'admission_details.admitting_diagnosis',
                'admission_details.reason_admission',
                'admission_details.ward AS admission_ward',
                'admission_details.room AS admission_room',
                'admission_details.bed_id',
                'patients.*',
                'beds.ward AS bed_ward',
                'beds.room AS bed_room',
                'beds.bed AS bed_label',
                'doctors.id AS attending_doctor_id',
                'users.username AS physician_username',
                "COALESCE(NULLIF(CONCAT(TRIM(doctors.first_name), ' ', TRIM(doctors.last_name)), ' '), users.username, CONCAT('Doctor #', doctors.id)) AS physician_name",
                'role.name AS physician_role',
            ])
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->join('beds', 'beds.id = admission_details.bed_id', 'left')
            ->join('doctors', 'doctors.id = admission_details.attending_doctor_id', 'left')
            ->join('users', 'users.id = doctors.user_id', 'left')
            ->join('roles role', 'role.id = users.role_id', 'left')
            ->where('admission_details.status', 'admitted')
            ->orderBy('CASE WHEN role.name = "doctor" THEN 0 ELSE 1 END', 'ASC', false)
            ->orderBy('admission_details.created_at', 'DESC')
            ->orderBy('admission_details.id', 'DESC')
            ->orderBy('admission_details.admission_date', 'DESC')
            ->orderBy('admission_details.admission_time', 'DESC')
            ->findAll();

        if (empty($rows)) {
            return [];
        }

        $unique = [];
        $seen = [];
        foreach ($rows as $row) {
            $patientId = $row['admission_patient_id'] ?? ($row['patient_id'] ?? ($row['id'] ?? null));
            if (!$patientId || isset($seen[$patientId])) {
                continue;
            }
            $seen[$patientId] = true;
            $unique[] = $row;
        }

        return $unique;
    }

    protected function getDischargedPatients(): array
    {
        $rows = $this->admissionModel
            ->select([
                'admission_details.id AS admission_id',
                'admission_details.patient_id AS admission_patient_id',
                'admission_details.admission_date',
                'admission_details.updated_at AS discharge_date',
                'admission_details.admission_type',
                'admission_details.status AS admission_status',
                'admission_details.admitting_diagnosis',
                'admission_details.reason_admission',
                'admission_details.ward AS admission_ward',
                'admission_details.room AS admission_room',
                'patients.*',
                'doctors.id AS attending_doctor_id',
                'users.username AS physician_username',
                "COALESCE(NULLIF(CONCAT(TRIM(doctors.first_name), ' ', TRIM(doctors.last_name)), ' '), users.username, CONCAT('Doctor #', doctors.id)) AS physician_name",
                'role.name AS physician_role',
            ])
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->join('doctors', 'doctors.id = admission_details.attending_doctor_id', 'left')
            ->join('users', 'users.id = doctors.user_id', 'left')
            ->join('roles role', 'role.id = users.role_id', 'left')
            ->where('admission_details.status', 'discharged')
            ->orderBy('admission_details.updated_at', 'DESC')
            ->orderBy('admission_details.id', 'DESC')
            ->findAll();

        if (empty($rows)) {
            return [];
        }

        $unique = [];
        $seen = [];
        foreach ($rows as $row) {
            $patientId = $row['admission_patient_id'] ?? ($row['patient_id'] ?? ($row['id'] ?? null));
            if (!$patientId || isset($seen[$patientId])) {
                continue;
            }
            $seen[$patientId] = true;
            $unique[] = $row;
        }

        return $unique;
    }
}
