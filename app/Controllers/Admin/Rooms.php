<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PatientModel;
use App\Models\BedModel;
use App\Models\AdmissionModel;

class Rooms extends BaseController
{
    protected PatientModel $patients;
    protected BedModel $beds;
    protected AdmissionModel $admissions;

    public function __construct()
    {
        $this->patients   = new PatientModel();
        $this->beds       = new BedModel();
        $this->admissions = new AdmissionModel();
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

    public function generalInpatient()
    {
        $filter = $this->request->getGet('filter') ?? 'all';
        $roomTypes = [
            'Private Suites (single occupancy)',
            'Semi-Private Ward (double occupancy)',
            'General Ward',
            'Medical-Surgical (Med-Surg) Unit'
        ];

        // Available ward filters (slug => metadata)
        $filterButtons = [
            'all' => ['label' => 'All', 'icon' => 'fas fa-list'],
            'pedia' => ['label' => 'Pedia Ward', 'icon' => 'fas fa-child', 'ward' => 'Pedia Ward'],
            'male' => ['label' => 'Male Ward', 'icon' => 'fas fa-mars', 'ward' => 'Male Ward'],
            'female' => ['label' => 'Female Ward', 'icon' => 'fas fa-venus', 'ward' => 'Female Ward'],
            'semi' => ['label' => 'Semi-Private Ward', 'icon' => 'fas fa-user-friends', 'ward' => 'Semi-Private Ward'],
            'private' => ['label' => 'Private Suites', 'icon' => 'fas fa-user-shield', 'ward' => 'Private Suites'],
        ];

        $wardFilter = null;
        $rows = [];
        $allWardsData = [];

        if ($filter !== 'all' && isset($filterButtons[$filter]) && isset($filterButtons[$filter]['ward'])) {
            $wardFilter = $filterButtons[$filter]['ward'];
            $rows = $this->getWardRows($wardFilter);
        } else {
            $filter = 'all';
            foreach ($filterButtons as $button) {
                if (!isset($button['ward'])) {
                    continue;
                }
                $wardName = $button['ward'];
                $allWardsData[$wardName] = $this->getWardRows($wardName);
            }
        }

        return view('Roles/admin/rooms/GeneralInpatient', [
            'roomTypes' => $roomTypes,
            'currentFilter' => $filter,
            'wardFilter' => $wardFilter,
            'rows' => $rows,
            'allWardsData' => $allWardsData,
            'filterButtons' => $filterButtons,
        ]);
    }

    protected function getWardRows(string $wardName): array
    {
        // Load configured beds for this ward from beds table
        $beds = $this->beds
            ->where('ward', $wardName)
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        // Get all bed IDs for this ward
        $bedIds = array_column($beds, 'id');

        // Load current occupants (from admissions) mapped by bed ID
        $patientsByBedId = $this->getBedOccupants($bedIds);

        // Build rows for view: every bed row from DB
        $rows = [];
        foreach ($beds as $bedRow) {
            $room = $bedRow['room'] ?? '';
            $bed  = $bedRow['bed'] ?? '';
            if ($room === '' || $bed === '') {
                continue;
            }

            $bedId = $bedRow['id'] ?? null;
            $patient = $bedId ? ($patientsByBedId[$bedId] ?? null) : null;

            // Add bed information to patient data for display (use full ward name)
            if ($patient) {
                $wardAbbr = $bedRow['ward'] ?? '';
                $patient['ward'] = $wardAbbr ? $this->getWardDisplayName($wardAbbr) : '';
                $patient['room'] = $bedRow['room'] ?? '';
                $patient['bed'] = $bedRow['bed'] ?? '';
            }

            // Effective status: Occupied if there is an inpatient, otherwise use bed.status
            $storedStatus   = $bedRow['status'] ?? 'Available';
            $effectiveStatus = $patient ? 'Occupied' : $storedStatus;

            $rows[] = [
                'bed_id'  => $bedId,
                'room'    => $room,
                'bed'     => $bed,
                'patient' => $patient,
                'status'  => $effectiveStatus,
                'raw_status' => $storedStatus,
            ];
        }

        return $rows;
    }

    protected function getBedOccupants(array $bedIds): array
    {
        $bedIds = array_filter(array_map('intval', $bedIds));
        if (empty($bedIds)) {
            return [];
        }

        $records = $this->admissions
            ->select('admission_details.bed_id AS admission_bed_id, patients.*')
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->where('admission_details.status', 'admitted')
            ->whereIn('admission_details.bed_id', $bedIds)
            ->orderBy('admission_details.created_at', 'DESC')
            ->findAll();

        $occupants = [];
        foreach ($records as $record) {
            $bedId = (int) ($record['admission_bed_id'] ?? $record['bed_id'] ?? 0);
            if (!$bedId) {
                continue;
            }

            $patient = $record;
            // Keep reference to original patient ID even if other selects override key names
            $patient['patient_id'] = $patient['id'] ?? null;
            $this->parseEmergencyContact($patient);
            $occupants[$bedId] = $patient;
        }

        return $occupants;
    }

    public function criticalCare()
    {
        $filter = $this->request->getGet('filter') ?? 'all';
        
        // Define ICU unit types and their ward name mappings
        $unitTypes = [
            'all' => 'All',
            'icu' => 'Intensive Care Unit',
            'nicu' => 'Neonatal Intensive Care Unit',
            'picu' => 'Pediatric Intensive Care Unit',
            'ccu' => 'Coronary Care Unit',
            'sicu' => 'Surgical ICU',
            'micu' => 'Medical ICU'
        ];

        // Map unit type filters to ward names in database
        $wardMapping = [
            'icu' => 'ICU',
            'nicu' => 'NICU',
            'picu' => 'PICU',
            'ccu' => 'CCU',
            'sicu' => 'SICU',
            'micu' => 'MICU'
        ];

        $rows = [];
        $unitFilter = null;
        
        if ($filter !== 'all' && isset($wardMapping[$filter])) {
            $unitFilter = $wardMapping[$filter];
            $rows = $this->getICURows($unitFilter);
        } elseif ($filter === 'all') {
            // Load all ICU units
            $allUnitsData = [];
            foreach ($wardMapping as $key => $wardName) {
                $allUnitsData[$unitTypes[$key]] = $this->getICURows($wardName);
            }
            return view('Roles/admin/rooms/CriticalCare', [
                'unitTypes' => $unitTypes,
                'currentFilter' => $filter,
                'unitFilter' => null,
                'rows' => [],
                'allUnitsData' => $allUnitsData,
            ]);
        }

        return view('Roles/admin/rooms/CriticalCare', [
            'unitTypes' => $unitTypes,
            'currentFilter' => $filter,
            'unitFilter' => $unitFilter,
            'rows' => $rows,
            'allUnitsData' => [],
        ]);
    }

    protected function getICURows(string $wardName): array
    {
        // Load configured beds for this ICU unit from beds table
        $beds = $this->beds
            ->where('ward', $wardName)
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        // Get all bed IDs for this ward
        $bedIds = array_column($beds, 'id');

        // Load current occupants (from admissions) mapped by bed ID
        $patientsByBedId = $this->getBedOccupants($bedIds);

        // Build rows for view: every bed row from DB
        $rows = [];
        foreach ($beds as $bedRow) {
            $room = $bedRow['room'] ?? '';
            $bed  = $bedRow['bed'] ?? '';
            if ($room === '' || $bed === '') {
                continue;
            }

            $bedId = $bedRow['id'] ?? null;
            $patient = $bedId ? ($patientsByBedId[$bedId] ?? null) : null;

            // Add bed information to patient data for display (use full ward name)
            if ($patient) {
                $wardAbbr = $bedRow['ward'] ?? '';
                $patient['ward'] = $wardAbbr ? $this->getWardDisplayName($wardAbbr) : '';
                $patient['room'] = $bedRow['room'] ?? '';
                $patient['bed'] = $bedRow['bed'] ?? '';
            }

            // Effective status: Occupied if there is an inpatient, otherwise use bed.status
            $storedStatus   = $bedRow['status'] ?? 'Available';
            $effectiveStatus = $patient ? 'Occupied' : $storedStatus;

            // Determine unit type name from ward name
            $unitTypeName = $this->getUnitTypeName($wardName);

            $rows[] = [
                'bed_id'  => $bedId,
                'room'    => $room,
                'bed'     => $bed,
                'patient' => $patient,
                'status'  => $effectiveStatus,
                'raw_status' => $storedStatus,
                'unit_type' => $unitTypeName,
            ];
        }

        return $rows;
    }

    protected function getUnitTypeName(string $wardName): string
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
     * Parse emergency contact data - migrate from JSON to separate columns if needed
     */
    protected function parseEmergencyContact(array &$patient): void
    {
        // If fields already exist, no parsing needed
        if (isset($patient['emergency_contact_person']) || isset($patient['emergency_contact_phone'])) {
            return;
        }

        // Fallback: try to parse from emergency_contact if it's JSON (for old data)
        if (isset($patient['emergency_contact']) && !empty($patient['emergency_contact'])) {
            $contactStr = trim($patient['emergency_contact']);
            
            // Check if it's JSON
            if (strpos($contactStr, '{') === 0) {
                $contactData = json_decode($contactStr, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($contactData)) {
                    $patient['emergency_contact_person'] = $contactData['person'] ?? null;
                    $patient['emergency_contact_relationship'] = $contactData['relationship'] ?? null;
                    $patient['emergency_contact_phone'] = $contactData['phone'] ?? null;
                } else {
                    // Malformed JSON - treat as phone
                    if (preg_match('/^\+?\d/', $contactStr)) {
                        $patient['emergency_contact_phone'] = $contactStr;
                    }
                }
            } else {
                // Not JSON - treat as phone number
                if (preg_match('/^\+?\d/', $contactStr)) {
                    $patient['emergency_contact_phone'] = $contactStr;
                }
            }
        }
    }

    protected function getWardDisplayName(string $wardName): string
    {
        // Critical Care Units
        $criticalCare = [
            'ICU' => 'Intensive Care Unit',
            'NICU' => 'Neonatal Intensive Care Unit',
            'PICU' => 'Pediatric Intensive Care Unit',
            'CCU' => 'Coronary Care Unit',
            'SICU' => 'Surgical ICU',
            'MICU' => 'Medical ICU'
        ];
        
        // Specialized Patient Rooms
        $specialized = [
            'ED' => 'Emergency Department',
            'ISO' => 'Isolation Room',
            'PACU' => 'Post-Anesthesia Care Unit',
            'LD' => 'Labor & Delivery Suite',
            'SDU' => 'Step-Down Unit',
            'ONC' => 'Oncology Unit',
            'REHAB' => 'Rehabilitation Unit'
        ];
        
        // General Inpatient (already full names, but keep for consistency)
        $generalInpatient = [
            'Pedia Ward' => 'Pedia Ward',
            'Male Ward' => 'Male Ward',
            'Female Ward' => 'Female Ward'
        ];
        
        // Check all mappings
        if (isset($criticalCare[$wardName])) {
            return $criticalCare[$wardName];
        }
        if (isset($specialized[$wardName])) {
            return $specialized[$wardName];
        }
        if (isset($generalInpatient[$wardName])) {
            return $generalInpatient[$wardName];
        }
        
        // Return as-is if no mapping found
        return $wardName;
    }

    public function specialized()
    {
        $filter = $this->request->getGet('filter') ?? 'all';
        
        // Define specialized room types and their ward name mappings
        $roomTypes = [
            'all' => 'All',
            'ed' => 'Emergency Department',
            'isolation' => 'Isolation Room',
            'pacu' => 'Post-Anesthesia Care Unit',
            'ld' => 'Labor & Delivery Suite',
            'sdu' => 'Step-Down Unit',
            'oncology' => 'Oncology Unit',
            'rehab' => 'Rehabilitation Unit'
        ];

        // Map room type filters to ward names in database
        $wardMapping = [
            'ed' => 'ED',
            'isolation' => 'ISO',
            'pacu' => 'PACU',
            'ld' => 'LD',
            'sdu' => 'SDU',
            'oncology' => 'ONC',
            'rehab' => 'REHAB'
        ];

        $rows = [];
        $roomFilter = null;
        
        if ($filter !== 'all' && isset($wardMapping[$filter])) {
            $roomFilter = $wardMapping[$filter];
            $rows = $this->getSpecializedRows($roomFilter);
        } elseif ($filter === 'all') {
            // Load all specialized rooms
            $allRoomsData = [];
            foreach ($wardMapping as $key => $wardName) {
                $allRoomsData[$roomTypes[$key]] = $this->getSpecializedRows($wardName);
            }
            return view('Roles/admin/rooms/Specialized', [
                'roomTypes' => $roomTypes,
                'currentFilter' => $filter,
                'roomFilter' => null,
                'rows' => [],
                'allRoomsData' => $allRoomsData,
            ]);
        }

        return view('Roles/admin/rooms/Specialized', [
            'roomTypes' => $roomTypes,
            'currentFilter' => $filter,
            'roomFilter' => $roomFilter,
            'rows' => $rows,
            'allRoomsData' => [],
        ]);
    }

    protected function getSpecializedRows(string $wardName): array
    {
        // Load configured beds for this specialized room from beds table
        $beds = $this->beds
            ->where('ward', $wardName)
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        // Get all bed IDs for this ward
        $bedIds = array_column($beds, 'id');

        // Load current occupants (from admissions) mapped by bed ID
        $patientsByBedId = $this->getBedOccupants($bedIds);

        // Build rows for view: every bed row from DB
        $rows = [];
        foreach ($beds as $bedRow) {
            $room = $bedRow['room'] ?? '';
            $bed  = $bedRow['bed'] ?? '';
            if ($room === '' || $bed === '') {
                continue;
            }

            $bedId = $bedRow['id'] ?? null;
            $patient = $bedId ? ($patientsByBedId[$bedId] ?? null) : null;

            // Add bed information to patient data for display (use full ward name)
            if ($patient) {
                $wardAbbr = $bedRow['ward'] ?? '';
                $patient['ward'] = $wardAbbr ? $this->getWardDisplayName($wardAbbr) : '';
                $patient['room'] = $bedRow['room'] ?? '';
                $patient['bed'] = $bedRow['bed'] ?? '';
                // Parse emergency contact JSON if present
                $this->parseEmergencyContact($patient);
            }

            // Effective status: Occupied if there is an inpatient, otherwise use bed.status
            $storedStatus   = $bedRow['status'] ?? 'Available';
            $effectiveStatus = $patient ? 'Occupied' : $storedStatus;

            // Determine room type name from ward name
            $roomTypeName = $this->getSpecializedRoomTypeName($wardName);

            $rows[] = [
                'bed_id'  => $bedId,
                'room'    => $room,
                'bed'     => $bed,
                'patient' => $patient,
                'status'  => $effectiveStatus,
                'raw_status' => $storedStatus,
                'room_type' => $roomTypeName,
            ];
        }

        return $rows;
    }

    protected function getSpecializedRoomTypeName(string $wardName): string
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

    protected function renderWard(string $wardName)
    {
        // Load configured beds for this ward from beds table
        $beds = $this->beds
            ->where('ward', $wardName)
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        // Get all bed IDs for this ward
        $bedIds = array_column($beds, 'id');

        // Load current occupants (from admissions) mapped by bed ID
        $patientsByBedId = $this->getBedOccupants($bedIds);

        // Build rows for view: every bed row from DB
        $rows = [];
        foreach ($beds as $bedRow) {
            $room = $bedRow['room'] ?? '';
            $bed  = $bedRow['bed'] ?? '';
            if ($room === '' || $bed === '') {
                continue;
            }

            $bedId = $bedRow['id'] ?? null;
            $patient = $bedId ? ($patientsByBedId[$bedId] ?? null) : null;

            // Add bed information to patient data for display (use full ward name)
            if ($patient) {
                $wardAbbr = $bedRow['ward'] ?? '';
                $patient['ward'] = $wardAbbr ? $this->getWardDisplayName($wardAbbr) : '';
                $patient['room'] = $bedRow['room'] ?? '';
                $patient['bed'] = $bedRow['bed'] ?? '';
                // Parse emergency contact JSON if present
                $this->parseEmergencyContact($patient);
            }

            // Effective status: Occupied if there is an inpatient, otherwise use bed.status
            $storedStatus   = $bedRow['status'] ?? 'Available';
            $effectiveStatus = $patient ? 'Occupied' : $storedStatus;

            $rows[] = [
                'bed_id'  => $bedId,
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
