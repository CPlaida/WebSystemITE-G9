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

    private const ROLE_VIEW_MAP = [
        'admin' => 'admin',
        'receptionist' => 'admin',
        'nurse' => 'admin',
    ];

    private const ACCESS_ROLES = ['admin', 'receptionist', 'nurse'];

    private const GENERAL_ROOM_TYPES = [
        'Private Room',
        'Semi-Private Room',
        'General Ward',
        'Medical-Surgical (Med-Surg) Unit',
    ];

    private const GENERAL_FILTERS = [
        'pedia' => ['ward' => 'Pedia Ward', 'label' => 'Pedia Ward', 'icon' => 'fas fa-child'],
        'male' => ['ward' => 'Male Ward', 'label' => 'Male Ward', 'icon' => 'fas fa-mars'],
        'female' => ['ward' => 'Female Ward', 'label' => 'Female Ward', 'icon' => 'fas fa-venus'],
        'semi-private' => ['ward' => 'Semi-Private Ward', 'label' => 'Semi-Private Ward', 'icon' => 'fas fa-users'],
        'private' => ['ward' => 'Private Suites', 'label' => 'Private Suites', 'icon' => 'fas fa-user'],
    ];

    private const CRITICAL_UNITS = [
        'icu' => ['ward' => 'ICU', 'label' => 'Intensive Care Unit'],
        'nicu' => ['ward' => 'NICU', 'label' => 'Neonatal Intensive Care Unit'],
        'picu' => ['ward' => 'PICU', 'label' => 'Pediatric Intensive Care Unit'],
    ];

    private const SPECIALIZED_ROOMS = [
        'ed' => ['ward' => 'ED', 'label' => 'Emergency Department'],
        'isolation' => ['ward' => 'ISO', 'label' => 'Isolation Room'],
        'ld' => ['ward' => 'LD', 'label' => 'Labor & Delivery Suite'],
        'sdu' => ['ward' => 'SDU', 'label' => 'Step-Down Unit'],
    ];

    public function __construct()
    {
        $this->patients   = new PatientModel();
        $this->beds       = new BedModel();
        $this->admissions = new AdmissionModel();
    }

    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleFolder = self::ROLE_VIEW_MAP[$role] ?? 'admin';
        return "Roles/{$roleFolder}/rooms/{$viewName}";
    }

    public function pediaWard()
    {
        $this->requireRole(self::ACCESS_ROLES);
        return $this->renderWard('Pedia Ward');
    }

    public function maleWard()
    {
        $this->requireRole(self::ACCESS_ROLES);
        return $this->renderWard('Male Ward');
    }

    public function femaleWard()
    {
        $this->requireRole(self::ACCESS_ROLES);
        return $this->renderWard('Female Ward');
    }

    public function generalInpatient()
    {
        $this->requireRole(self::ACCESS_ROLES);
        
        $filter = $this->request->getGet('filter') ?? 'all';
        
        $roomTypes = self::GENERAL_ROOM_TYPES;

        $wardFilter = null;
        $rows = [];
        $allWardsData = [];

        if ($filter === 'all') {
            foreach (self::GENERAL_FILTERS as $config) {
                $allWardsData[$config['ward']] = $this->getWardRows($config['ward']);
            }
        } elseif (isset(self::GENERAL_FILTERS[$filter])) {
            $wardFilter = self::GENERAL_FILTERS[$filter]['ward'];
            $rows = $this->getWardRows($wardFilter);
        }

        $filterButtons = [
            'all' => ['label' => 'All', 'icon' => 'fas fa-list'],
        ];
        foreach (self::GENERAL_FILTERS as $key => $config) {
            $filterButtons[$key] = [
                'label' => $config['label'],
                'icon' => $config['icon'],
            ];
        }

        return view($this->getRoleViewPath('GeneralInpatient'), [
            'roomTypes' => $roomTypes,
            'currentFilter' => $filter,
            'wardFilter' => $wardFilter,
            'rows' => $rows,
            'allWardsData' => $allWardsData,
            'filterButtons' => $filterButtons,
        ]);
    }

    protected function getWardRows(string $wardName, ?callable $rowDecorator = null): array
    {
        $beds = $this->beds
            ->where('ward', $wardName)
            ->orderBy('room', 'ASC')
            ->orderBy('bed', 'ASC')
            ->findAll();

        // Deduplicate beds by ward+room+bed combination to prevent duplicate entries
        // This handles cases where the same bed exists multiple times with different IDs
        $uniqueBeds = [];
        $processedKeys = [];
        foreach ($beds as $bedRow) {
            $room = trim($bedRow['room'] ?? '');
            $bed = trim($bedRow['bed'] ?? '');
            $ward = trim($bedRow['ward'] ?? '');
            
            if ($room === '' || $bed === '' || $ward === '') {
                continue;
            }
            
            // Create a unique key from ward+room+bed combination
            $uniqueKey = strtolower($ward . '|' . $room . '|' . $bed);
            
            // Only keep the first occurrence (or the one with the highest ID if we want the latest)
            if (!isset($processedKeys[$uniqueKey])) {
                $uniqueBeds[] = $bedRow;
                $processedKeys[$uniqueKey] = true;
            }
        }
        $beds = $uniqueBeds;

        $bedIds = array_column($beds, 'id');

        $patientsByBedId = $this->getBedOccupants($bedIds);

        $rows = [];
        $processedKeys = []; // Track processed ward+room+bed combinations to prevent duplicates
        foreach ($beds as $bedRow) {
            $room = trim($bedRow['room'] ?? '');
            $bed  = trim($bedRow['bed'] ?? '');
            $ward = trim($bedRow['ward'] ?? '');
            
            if ($room === '' || $bed === '' || $ward === '') {
                continue;
            }

            // Create a unique key from ward+room+bed combination
            $uniqueKey = strtolower($ward . '|' . $room . '|' . $bed);
            
            // Skip if we've already processed this ward+room+bed combination
            if (isset($processedKeys[$uniqueKey])) {
                continue;
            }
            
            $bedId = (int)($bedRow['id'] ?? 0);
            $patient = $bedId ? ($patientsByBedId[$bedId] ?? null) : null;

            if ($patient) {
                $wardAbbr = $bedRow['ward'] ?? '';
                $patient['ward'] = $wardAbbr ? $this->getWardDisplayName($wardAbbr) : '';
                $patient['room'] = $bedRow['room'] ?? '';
                $patient['bed'] = $bedRow['bed'] ?? '';
                $this->parseEmergencyContact($patient);
            }

            $storedStatus   = $bedRow['status'] ?? 'Available';
            // If patient is found OR bed status is Occupied, mark as Occupied
            $effectiveStatus = ($patient || strtolower($storedStatus) === 'occupied') ? 'Occupied' : $storedStatus;

            $row = [
                'bed_id'  => $bedId,
                'room'    => $room,
                'bed'     => $bed,
                'patient' => $patient,
                'status'  => $effectiveStatus,
                'raw_status' => $storedStatus,
            ];

            if ($rowDecorator) {
                $extra = $rowDecorator($wardName);
                if (is_array($extra) && !empty($extra)) {
                    $row = array_merge($row, $extra);
                }
            }

            $rows[] = $row;
            $processedKeys[$uniqueKey] = true; // Mark as processed
        }

        return $rows;
    }

    protected function getBedOccupants(array $bedIds): array
    {
        $bedIds = array_filter(array_map('intval', $bedIds));
        if (empty($bedIds)) {
            return [];
        }

        // Query for active admissions with the given bed IDs
        // Use database query builder directly to ensure proper table name handling
        $db = \Config\Database::connect();
        $builder = $db->table('admission_details');
        $builder->select('admission_details.bed_id AS admission_bed_id, admission_details.status AS admission_status, admission_details.patient_id AS admission_patient_id, patients.*')
            ->join('patients', 'patients.id = admission_details.patient_id', 'left')
            ->where('admission_details.status', 'admitted')
            ->whereIn('admission_details.bed_id', $bedIds)
            ->where('admission_details.bed_id IS NOT NULL')
            ->orderBy('admission_details.created_at', 'DESC');
        
        $records = $builder->get()->getResultArray();

        $occupants = [];
        foreach ($records as $record) {
            $bedId = (int) ($record['admission_bed_id'] ?? $record['bed_id'] ?? 0);
            if (!$bedId) {
                continue;
            }

            $patient = $record;
            $patient['patient_id'] = $patient['id'] ?? null;
            $this->parseEmergencyContact($patient);
            $occupants[$bedId] = $patient;
        }

        return $occupants;
    }

    public function criticalCare()
    {
        $this->requireRole(self::ACCESS_ROLES);
        
        $filter = $this->request->getGet('filter') ?? 'all';
        
        $unitTypes = ['all' => 'All'];
        foreach (self::CRITICAL_UNITS as $key => $config) {
            $unitTypes[$key] = $config['label'];
        }

        $filterButtons = [
            'all' => ['label' => 'All', 'icon' => 'fas fa-list'],
        ];
        foreach (self::CRITICAL_UNITS as $key => $config) {
            $filterButtons[$key] = [
                'label' => $config['label'],
                'icon' => 'fas fa-heartbeat',
            ];
        }

        $rows = [];
        $unitFilter = null;
        $decorator = fn(string $ward): array => [
            'unit_type' => $this->getUnitTypeName($ward),
        ];
        
        if ($filter !== 'all' && isset(self::CRITICAL_UNITS[$filter])) {
            $unitFilter = self::CRITICAL_UNITS[$filter]['ward'];
            $rows = $this->getWardRows($unitFilter, $decorator);
        } elseif ($filter === 'all') {
            $allUnitsData = [];
            foreach (self::CRITICAL_UNITS as $config) {
                $allUnitsData[$config['label']] = $this->getWardRows($config['ward'], $decorator);
            }
            return view($this->getRoleViewPath('CriticalCare'), [
                'unitTypes' => $unitTypes,
                'currentFilter' => $filter,
                'unitFilter' => null,
                'rows' => [],
                'allUnitsData' => $allUnitsData,
                'filterButtons' => $filterButtons,
            ]);
        }

        return view($this->getRoleViewPath('CriticalCare'), [
            'unitTypes' => $unitTypes,
            'currentFilter' => $filter,
            'unitFilter' => $unitFilter,
            'rows' => $rows,
            'allUnitsData' => [],
            'filterButtons' => $filterButtons,
        ]);
    }

    protected function getUnitTypeName(string $wardName): string
    {
        $mapping = [
            'ICU' => 'Intensive Care Unit',
            'NICU' => 'Neonatal Intensive Care Unit',
            'PICU' => 'Pediatric Intensive Care Unit',
        ];
        return $mapping[$wardName] ?? $wardName;
    }

    protected function parseEmergencyContact(array &$patient): void
    {
        if (isset($patient['emergency_contact_person']) || isset($patient['emergency_contact_phone'])) {
            return;
        }

        $contact = trim((string) ($patient['emergency_contact'] ?? ''));
        if ($contact === '') {
            return;
        }

        if (strpos($contact, '{') === 0) {
            $data = json_decode($contact, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $patient['emergency_contact_person'] = $data['person'] ?? null;
                $patient['emergency_contact_relationship'] = $data['relationship'] ?? null;
                $patient['emergency_contact_phone'] = $data['phone'] ?? null;
                return;
            }
        }

        if (preg_match('/^\+?\d/', $contact)) {
            $patient['emergency_contact_phone'] = $contact;
        }
    }

    protected function getWardDisplayName(string $wardName): string
    {
        $criticalCare = [
            'ICU' => 'Intensive Care Unit',
            'NICU' => 'Neonatal Intensive Care Unit',
            'PICU' => 'Pediatric Intensive Care Unit',
        ];

        $specialized = [
            'ED' => 'Emergency Department',
            'ISO' => 'Isolation Room',
            'LD' => 'Labor & Delivery Suite',
            'SDU' => 'Step-Down Unit',
        ];

        $generalInpatient = [
            'Pedia Ward' => 'Pedia Ward',
            'Male Ward' => 'Male Ward',
            'Female Ward' => 'Female Ward'
        ];

        if (isset($criticalCare[$wardName])) {
            return $criticalCare[$wardName];
        }
        if (isset($specialized[$wardName])) {
            return $specialized[$wardName];
        }
        if (isset($generalInpatient[$wardName])) {
            return $generalInpatient[$wardName];
        }

    return $wardName;
    }

    public function specialized()
    {
        $this->requireRole(self::ACCESS_ROLES);
        
        $filter = $this->request->getGet('filter') ?? 'all';
        
        $roomTypes = ['all' => 'All'];
        foreach (self::SPECIALIZED_ROOMS as $key => $config) {
            $roomTypes[$key] = $config['label'];
        }

        $filterButtons = [
            'all' => ['label' => 'All', 'icon' => 'fas fa-list'],
        ];
        $specializedIcons = [
            'ed' => 'fas fa-ambulance',
            'isolation' => 'fas fa-shield-virus',
            'ld' => 'fas fa-baby',
            'sdu' => 'fas fa-procedures',
        ];
        foreach (self::SPECIALIZED_ROOMS as $key => $config) {
            $filterButtons[$key] = [
                'label' => $config['label'],
                'icon' => $specializedIcons[$key] ?? 'fas fa-door-open',
            ];
        }

        $rows = [];
        $roomFilter = null;
        $decorator = fn(string $ward): array => [
            'room_type' => $this->getSpecializedRoomTypeName($ward),
        ];
        
        if ($filter !== 'all' && isset(self::SPECIALIZED_ROOMS[$filter])) {
            $roomFilter = self::SPECIALIZED_ROOMS[$filter]['ward'];
            $rows = $this->getWardRows($roomFilter, $decorator);
        } elseif ($filter === 'all') {
            $allRoomsData = [];
            foreach (self::SPECIALIZED_ROOMS as $config) {
                $allRoomsData[$config['label']] = $this->getWardRows($config['ward'], $decorator);
            }
            return view($this->getRoleViewPath('Specialized'), [
                'roomTypes' => $roomTypes,
                'currentFilter' => $filter,
                'roomFilter' => null,
                'rows' => [],
                'allRoomsData' => $allRoomsData,
                'filterButtons' => $filterButtons,
            ]);
        }

        return view($this->getRoleViewPath('Specialized'), [
            'roomTypes' => $roomTypes,
            'currentFilter' => $filter,
            'roomFilter' => $roomFilter,
            'rows' => $rows,
            'allRoomsData' => [],
            'filterButtons' => $filterButtons,
        ]);
    }

    protected function getSpecializedRoomTypeName(string $wardName): string
    {
        $mapping = [
            'ED' => 'Emergency Department',
            'ISO' => 'Isolation Room',
            'LD' => 'Labor & Delivery Suite',
            'SDU' => 'Step-Down Unit',
        ];
        return $mapping[$wardName] ?? $wardName;
    }

    protected function renderWard(string $wardName)
    {
        $rows = $this->getWardRows($wardName);

        return view($this->getRoleViewPath('WardTemplate'), [
            'wardName' => $wardName,
            'rows'     => $rows,
        ]);
    }

    public function updateBedStatus()
    {
        $this->requireRole(['admin', 'nurse']);
        
        if ($this->request->getMethod() !== 'post') {
            return redirect()->back();
        }

        $bedId  = (int) $this->request->getPost('bed_id');
        $status = (string) $this->request->getPost('status');
        $ward   = (string) $this->request->getPost('ward');

        $allowedStatuses = ['Available', 'Occupied'];
        if (!$bedId || !in_array($status, $allowedStatuses, true)) {
            return redirect()->back()->with('error', 'Invalid bed status update.');
        }

        $this->beds->update($bedId, ['status' => $status]);

        return redirect()->back()->with('success', 'Bed status updated successfully.');
    }

    public function apiWards()
    {
        $this->requireRole(self::ACCESS_ROLES);
        
        $patientId = $this->request->getGet('patient_id');
        $patientAge = null;
        $patientAgeDays = null;
        $patientGender = null;
        
        // If patient_id is provided, load patient data and calculate age
        if ($patientId) {
            $patient = $this->patients->find($patientId);
            if ($patient) {
                $dateOfBirth = $patient['date_of_birth'] ?? null;
                $ageData = $this->calculateAge($dateOfBirth);
                $patientAge = $ageData['years'];
                $patientAgeDays = $ageData['days'];
                $patientGender = $patient['gender'] ?? null;
            }
        }
        
        $available = $this->getAvailableBedsBySlot();
        $availableWards = [];
        foreach ($available as $slot) {
            $ward = $slot['ward'] ?? '';
            if ($ward !== '') {
                $availableWards[$ward] = true;
            }
        }

        $allWards = array_keys($availableWards);

        // Filter wards based on patient age/gender if patient is provided
        if ($patientId) {
            $filteredWards = [];
            $filteredWardsMap = [];
            foreach ($allWards as $wardName) {
                if ($this->isWardValidForPatient($wardName, $patientAge, $patientAgeDays, $patientGender)) {
                    $filteredWards[] = $wardName;
                    $filteredWardsMap[$wardName] = true;
                }
            }
            $allWards = $filteredWards;
            $availableWards = $filteredWardsMap;
        }

        $generalInpatient = array_column(self::GENERAL_FILTERS, 'ward');
        $criticalCare = array_column(self::CRITICAL_UNITS, 'ward');
        $specialized = array_column(self::SPECIALIZED_ROOMS, 'ward');

        $categorized = [
            'General Inpatient' => [],
            'Critical Care Units' => [],
            'Specialized Patient Rooms' => []
        ];

        foreach ($generalInpatient as $wardName) {
            if (isset($availableWards[$wardName])) {
                $categorized['General Inpatient'][] = [
                    'name' => $wardName, 
                    'category' => 'General Inpatient'
                ];
            }
        }

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

        $out = [
            'categories' => $categorized,
            'all' => $allWards // Keep flat list for backward compatibility
        ];

        return $this->response->setJSON($out);
    }

    /**
     * Check if a ward is valid for a patient based on age and gender
     * 
     * @param string $ward Ward name
     * @param int|null $ageInYears Patient age in years (null if unknown)
     * @param int|null $ageInDays Patient age in days (for NICU - 0-28 days)
     * @param string|null $gender Patient gender (male/female)
     * @return bool True if ward is valid for patient
     */
    protected function isWardValidForPatient(string $ward, ?int $ageInYears, ?int $ageInDays, ?string $gender): bool
    {
        $wardNormalized = strtoupper(trim($ward));
        $genderLower = $gender ? strtolower(trim($gender)) : null;
        
        // Pedia Ward: Age 0-12 years, Male & Female
        if ($wardNormalized === 'PEDIA WARD') {
            return ($ageInYears !== null && $ageInYears >= 0 && $ageInYears <= 12);
        }
        
        // NICU: Age 0-28 days only, Male & Female
        if ($wardNormalized === 'NICU') {
            return ($ageInDays !== null && $ageInDays >= 0 && $ageInDays <= 28);
        }
        
        // PICU: Age 0-12 years, Male & Female
        if ($wardNormalized === 'PICU') {
            return ($ageInYears !== null && $ageInYears >= 0 && $ageInYears <= 12);
        }
        
        // Female Ward: Age 13+ years, Female only
        if ($wardNormalized === 'FEMALE WARD') {
            return ($ageInYears !== null && $ageInYears >= 13 && $genderLower === 'female');
        }
        
        // Male Ward: Age 13+ years, Male only
        if ($wardNormalized === 'MALE WARD') {
            return ($ageInYears !== null && $ageInYears >= 13 && $genderLower === 'male');
        }
        
        // Semi-Private Ward: Age 13+ years, Male & Female
        if ($wardNormalized === 'SEMI-PRIVATE WARD') {
            return ($ageInYears !== null && $ageInYears >= 13);
        }
        
        // Private Suites: Age 13+ years, Male & Female
        if ($wardNormalized === 'PRIVATE SUITES') {
            return ($ageInYears !== null && $ageInYears >= 13);
        }
        
        // ICU: All ages, Male & Female
        if ($wardNormalized === 'ICU') {
            return true;
        }
        
        // Emergency Department: All ages, Male & Female
        if ($wardNormalized === 'ED') {
            return true;
        }
        
        // Isolation Room: All ages, Male & Female
        if ($wardNormalized === 'ISO') {
            return true;
        }
        
        // Labor & Delivery Suite: Age 13+ years, Female only
        if ($wardNormalized === 'LD') {
            return ($ageInYears !== null && $ageInYears >= 13 && $genderLower === 'female');
        }
        
        // Step-Down Unit: All ages, Male & Female
        if ($wardNormalized === 'SDU') {
            return true;
        }
        
        // Default: allow if we don't have specific rules (backward compatibility)
        return true;
    }

    /**
     * Calculate age in years and days from date of birth
     * 
     * @param string|null $dateOfBirth Date of birth (Y-m-d format)
     * @return array{years: int|null, days: int|null}
     */
    protected function calculateAge(?string $dateOfBirth): array
    {
        if (!$dateOfBirth) {
            return ['years' => null, 'days' => null];
        }
        
        try {
            $dob = new \DateTime($dateOfBirth);
            $now = new \DateTime();
            $diff = $now->diff($dob);
            
            $years = (int)$diff->y;
            $days = (int)$diff->days;
            
            return ['years' => $years, 'days' => $days];
        } catch (\Exception $e) {
            return ['years' => null, 'days' => null];
        }
    }

    /**
     * Return rooms in a given ward that still have at least one available bed.
     * Optionally filters by patient age and gender if patient_id is provided.
     */
    public function apiRooms($ward)
    {
        $this->requireRole(self::ACCESS_ROLES);
        
        $patientId = $this->request->getGet('patient_id');
        $patientAge = null;
        $patientAgeDays = null;
        $patientGender = null;
        
        // If patient_id is provided, load patient data and calculate age
        if ($patientId) {
            $patient = $this->patients->find($patientId);
            if ($patient) {
                $dateOfBirth = $patient['date_of_birth'] ?? null;
                $ageData = $this->calculateAge($dateOfBirth);
                $patientAge = $ageData['years'];
                $patientAgeDays = $ageData['days'];
                $patientGender = $patient['gender'] ?? null;
            }
        }
        
        // Check if ward is valid for patient (if patient info is available)
        if ($patientId) {
            $isValid = $this->isWardValidForPatient($ward, $patientAge, $patientAgeDays, $patientGender);
            log_message('debug', "Ward validation: ward={$ward}, patient_id={$patientId}, age_years={$patientAge}, age_days={$patientAgeDays}, gender={$patientGender}, valid=" . ($isValid ? 'yes' : 'no'));
            
            if (!$isValid) {
                // Ward is not valid for this patient, return empty array
                return $this->response->setJSON([]);
            }
        }
        
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
        $processedKeys = []; // Track processed ward+room+bed combinations to prevent duplicates
        
        foreach ($beds as $row) {
            $ward = $row['ward'] ?? '';
            $room = $row['room'] ?? '';
            $bed  = $row['bed'] ?? '';
            $bedId = $row['id'] ?? null;
            
            if ($ward === '' || $room === '' || $bed === '' || !$bedId) {
                continue;
            }

            // Create a unique key from ward+room+bed combination
            $uniqueKey = strtolower($ward . '|' . $room . '|' . $bed);
            
            // Skip if we've already processed this ward+room+bed combination
            if (isset($processedKeys[$uniqueKey])) {
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
            
            $processedKeys[$uniqueKey] = true; // Mark as processed
        }

        return $available;
    }
}

