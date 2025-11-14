<?php

namespace App\Controllers\Doctor;

use App\Controllers\BaseController;
use App\Models\PatientModel;

class PatientController extends BaseController
{
    /**
     * Show patient list for doctors (read-only).
     */
    public function view()
    {
        // Require logged-in doctor
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (session()->get('role') !== 'doctor') {
            return redirect()->to('/dashboard')
                ->with('error', 'Access denied. Doctor only.');
        }

        $patientModel = new PatientModel();

        // For now, show all active patients; view will split in/out-patients by type
        $patients = $patientModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title'     => 'Patient Records',
            'patients'  => $patients,
        ];

        return view('Roles/doctor/patients/view', $data);
    }
}


