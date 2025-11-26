<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BedModel;
use App\Models\PatientModel;
use App\Models\AdmissionModel;

class Rooms extends BaseController
{
    protected BedModel $beds;
    protected PatientModel $patients;
    protected AdmissionModel $admissions;

    public function __construct()
    {
        $this->beds       = new BedModel();
        $this->patients   = new PatientModel();
        $this->admissions = new AdmissionModel();
    }

    /**
     * Return only wards that have at least one available bed, organized by category.
     * Organized by category: General Inpatient, Critical Care, Specialized
     */
    public function wards()
    {
        // Get available beds to determine which wards have availability
        $available = $this->getAvailableBedsBySlot();
        $availableWards = [];
        foreach ($available as $slot) {
            $ward = $slot['ward'] ?? '';
            if ($ward !== '') {
                $availableWards[$ward] = true;
            }
        }

        // Only include wards that have available beds
        $allWards = array_keys($availableWards);

        // Define all possible wards by category
        $generalInpatient = ['Pedia Ward', 'Male Ward', 'Female Ward'];
        $criticalCare = ['ICU', 'NICU', 'PICU', 'CCU', 'SICU', 'MICU'];
        $specialized = ['ED', 'ISO', 'PACU', 'LD', 'SDU', 'ONC', 'REHAB'];

        $categorized = [
            'General Inpatient' => [],
            'Critical Care Units' => [],
            'Specialized Patient Rooms' => []
        ];

        // Add General Inpatient wards that have available beds
        foreach ($generalInpatient as $wardName) {
            if (isset($availableWards[$wardName])) {
                $categorized['General Inpatient'][] = [
                    'name' => $wardName, 
                    'category' => 'General Inpatient'
                ];
            }
        }

        // Add Critical Care wards that have available beds
        foreach ($criticalCare as $wardName) {
            if (isset($availableWards[$wardName])) {
                $displayName = $this->getCriticalCareDisplayName($wardName);
                $categorized['Critical Care Units'][] = [
                    'name' => $displayName, 
                    'value' => $wardName, // Keep abbreviation for database
                    'category' => 'Critical Care Units'
                ];
            }
        }

        // Add Specialized wards that have available beds
        foreach ($specialized as $wardName) {
            if (isset($availableWards[$wardName])) {
                $displayName = $this->getSpecializedDisplayName($wardName);
                $categorized['Specialized Patient Rooms'][] = [
                    'name' => $displayName, 
                    'value' => $wardName, // Keep abbreviation for database
                    'category' => 'Specialized Patient Rooms'
                ];
            }
        }

        // Add any unknown wards to General Inpatient as fallback (only if available)
        foreach ($allWards as $wardName) {
            if (!in_array($wardName, $generalInpatient) && 
                !in_array($wardName, $criticalCare) && 
                !in_array($wardName, $specialized)) {
                $categorized['General Inpatient'][] = [
                    'name' => $wardName, 
                    'category' => 'General Inpatient'
                ];
            }
        }

        // Build output with categories
        $out = [
            'categories' => $categorized,
            'all' => $allWards // Keep flat list for backward compatibility
        ];

        return $this->response->setJSON($out);
    }

    /**
     * Get display name for Critical Care ward abbreviations
     */
    protected function getCriticalCareDisplayName(string $wardName): string
    {
        $mapping = [
            'ICU' => 'Intensive Care Unit',
            'NICU' => 'Neonatal Intensive Care Unit',
            'PICU' => 'Pediatric Intensive Care Unit',
            'CCU' => 'Coronary Care Unit',
            'SICU' => 'Surgical ICU',
            'MICU' => 'Medical ICU'
        ];
        return $mapping[$wardName] ?? $wardName;
    }

    /**
     * Get display name for Specialized ward abbreviations
     */
    protected function getSpecializedDisplayName(string $wardName): string
    {
        $mapping = [
            'ED' => 'Emergency Department',
            'ISO' => 'Isolation Room',
            'PACU' => 'Post-Anesthesia Care Unit',
            'LD' => 'Labor & Delivery Suite',
            'SDU' => 'Step-Down Unit',
            'ONC' => 'Oncology Unit',
            'REHAB' => 'Rehabilitation Unit'
        ];
        return $mapping[$wardName] ?? $wardName;
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

        // Get all bed IDs
        $bedIds = array_column($beds, 'id');

        // Load all current admissions (status = admitted) with assigned bed_id
        $occupiedBedIds = [];
        if (!empty($bedIds)) {
            $admissions = $this->admissions
                ->select('bed_id')
                ->where('status', 'admitted')
                ->where('bed_id IS NOT NULL')
                ->whereIn('bed_id', $bedIds)
                ->findAll();

            foreach ($admissions as $a) {
                if (isset($a['bed_id'])) {
                    $occupiedBedIds[(int)$a['bed_id']] = true;
                }
            }
        }

        $available = [];
        foreach ($beds as $row) {
            $ward = $row['ward'] ?? '';
            $room = $row['room'] ?? '';
            $bed  = $row['bed'] ?? '';
            $bedId = $row['id'] ?? null;
            
            if ($ward === '' || $room === '' || $bed === '' || !$bedId) {
                continue;
            }

            $storedStatus = $row['status'] ?? 'Available';
            $isOccupied = isset($occupiedBedIds[$bedId]);
            $effectiveStatus = $isOccupied ? 'Occupied' : $storedStatus;

            if ($effectiveStatus !== 'Available') {
                continue;
            }

            $available[] = [
                'id'   => $bedId,
                'ward' => $ward,
                'room' => $room,
                'bed'  => $bed,
            ];
        }

        return $available;
    }
}
