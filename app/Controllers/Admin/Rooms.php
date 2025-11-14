<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\BedModel;

class Rooms extends BaseController
{
    protected PatientModel $patients;
    protected BedModel $beds;

    public function __construct()
    {
        $this->patients = new PatientModel();
        $this->beds     = new BedModel();
    }

    public function pediaWard()
    {
        return $this->renderWard('Pedia Ward');
    }

    public function maleWard()
    {
        return $this->renderWard('Male Ward');
    }

    public function femaleWard()
    {
        return $this->renderWard('Female Ward');
    }

    protected function renderWard(string $wardName)
    {
        // Load all inpatients assigned to this ward (actual occupancy)
        $patients = $this->patients
            ->where('type', 'inpatient')
            ->where('ward', $wardName)
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        // Map occupied slots: "room|bed" => patient
        $slots = [];
        foreach ($patients as $p) {
            $room = $p['room'] ?? '';
            $bed  = $p['bed'] ?? '';
            if ($room === '' || $bed === '') {
                continue;
            }
            $key = $room . '|' . $bed;
            $slots[$key] = $p;
        }

        // Load configured beds for this ward from beds table
        $beds = $this->beds
            ->where('ward', $wardName)
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        // Build rows for view: every bed row from DB
        $rows = [];
        foreach ($beds as $bedRow) {
            $room = $bedRow['room'] ?? '';
            $bed  = $bedRow['bed'] ?? '';
            if ($room === '' || $bed === '') {
                continue;
            }

            $key     = $room . '|' . $bed;
            $patient = $slots[$key] ?? null;

            // Effective status: Occupied if there is an inpatient, otherwise use bed.status
            $storedStatus   = $bedRow['status'] ?? 'Available';
            $effectiveStatus = $patient ? 'Occupied' : $storedStatus;

            $rows[] = [
                'bed_id'  => $bedRow['id'] ?? null,
                'room'    => $room,
                'bed'     => $bed,
                'patient' => $patient,
                'status'  => $effectiveStatus,
                'raw_status' => $storedStatus,
            ];
        }

        return view('Roles/admin/rooms/WardTemplate', [
            'wardName' => $wardName,
            'rows'     => $rows,
        ]);
    }

    public function updateBedStatus()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $bedId  = (int) $this->request->getPost('bed_id');
        $status = (string) $this->request->getPost('status');
        $ward   = (string) $this->request->getPost('ward');

        // Simple validation and whitelist of statuses
        $allowedStatuses = ['Available', 'Occupied'];
        if (!$bedId || !in_array($status, $allowedStatuses, true)) {
            return redirect()->back()->with('error', 'Invalid bed status update.');
        }

        $this->beds->update($bedId, ['status' => $status]);

        return redirect()->back()->with('success', 'Bed status updated successfully.');
    }
}
