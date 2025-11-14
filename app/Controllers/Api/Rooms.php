<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BedModel;
use App\Models\PatientModel;

class Rooms extends BaseController
{
    protected BedModel $beds;
    protected PatientModel $patients;

    public function __construct()
    {
        $this->beds     = new BedModel();
        $this->patients = new PatientModel();
    }

    /**
     * Return wards that still have at least one effectively available bed.
     */
    public function wards()
    {
        $available = $this->getAvailableBedsBySlot();

        $wards = [];
        foreach ($available as $slot) {
            $ward = $slot['ward'] ?? '';
            if ($ward !== '') {
                $wards[$ward] = $ward;
            }
        }

        $out = [];
        foreach (array_values($wards) as $name) {
            $out[] = ['name' => $name];
        }

        return $this->response->setJSON($out);
    }

    /**
     * Return rooms in a given ward that still have at least one available bed.
     */
    public function rooms(string $ward)
    {
        $available = $this->getAvailableBedsBySlot();

        $rooms = [];
        foreach ($available as $slot) {
            if (($slot['ward'] ?? '') !== $ward) {
                continue;
            }
            $room = $slot['room'] ?? '';
            if ($room !== '') {
                $rooms[$room] = $room;
            }
        }

        $out = [];
        foreach (array_values($rooms) as $name) {
            $out[] = ['name' => $name];
        }

        return $this->response->setJSON($out);
    }

    /**
     * Return beds for a given ward and room that are still effectively available.
     */
    public function beds(string $ward, string $room)
    {
        $available = $this->getAvailableBedsBySlot();

        $beds = [];
        foreach ($available as $slot) {
            if (($slot['ward'] ?? '') !== $ward) {
                continue;
            }
            if (($slot['room'] ?? '') !== $room) {
                continue;
            }
            $beds[] = [
                'id'   => $slot['id'] ?? null,
                'name' => $slot['bed'] ?? '',
            ];
        }

        return $this->response->setJSON($beds);
    }

    /**
     * Compute effectively available beds by combining beds table with current inpatients.
     *
     * @return array<int, array<string,mixed>>
     */
    protected function getAvailableBedsBySlot(): array
    {
        // Load all configured beds
        $beds = $this->beds
            ->orderBy('ward', 'ASC')
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        if (empty($beds)) {
            return [];
        }

        // Load all current inpatients with assigned ward/room/bed
        $patients = $this->patients
            ->where('type', 'inpatient')
            ->where('ward IS NOT NULL')
            ->where('room IS NOT NULL')
            ->where('bed IS NOT NULL')
            ->findAll();

        $occupied = [];
        foreach ($patients as $p) {
            $ward = $p['ward'] ?? '';
            $room = $p['room'] ?? '';
            $bed  = $p['bed'] ?? '';
            if ($ward === '' || $room === '' || $bed === '') {
                continue;
            }
            $key = $ward . '|' . $room . '|' . $bed;
            $occupied[$key] = true;
        }

        $available = [];
        foreach ($beds as $row) {
            $ward = $row['ward'] ?? '';
            $room = $row['room'] ?? '';
            $bed  = $row['bed'] ?? '';
            if ($ward === '' || $room === '' || $bed === '') {
                continue;
            }

            $key = $ward . '|' . $room . '|' . $bed;
            $storedStatus = $row['status'] ?? 'Available';
            $effectiveStatus = isset($occupied[$key]) ? 'Occupied' : $storedStatus;

            if ($effectiveStatus !== 'Available') {
                continue;
            }

            $available[] = [
                'id'   => $row['id'] ?? null,
                'ward' => $ward,
                'room' => $room,
                'bed'  => $bed,
            ];
        }

        return $available;
    }
}
