<?php

namespace App\Controllers;

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

    /**
     * Get role-based view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleMap = [
            'admin' => 'admin',
            'receptionist' => 'admin',
            'nurse' => 'admin', // Nurses use admin views (unified)
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/rooms/{$viewName}";
    }

    public function pediaWard()
    {
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        return $this->renderWard('Pedia Ward');
    }

    public function maleWard()
    {
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        return $this->renderWard('Male Ward');
    }

    public function femaleWard()
    {
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        return $this->renderWard('Female Ward');
    }

    public function generalInpatient()
    {
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        
        $filter = $this->request->getGet('filter') ?? 'all';
        
        // Define room types for General Inpatient Rooms
        $roomTypes = [
            'Private Room',
            'Semi-Private Room',
            'General Ward',
            'Medical-Surgical (Med-Surg) Unit'
        ];

        // Filter by ward if specified
        $wardFilter = null;
        $rows = [];
        $allWardsData = [];
        
        if ($filter === 'pedia') {
            $wardFilter = 'Pedia Ward';
            $rows = $this->getWardRows($wardFilter);
        } elseif ($filter === 'male') {
            $wardFilter = 'Male Ward';
            $rows = $this->getWardRows($wardFilter);
        } elseif ($filter === 'female') {
            $wardFilter = 'Female Ward';
            $rows = $this->getWardRows($wardFilter);
        } elseif ($filter === 'semi-private') {
            $wardFilter = 'Semi-Private Ward';
            $rows = $this->getWardRows($wardFilter);
        } elseif ($filter === 'private') {
            $wardFilter = 'Private Suites';
            $rows = $this->getWardRows($wardFilter);
        } elseif ($filter === 'all') {
            // Load all wards when "All" is selected
            $allWardsData = [
                'Pedia Ward' => $this->getWardRows('Pedia Ward'),
                'Male Ward' => $this->getWardRows('Male Ward'),
                'Female Ward' => $this->getWardRows('Female Ward'),
                'Semi-Private Ward' => $this->getWardRows('Semi-Private Ward'),
                'Private Suites' => $this->getWardRows('Private Suites'),
            ];
        }

        // Define filter buttons for the view
        $filterButtons = [
            'all' => ['label' => 'All', 'icon' => 'fas fa-list'],
            'pedia' => ['label' => 'Pedia Ward', 'icon' => 'fas fa-child'],
            'male' => ['label' => 'Male Ward', 'icon' => 'fas fa-mars'],
            'female' => ['label' => 'Female Ward', 'icon' => 'fas fa-venus'],
            'semi-private' => ['label' => 'Semi-Private Ward', 'icon' => 'fas fa-users'],
            'private' => ['label' => 'Private Suites', 'icon' => 'fas fa-user'],
        ];

        return view($this->getRoleViewPath('GeneralInpatient'), [
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
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        
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
            return view($this->getRoleViewPath('CriticalCare'), [
                'unitTypes' => $unitTypes,
                'currentFilter' => $filter,
                'unitFilter' => null,
                'rows' => [],
                'allUnitsData' => $allUnitsData,
            ]);
        }

        return view($this->getRoleViewPath('CriticalCare'), [
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
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        
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
            return view($this->getRoleViewPath('Specialized'), [
                'roomTypes' => $roomTypes,
                'currentFilter' => $filter,
                'roomFilter' => null,
                'rows' => [],
                'allRoomsData' => $allRoomsData,
            ]);
        }

        return view($this->getRoleViewPath('Specialized'), [
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

        return view($this->getRoleViewPath('WardTemplate'), [
            'wardName' => $wardName,
            'rows'     => $rows,
        ]);
    }

    public function updateBedStatus()
    {
        // Only admin and nurse can update bed status
        $this->requireRole(['admin', 'nurse']);
        
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

    // API methods for AJAX requests - returns only available beds
    /**
     * Return only wards that have at least one available bed, organized by category.
     * Organized by category: General Inpatient, Critical Care, Specialized
     */
    public function apiWards()
    {
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        
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
                $displayName = $this->getUnitTypeName($wardName);
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
                $displayName = $this->getSpecializedRoomTypeName($wardName);
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
     * Return rooms in a given ward that still have at least one available bed.
     */
    public function apiRooms($ward)
    {
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        
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
    public function apiBeds($ward, $room)
    {
        $this->requireRole(['admin', 'receptionist', 'nurse']);
        
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

