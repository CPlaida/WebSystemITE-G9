<?php

namespace App\Controllers;

use App\Models\LaboratoryModel;
use App\Models\ServiceModel;

class Laboratory extends BaseController
{
    protected $labModel;

    public function __construct()
    {
        $this->labModel = new LaboratoryModel();
    }

    /**
     * Get role-based view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleMap = [
            'admin' => 'admin',
            'labstaff' => 'admin', // Lab staff use admin views (unified)
            'doctor' => 'admin', // Use admin view for doctors (unified)
            'nurse' => 'admin', // Use admin view for nurses (unified)
            'receptionist' => 'admin',
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/laboratory/{$viewName}";
    }

    /**
     * Patient name suggestions (case-insensitive) for lab and booking forms.
     * GET /laboratory/patient/suggest?q=term
     * Returns: { success: true, results: [{id,name,type}] }
     */
    public function patientSuggest()
    {
        if (!session()->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'results' => []]);
        }

        $q = trim((string) ($this->request->getGet('q') ?? ''));
        if ($q === '' || mb_strlen($q) < 2) {
            return $this->response->setJSON(['success' => true, 'results' => []]);
        }

        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('patients')) {
                return $this->response->setJSON(['success' => true, 'results' => []]);
            }

            $fields = array_map('strtolower', $db->getFieldNames('patients'));
            $colFirst = in_array('first_name', $fields) ? 'first_name' : (in_array('firstname', $fields) ? 'firstname' : null);
            $colLast  = in_array('last_name', $fields) ? 'last_name'  : (in_array('lastname', $fields)  ? 'lastname'  : null);
            $colName  = in_array('name', $fields) ? 'name' : (in_array('full_name', $fields) ? 'full_name' : null);
            $colType  = in_array('type', $fields) ? 'type' : null; // inpatient/outpatient if present

            $b = $db->table('patients');
            // Build select name expression
            if ($colName) {
                $b->select("id, TRIM($colName) AS name" . ($colType ? ", $colType AS type" : ", '' AS type"), false);
                // case-insensitive search on name column
                $b->groupStart()
                  ->like($colName, $q, 'both', null, true)
                  ->groupEnd();
            } else {
                $firstExpr = $colFirst ? $colFirst : "''";
                $lastExpr  = $colLast  ? $colLast  : "''";
                $nameExpr  = "TRIM(CONCAT($firstExpr, ' ', $lastExpr))";
                $b->select("id, $nameExpr AS name" . ($colType ? ", $colType AS type" : ", '' AS type"), false);
                // case-insensitive search across first/last and concatenation
                $b->groupStart();
                if ($colFirst) { $b->like($colFirst, $q, 'both', null, true); }
                if ($colLast)  { $b->orLike($colLast,  $q, 'both', null, true); }
                $b->orLike("CONCAT($firstExpr, ' ', $lastExpr)", $q, 'both', null, true);
                $b->groupEnd();
            }

            $rows = $b->orderBy('id', 'DESC')->limit(10)->get()->getResultArray();
            // Normalize output
            $results = array_map(function($r){
                return [
                    'id' => trim((string)($r['id'] ?? '')),
                    'name' => trim((string)($r['name'] ?? '')),
                    'type' => (string)($r['type'] ?? '')
                ];
            }, $rows ?? []);

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'results' => []]);
        }
    }

    private function resolveResultFilePath($relativePath, bool $mustBeFile = true)
    {
        if (empty($relativePath)) {
            return null;
        }

        $cleanPath = ltrim(str_replace(['..', '\\'], ['', '/'], $relativePath), '/');
        $fullPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanPath);

        if (!file_exists($fullPath)) {
            return null;
        }

        if ($mustBeFile && !is_file($fullPath)) {
            return null;
        }

        return $fullPath;
    }

    private function deleteResultFile($relativePath)
    {
        $fullPath = $this->resolveResultFilePath($relativePath, false);
        if (!$fullPath || !file_exists($fullPath)) {
            return;
        }

        if (is_dir($fullPath)) {
            $items = scandir($fullPath);
            foreach ($items as $item) {
                if (in_array($item, ['.', '..'])) {
                    continue;
                }
                $itemPath = $fullPath . DIRECTORY_SEPARATOR . $item;
                $relativeItem = rtrim($relativePath, '/\\') . '/' . $item;
                if (is_dir($itemPath)) {
                    $this->deleteResultFile($relativeItem);
                } else {
                    @unlink($itemPath);
                }
            }
            @rmdir($fullPath);
            return;
        }

        @unlink($fullPath);
    }

    private function getResultFileManifest(array $record): array
    {
        $relativePath = $record['result_file_path'] ?? '';
        if (empty($relativePath)) {
            return [];
        }

        $fullPath = $this->resolveResultFilePath($relativePath, false);
        if (!$fullPath || !file_exists($fullPath)) {
            return [];
        }

        $manifest = [];

        if (is_dir($fullPath)) {
            $manifestPath = rtrim($fullPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'manifest.json';
            if (is_file($manifestPath)) {
                $decoded = json_decode(file_get_contents($manifestPath), true);
                if (is_array($decoded)) {
                    foreach ($decoded as $idx => $entry) {
                        $manifest[] = [
                            'label' => $entry['label'] ?? basename($entry['stored_path'] ?? ''),
                            'stored_path' => $entry['stored_path'] ?? null,
                            'mime' => $entry['mime'] ?? 'application/octet-stream',
                            'size' => $entry['size'] ?? null,
                            'test_type' => $entry['test_type'] ?? '', // Include test_type from manifest
                            'index' => $idx,
                        ];
                    }
                    return $manifest;
                }
            }

            $files = scandir($fullPath);
            foreach ($files as $fileName) {
                if (in_array($fileName, ['.', '..', 'manifest.json'])) {
                    continue;
                }
                $filePath = $fullPath . DIRECTORY_SEPARATOR . $fileName;
                if (!is_file($filePath)) {
                    continue;
                }
                $manifest[] = [
                    'label' => $fileName,
                    'stored_path' => rtrim($relativePath, '/\\') . '/' . $fileName,
                    'mime' => mime_content_type($filePath) ?: 'application/octet-stream',
                    'size' => filesize($filePath),
                    'index' => count($manifest),
                ];
            }

            return $manifest;
        }

        return [[
            'label' => $record['result_file_name'] ?? basename($relativePath),
            'stored_path' => $relativePath,
            'mime' => $record['result_file_type'] ?? 'application/octet-stream',
            'size' => $record['result_file_size'] ?? null,
            'index' => 0,
        ]];
    }

    private function attachFileMetadata(array $record): array
    {
        if (empty($record)) {
            return $record;
        }

        $attachments = $this->getResultFileManifest($record);
        $record['attachments'] = $attachments;

        if (!empty($attachments) && isset($record['id'])) {
            $primary = $attachments[0];
            $downloadUrl = base_url('laboratory/testresult/download/' . $record['id']);
            if (isset($primary['index'])) {
                $downloadUrl .= '?file=' . $primary['index'];
            }

            $label = $primary['label'] ?? 'Download Result File';
            $additionalCount = max(count($attachments) - 1, 0);
            if ($additionalCount > 0) {
                $label .= ' (+' . $additionalCount . ' more)';
            }

            $record['result_file_url'] = $downloadUrl;
            $record['result_file_label'] = $label;
        } else {
            $record['result_file_url'] = null;
            $record['result_file_label'] = null;
        }

        return $record;
    }

    public function downloadResultFile($testId = null)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin', 'nurse', 'receptionist'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to download this file.');
        }

        if (empty($testId)) {
            return redirect()->back()->with('error', 'Test ID is required.');
        }

        $record = $this->labModel->find($testId);
        if (!$record || empty($record['result_file_path'])) {
            return redirect()->back()->with('error', 'No analyzer file is attached to this test result.');
        }

        $manifest = $this->getResultFileManifest($record);
        if (empty($manifest)) {
            return redirect()->back()->with('error', 'Analyzer files are missing on the server.');
        }

        $index = $this->request->getGet('file');
        $index = is_numeric($index) ? (int)$index : 0;
        $entry = $manifest[$index] ?? $manifest[0];

        if (empty($entry['stored_path'])) {
            return redirect()->back()->with('error', 'Unable to locate the requested analyzer file.');
        }

        $fullPath = $this->resolveResultFilePath($entry['stored_path']);
        if (!$fullPath) {
            return redirect()->back()->with('error', 'Analyzer file is missing on the server.');
        }

        $downloadName = $entry['label'] ?? (!empty($record['result_file_name']) ? $record['result_file_name'] : basename($fullPath));
        return $this->response->download($fullPath, null)->setFileName($downloadName);
    }

    public function request()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin', 'nurse'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Laboratory Request',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view($this->getRoleViewPath('LaboratoryReq'), $data);
    }

    /**
     * Laboratory Request (alias for labstaff)
     */
    public function laboratoryRequest()
    {
        return $this->request();
    }

    /*Submit lab request */
    public function submitRequest()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            // Check if it's an API call or form submission
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
            }
            return redirect()->to('/login')->with('error', 'Access denied');
        }

        // Handle GET request - show form or redirect
        if ($this->request->getMethod() === 'get') {
            return redirect()->to('laboratory/request');
        }

        // Map form fields to database fields
        $patientName = $this->request->getPost('patient_name');
        $patientId = $this->request->getPost('patient_id');
        $testTypesJson = $this->request->getPost('test_types');
        $priority = $this->request->getPost('priority');
        $clinicalNotes = $this->request->getPost('clinical_notes');
        $testDate = $this->request->getPost('test_date');
        
        // Parse test types from JSON or fallback to single test_type
        $testTypes = [];
        if (!empty($testTypesJson)) {
            $decoded = json_decode($testTypesJson, true);
            if (is_array($decoded) && !empty($decoded)) {
                $testTypes = array_values(array_filter(array_map('trim', $decoded)));
            }
        }
        
        // Fallback to old single test_type field if test_types is empty
        if (empty($testTypes)) {
            $singleTestType = $this->request->getPost('test_type');
            if (!empty($singleTestType)) {
                $testTypes = [trim($singleTestType)];
            }
        }
        
        if (empty($testTypes)) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON(['success' => false, 'message' => 'Please add at least one test type.']);
            }
            return redirect()->back()->withInput()->with('error', 'Please add at least one test type.');
        }
        
        // Validate patient_id is provided
        if (empty($patientId)) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON(['success' => false, 'message' => 'Please select a valid patient from the suggestions.']);
            }
            return redirect()->back()->withInput()->with('error', 'Please select a valid patient from the suggestions.');
        }
        
        // Normalize patient_id to string for consistent comparison
        $patientId = trim((string)$patientId);
        
        // Check for pending/incomplete laboratory requests for this patient
        $hasPending = $this->checkPatientHasPendingRequest($patientId);
        
        if ($hasPending) {
            $errorMsg = "This patient already has a pending laboratory request. Please wait until it is completed.";
            
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
            }
            return redirect()->back()->withInput()->with('error', $errorMsg);
        }
        
        // Check for duplicate requests (same patient, same test types, not completed/cancelled)
        // This is a more specific check after the pending check
        $existingRequests = $this->labModel
            ->where('patient_id', $patientId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->findAll();
        
        if (!empty($existingRequests)) {
            foreach ($existingRequests as $existing) {
                $existingTestTypes = $this->parseTestTypes($existing['test_type'] ?? '');
                $newTestTypes = $testTypes;
                
                // Sort both arrays for comparison
                sort($existingTestTypes);
                sort($newTestTypes);
                
                // Check if test types match exactly
                if ($existingTestTypes === $newTestTypes) {
                    $testTypesList = implode(', ', array_map('ucfirst', $newTestTypes));
                    $errorMsg = "A duplicate laboratory request already exists for this patient with the same test types ({$testTypesList}). Request ID: {$existing['id']}";
                    
                    if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                        return $this->response->setJSON(['success' => false, 'message' => $errorMsg]);
                    }
                    return redirect()->back()->withInput()->with('error', $errorMsg);
                }
            }
        }
        
        // Store test types as JSON array
        $testType = count($testTypes) === 1 ? $testTypes[0] : json_encode($testTypes);

        // Validate test date - cannot be in the past
        $today = date('Y-m-d');
        if ($testDate && $testDate < $today) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON(['success' => false, 'message' => 'Test date cannot be in the past. Please select today or a future date.']);
            }
            return redirect()->back()->withInput()->with('error', 'Test date cannot be in the past. Please select today or a future date.');
        }
        
        // Use submitted date if valid, otherwise use today
        $validTestDate = ($testDate && $testDate >= $today) ? $testDate : $today;

        // patient_name is expected to be the actual name from the form

        // Store test types as JSON array (multiple test types)
        $testTypeValue = count($testTypes) === 1 ? $testTypes[0] : json_encode($testTypes);

        // Simple data array without foreign key dependencies
        $data = [
            'test_name' => $patientName,
            'patient_id' => $patientId, // Store patient_id for validation
            'test_type' => $testTypeValue, // JSON array for multiple, or single string
            'priority' => $priority,
            'test_date' => $validTestDate,
            'test_time' => date('H:i:s'),
            'status' => 'pending',
            'notes' => $clinicalNotes
        ];

        // Attempt to snapshot service_id and cost from services table (use first test type for service lookup)
        try {
            $svcModel = new ServiceModel();
            $svc = null;
            if (!empty($testTypes[0])) {
                $firstTestType = $testTypes[0];
                $svc = $svcModel->findByCodeOrName($firstTestType);
                if (!$svc) {
                    // fallback: try case-insensitive name match
                    $svc = $svcModel->where('LOWER(name)', strtolower($firstTestType))->where('active', 1)->get()->getRowArray();
                }
            }
            if ($svc) {
                $data['service_id'] = (int)$svc['id'];
                $data['cost'] = (float)$svc['base_price'];
            }
        } catch (\Throwable $e) {
            // ignore snapshot errors; lab record can still be created
        }

        // Data prepared for insertion
        try {
            // Add timestamps
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            // Insert via model (will run validation). Returns insert ID on success or false on failure.
            $insertId = $this->labModel->insert($data, true);
            
            if ($insertId) {
                // Check if it's an API call or form submission
                if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Lab request submitted successfully',
                        // Compatibility: expose numeric id under test_id key for existing UI
                        'test_id' => (string) $insertId,
                        'id' => (int) $insertId,
                    ]);
                } else {
                    // For form submissions, redirect to test results page
                    return redirect()->to('laboratory/testresult')->with('success', 'Lab request submitted successfully. Request ID: ' . $insertId);
                }
            } else {
                // Validation failed or insert error
                $errors = $this->labModel->errors() ?: ['Failed to insert lab request'];
                if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                    return $this->response->setJSON(['success' => false, 'errors' => $errors]);
                }
                return redirect()->back()->withInput()->with('errors', $errors);
            }
        } catch (\Exception $e) {
            log_message('error', 'Lab request creation failed: ' . $e->getMessage());
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Failed to create lab request: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to create lab request: ' . $e->getMessage());
        }
    }

    public function testresult()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin', 'nurse'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        // Preload initial data so the view can render immediately even if JS fetch is delayed
        try {
            $initialResults = $this->labModel
                ->select('id, id as test_id, test_name as patient_name, test_type, test_date, status, notes')
                ->orderBy('created_at', 'DESC')
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Failed to preload test results: ' . $e->getMessage());
            $initialResults = [];
        }

        $data = [
            'title' => 'Test Results',
            'user' => session()->get('username'),
            'role' => session()->get('role'),
            'initialResults' => $initialResults,
        ];

        return view($this->getRoleViewPath('TestResult'), $data);
    }

    /**
     * Get test results data for TestResult view
     */
    public function getTestResultsData()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        try {
            // test_id column removed; expose numeric id as test_id for UI compatibility
            $results = $this->labModel
                ->select('id, id as test_id, test_name as patient_name, test_name, test_type, test_date, status, notes, result_file_path, result_file_name, result_file_type, result_file_size')
                ->orderBy('created_at', 'DESC')
                ->findAll();

            if (!empty($results)) {
                foreach ($results as $idx => $row) {
                    $results[$idx] = $this->attachFileMetadata($row);
                }
            }

            return $this->response->setJSON($results);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get test results data: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load data']);
        }
    }

    public function patientLabRecords()
    {
        // Return completed lab records for EHR by patient
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['admin','doctor','nurse','receptionist','labstaff'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $patientId = trim((string) ($this->request->getGet('patient_id') ?? ''));
        $name = trim((string) ($this->request->getGet('name') ?? ''));

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('laboratory');
            $builder->select('id, test_type, test_date, status, notes, test_name, result_file_path, result_file_name, result_file_type, result_file_size')
                    ->whereIn('status', ['pending', 'in_progress', 'completed'])
                    ->orderBy('test_date', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->limit(20);

            // Check if patient_id column exists in laboratory table
            $labFields = $db->getFieldNames('laboratory');
            $hasPatientId = in_array('patient_id', $labFields);

            // Build robust name filters based on patient_id and/or provided name
            $fullName = '';
            $first = '';
            $last = '';
            $middle = '';
            
            if ($patientId !== '' && $db->tableExists('patients')) {
                try {
                    $pf = $db->getFieldNames('patients');
                    $fn = in_array('first_name',$pf) ? 'first_name' : (in_array('firstname',$pf) ? 'firstname' : null);
                    $ln = in_array('last_name',$pf) ? 'last_name' : (in_array('lastname',$pf) ? 'lastname' : null);
                    $mn = in_array('middle_name',$pf) ? 'middle_name' : null;
                    $nm = in_array('name',$pf) ? 'name' : (in_array('full_name',$pf) ? 'full_name' : null);
                    
                    $selectFields = [];
                    if ($nm) {
                        $selectFields[] = "$nm as name";
                    } else {
                        if ($fn) $selectFields[] = "$fn as first_name";
                        if ($ln) $selectFields[] = "$ln as last_name";
                        if ($mn) $selectFields[] = "$mn as middle_name";
                    }
                    
                    $rowP = $db->table('patients')
                               ->select(implode(',', $selectFields))
                               ->where('id', $patientId)
                               ->get()
                               ->getRowArray();
                    
                    if ($rowP) {
                        if (!empty($rowP['name'])) { 
                            $fullName = trim($rowP['name']); 
                        } else {
                            $first = trim((string)($rowP['first_name'] ?? ''));
                            $middle = trim((string)($rowP['middle_name'] ?? ''));
                            $last  = trim((string)($rowP['last_name'] ?? ''));
                            // Build full name with middle name
                            $nameParts = array_filter([$first, $middle, $last]);
                            $fullName = trim(implode(' ', $nameParts));
                        }
                    }
                } catch (\Throwable $e) { 
                    log_message('error', 'Error fetching patient for lab records: ' . $e->getMessage());
                }
            }

            // Match by patient_id OR name (in case patient_id wasn't set in lab record)
            $hasNameFilter = ($name !== '' || $fullName !== '' || $first !== '' || $last !== '');
            
            // Build flexible name search terms 
            $searchTerms = [];
            
            // Most important: First + Last name (this is what's usually in test_name)
            if ($first !== '' && $last !== '') {
                $searchTerms[] = trim($first . ' ' . $last);
                // Also try with middle initial
                if ($middle !== '') {
                    $middleInitial = trim(substr($middle, 0, 1));
                    $searchTerms[] = trim($first . ' ' . $middleInitial . ' ' . $last);
                    $searchTerms[] = trim($first . ' ' . $middleInitial . '. ' . $last);
                }
            }
            
            // Full name variations
            if ($fullName !== '') {
                $searchTerms[] = $fullName;
                // Remove common suffixes (Jr., Sr., II, III, etc.)
                $nameWithoutSuffix = preg_replace('/\s+(Jr\.?|Sr\.?|II|III|IV|V)$/i', '', $fullName);
                if ($nameWithoutSuffix !== $fullName) {
                    $searchTerms[] = trim($nameWithoutSuffix);
                }
            }
            
            // Individual name parts
            if ($first !== '') {
                $searchTerms[] = $first;
            }
            if ($last !== '') {
                $searchTerms[] = $last;
            }
            if ($name !== '' && !in_array($name, $searchTerms)) {
                $searchTerms[] = $name;
            }
            
            // If name parameter is provided but we couldn't build search terms from patient lookup,
            // use the name parameter directly (it might be in a different format)
            if (empty($searchTerms) && $name !== '') {
                $searchTerms[] = $name;
                // Also try splitting the name and using first/last parts
                $nameParts = preg_split('/\s+/', trim($name));
                if (count($nameParts) >= 2) {
                    $searchTerms[] = trim($nameParts[0] . ' ' . end($nameParts)); // First and last
                }
            }
            
            // Remove duplicates and empty values
            $searchTerms = array_unique(array_filter($searchTerms));
            
            if ($patientId !== '' && $hasPatientId && !empty($searchTerms)) {
                // Match by patient_id OR name (grouped)
                $builder->groupStart()
                        ->where('patient_id', $patientId)
                        ->orGroupStart();
                
                $firstTerm = true;
                foreach ($searchTerms as $term) {
                    if ($firstTerm) {
                        $builder->like('test_name', $term);
                        $firstTerm = false;
                    } else {
                        $builder->orLike('test_name', $term);
                    }
                }
                
                $builder->groupEnd()
                        ->groupEnd();
            } 
            else if ($patientId !== '' && $hasPatientId) {
                // Only patient_id match (but still try name if provided)
                if (!empty($searchTerms)) {
                    $builder->groupStart()
                            ->where('patient_id', $patientId)
                            ->orGroupStart();
                    
                    $firstTerm = true;
                    foreach ($searchTerms as $term) {
                        if ($firstTerm) {
                            $builder->like('test_name', $term);
                            $firstTerm = false;
                        } else {
                            $builder->orLike('test_name', $term);
                        }
                    }
                    
                    $builder->groupEnd()
                            ->groupEnd();
                } else {
                    $builder->where('patient_id', $patientId);
                }
            }
            else if (!empty($searchTerms)) {
                // Only name matching
                $builder->groupStart();
                
                $firstTerm = true;
                foreach ($searchTerms as $term) {
                    if ($firstTerm) {
                        $builder->like('test_name', $term);
                        $firstTerm = false;
                    } else {
                        $builder->orLike('test_name', $term);
                    }
                }
                
                $builder->groupEnd();
            }

            $rows = $builder->get()->getResultArray();

            if (!empty($rows)) {
                foreach ($rows as $idx => $row) {
                    $rows[$idx] = $this->attachFileMetadata($row);
                }
            }
            
            // Debug logging (can be removed in production)
            log_message('debug', 'Lab records query - Patient ID: ' . $patientId . ', Name: ' . $name . ', Full Name: ' . $fullName . ', First: ' . $first . ', Last: ' . $last . ', Search Terms: ' . implode(', ', $searchTerms ?? []) . ', Records found: ' . count($rows));

            return $this->response->setJSON([
                'success' => true,
                'records' => $rows,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to load lab records']);
        }
    }

    public function viewTestResult($testId = null)
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin', 'nurse'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        // Accept test_id from POST body if route parameter is missing
        if (empty($testId)) {
            $testId = $this->request->getPost('test_id');
        }
        if (empty($testId)) {
            return redirect()->to('laboratory/testresult')->with('error', 'Test ID is required');
        }

        try {
            $testResult = $this->labModel->where('id', $testId)->first();

            if (!$testResult) {
                return redirect()->to('laboratory/testresult')->with('error', 'Test result not found');
            }

            // Parse JSON fields if they exist
            if (!empty($testResult['test_results'])) {
                $testResult['results'] = json_decode($testResult['test_results'], true) ?: [];
            } else {
                $testResult['results'] = [];
            }

            if (!empty($testResult['normal_range'])) {
                $testResult['normal_ranges'] = json_decode($testResult['normal_range'], true) ?: [];
            } else {
                $testResult['normal_ranges'] = [];
            }

            $testResult = $this->attachFileMetadata($testResult);

            // Add patient name mapping for display
            $testResult['patient_name'] = $testResult['test_name']; // test_name contains patient name
            // Expose numeric id as test_id for display consistency
            $testResult['test_id'] = $testResult['id'];
            
            // Add formatted dates
            $testResult['formatted_test_date'] = date('F j, Y', strtotime($testResult['test_date']));
            $testResult['formatted_test_time'] = !empty($testResult['test_time']) ? date('g:i A', strtotime($testResult['test_time'])) : '—';
            
            // Add priority display if exists
            $testResult['priority_display'] = ucfirst($testResult['priority'] ?? 'routine');
            
            // Add status badge class
            $testResult['status_class'] = $testResult['status'] === 'completed' ? 'badge-success' : 'badge-warning';

            // Per-test-type progress
            $allTestTypes = $this->parseTestTypes($testResult['test_type'] ?? '');
            $completedTypes = $this->getCompletedTestTypes($testResult, false);
            
            // Load per-test-type notes from metadata
            $testTypeNotes = [];
            $resultDir = $testResult['result_file_path'] ?? '';
            if (!empty($resultDir)) {
                $notesMetadataPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $resultDir) . DIRECTORY_SEPARATOR . 'test_type_notes.json';
                if (file_exists($notesMetadataPath)) {
                    $testTypeNotes = json_decode(file_get_contents($notesMetadataPath), true) ?: [];
                }
            }
            
            // Get all attachments and filter by test type
            $allAttachments = $testResult['attachments'] ?? [];
            
            $processedTests = [];
            foreach ($allTestTypes as $tt) {
                // Get notes specific to this test type
                $testSpecificNotes = $testTypeNotes[$tt] ?? '';
                
                // Filter attachments for this specific test type
                $testSpecificAttachments = array_filter($allAttachments, function($attachment) use ($tt) {
                    return isset($attachment['test_type']) && $attachment['test_type'] === $tt;
                });
                
                $processedTests[] = [
                    'id' => $testResult['id'],
                    'test_type' => $tt,
                    'status' => in_array($tt, $completedTypes) ? 'completed' : ($testResult['status'] === 'completed' ? 'completed' : $testResult['status']),
                    'has_results' => in_array($tt, $completedTypes),
                    'is_completed' => in_array($tt, $completedTypes),
                    'notes' => $testSpecificNotes, // Only notes for this specific test type
                    'attachments' => array_values($testSpecificAttachments), // Only attachments for this test type
                ];
            }
            $testResult['all_tests'] = $processedTests;
            $testResult['completed_count'] = count($completedTypes);
            $testResult['total_count'] = count($allTestTypes);
            $testResult['all_test_types_completed'] = $testResult['completed_count'] > 0 && $testResult['completed_count'] === $testResult['total_count'];

            $data = [
                'title' => 'View Test Result',
                'user' => session()->get('username'),
                'role' => session()->get('role'),
                'testResult' => $testResult
            ];

            return view($this->getRoleViewPath('ViewTestResult'), $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get test result: ' . $e->getMessage());
            return redirect()->to('laboratory/testresult')->with('error', 'Failed to load test result');
        }
    }

    public function addTestResult($testId = null)
    {
        // Only lab staff and admin can add test results
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        // Handle form submission
        if (strtolower($this->request->getMethod()) === 'post') {
            try {
        // Accept test_id from POST body if route parameter is missing
        if (empty($testId)) {
            $testId = $this->request->getPost('test_id');
        }
        if (empty($testId)) {
            return redirect()->to('laboratory/testresult')->with('error', 'Test ID is required');
        }

                $testRecord = $this->labModel->find($testId);
                if (!$testRecord) {
                    return redirect()->to('laboratory/testresult')->with('error', 'Test not found');
                }

                // Block adding results to completed requests
                if ($testRecord['status'] === 'completed') {
                    return redirect()->to('laboratory/testresult/view/' . $testId)->with('error', 'This request is already completed and cannot be modified.');
                }

                // Handle multiple test types from form submission
                $allTestTypes = $this->parseTestTypes($testRecord['test_type'] ?? '');
                $resultFiles = $_FILES['result_files'] ?? [];
                $notesData = $this->request->getPost('notes') ?? [];
                
                // Process each test type that has files
                $processedTestTypes = [];
                $allManifestEntries = [];
                $totalSize = 0;
                $timestamp = time();
                $relativeDir = 'uploads/lab_results/' . $testId . '_' . $timestamp;
                $uploadDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $allowedExtensions = ['pdf', 'csv', 'txt', 'xml', 'json', 'xls', 'xlsx', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

                // Process files for each test type
                foreach ($allTestTypes as $testType) {
                    // Check if this test type has files
                    if (!isset($resultFiles['name'][$testType]) || empty($resultFiles['name'][$testType])) {
                        continue;
                    }

                    // Disallow re-adding results to a completed test type
                    if ($this->checkTestTypeHasResults($testId, $testType)) {
                        continue; // Skip completed test types
                    }

                    $files = $resultFiles['name'][$testType];
                    $tmpNames = $resultFiles['tmp_name'][$testType];
                    $sizes = $resultFiles['size'][$testType];
                    $errors = $resultFiles['error'][$testType];
                    
                    if (!is_array($files)) {
                        $files = [$files];
                        $tmpNames = [$tmpNames];
                        $sizes = [$sizes];
                        $errors = [$errors];
                }

                $validFiles = [];
                    foreach ($files as $idx => $fileName) {
                        if ($errors[$idx] !== UPLOAD_ERR_OK || empty($tmpNames[$idx])) {
                        continue;
                    }

                        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($extension, $allowedExtensions)) {
                            continue;
                        }

                        $fileSize = $sizes[$idx];
                        if ($fileSize <= 0 || $fileSize > 10 * 1024 * 1024) { // 10MB limit
                            continue;
                        }

                        $validFiles[] = [
                            'name' => $fileName,
                            'tmp_name' => $tmpNames[$idx],
                            'size' => $fileSize,
                            'extension' => $extension,
                        ];
                    }

                    if (empty($validFiles)) {
                        continue;
                    }

                    // Move files and create manifest entries
                    foreach ($validFiles as $idx => $fileInfo) {
                        $clientName = $fileInfo['name'];
                    $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($clientName, PATHINFO_FILENAME) ?: ('attachment_' . ($idx + 1)));
                        $storedName = $safeBase . '_' . uniqid('', true) . '.' . $fileInfo['extension'];

                        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $storedName;
                        if (move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
                    $storedRelative = rtrim($relativeDir, '/\\') . '/' . $storedName;
                            $mimeType = mime_content_type($targetPath) ?: 'application/octet-stream';

                            $allManifestEntries[] = [
                        'label' => $clientName,
                        'stored_path' => $storedRelative,
                                'mime' => $mimeType,
                                'size' => $fileInfo['size'],
                                'test_type' => $testType,
                                'index' => count($allManifestEntries),
                            ];
                            
                            $totalSize += $fileInfo['size'];
                        }
                    }

                    // Track completion for this test type
                    $this->updateCompletedTestTypes($testId, $testType);
                    $processedTestTypes[] = $testType;
                }

                if (empty($processedTestTypes)) {
                    return redirect()->back()->withInput()->with('error', 'Please select at least one test type and upload files.');
                }

                // Save manifest
                if (!empty($allManifestEntries)) {
                    file_put_contents($uploadDir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($allManifestEntries, JSON_PRETTY_PRINT));
                }

                // Update database
                $fileMeta = [
                    'result_file_path' => $relativeDir,
                    'result_file_name' => count($allManifestEntries) === 1 ? ($allManifestEntries[0]['label'] ?? 'Result File') : 'Multiple files (' . count($allManifestEntries) . ')',
                    'result_file_type' => $allManifestEntries[0]['mime'] ?? 'application/octet-stream',
                    'result_file_size' => $totalSize,
                ];

                // Combine notes from all test types
                $combinedNotes = '';
                if (is_array($notesData)) {
                    $notesArray = [];
                    foreach ($notesData as $tt => $note) {
                        if (!empty(trim($note))) {
                            $notesArray[] = ucfirst($tt) . ': ' . trim($note);
                        }
                    }
                    $combinedNotes = implode("\n\n", $notesArray);
                } else {
                    $combinedNotes = trim($notesData ?? '');
                }

                // Reload record to get updated completed types
                $testRecord = $this->labModel->find($testId);
                $completedTypes = $this->getCompletedTestTypes($testRecord, false);
                $allCompleted = count($completedTypes) === count($allTestTypes) && count($allTestTypes) > 0;

                $updateData = [
                    'test_results' => null,
                    'normal_range' => null,
                    'notes' => $combinedNotes,
                    'status' => !empty($completedTypes) ? 'in_progress' : ($testRecord['status'] ?? 'pending'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if (!empty($fileMeta)) {
                    $updateData = array_merge($updateData, $fileMeta);
                }

                $result = $this->labModel->update($testId, $updateData);

                if ($result) {
                    $message = 'Test results added successfully for: ' . implode(', ', $processedTestTypes);
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'success' => true,
                            'message' => $message,
                            'all_completed' => $allCompleted
                        ]);
                    }
                    return redirect()->to('laboratory/testresult/view/' . $testId)
                        ->with('success', $message);
                } else {
                    log_message('error', 'AddTestResult update failed', ['test_id' => $testId]);
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Failed to add test result'
                        ]);
                    }
                    return redirect()->back()->with('error', 'Failed to add test result');
                }
            } catch (\Exception $e) {
                log_message('error', 'Failed to add test result: ' . $e->getMessage());
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Failed to add test result: ' . $e->getMessage()
                    ]);
                }
                return redirect()->back()->with('error', 'Failed to add test result: ' . $e->getMessage());
            }
        }

        // For GET request, show the add result form
        try {
            $testResult = $this->labModel->find($testId);

            if (!$testResult) {
                return redirect()->to('laboratory/testresult')->with('error', 'Test not found');
            }

            $allTestTypes = $this->parseTestTypes($testResult['test_type'] ?? '');
            $completedTypes = $this->getCompletedTestTypes($testResult, false);

            // Build list with status
            $testTypeItems = [];
            foreach ($allTestTypes as $tt) {
                $testTypeItems[] = [
                    'name' => $tt,
                    'completed' => in_array($tt, $completedTypes),
                ];
            }

            $completedCount = count($completedTypes);
            $totalCount = count($allTestTypes);

            $data = [
                'title' => 'Add Test Result',
                'user' => session()->get('username'),
                'role' => session()->get('role'),
                'testResult' => $testResult,
                'testTypes' => $testTypeItems,
                'completedTestTypes' => $completedTypes,
                'completedCount' => $completedCount,
                'totalCount' => $totalCount
            ];

            return view($this->getRoleViewPath('AddTestResult'), $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load test for result entry: ' . $e->getMessage());
            return redirect()->to('laboratory/testresult')->with('error', 'Failed to load test');
        }
    }

    /**
     * Mark the entire request as completed when all test types have results
     */
    public function markRequestComplete($testId = null)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        if (empty($testId)) {
            return redirect()->back()->with('error', 'Test ID is required.');
        }

        $testRecord = $this->labModel->find($testId);
        if (!$testRecord) {
            return redirect()->back()->with('error', 'Test not found.');
        }

        $allTypes = $this->parseTestTypes($testRecord['test_type'] ?? '');
        $completedTypes = $this->getCompletedTestTypes($testRecord, false);
        $allCompleted = count($completedTypes) === count($allTypes) && count($allTypes) > 0;

        if (!$allCompleted) {
            $remaining = array_diff($allTypes, $completedTypes);
            return redirect()->back()->with('error', 'Cannot mark request as complete. Some test types still need results: ' . implode(', ', $remaining));
        }

        $this->labModel->update($testId, [
            'status' => 'completed',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Request marked as completed.');
    }

    /* ---------- Helpers for per-test-type completion ---------- */
    private function parseTestTypes($value): array
    {
        if (empty($value)) {
            return [];
        }
        if (is_array($value)) {
            return array_values(array_filter($value));
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }
        // fallback comma separated
        return array_values(array_filter(array_map('trim', explode(',', (string)$value))));
    }

    private function getCompletedTestTypes(array $record, bool $decodeOnly = true): array
    {
        $raw = $record['completed_test_types'] ?? null;
        if (empty($raw)) {
            return [];
        }
        if (is_array($raw)) {
            return array_values(array_filter($raw));
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }
        return [];
    }

    private function checkTestTypeHasResults($testId, $testType): bool
    {
        $record = $this->labModel->find($testId);
        if (!$record) {
            return false;
        }
        $completed = $this->getCompletedTestTypes($record, false);
        return in_array($testType, $completed);
    }

    private function updateCompletedTestTypes($testId, $testType)
    {
        $record = $this->labModel->find($testId);
        if (!$record) {
            return;
        }
        $completed = $this->getCompletedTestTypes($record, false);
        if (!in_array($testType, $completed)) {
            $completed[] = $testType;
        }
        
        $allTestTypes = $this->parseTestTypes($record['test_type'] ?? '');
        $allCompleted = count($completed) === count($allTestTypes) && count($allTestTypes) > 0;
        
        // Special case: If only one test type, automatically mark as completed when file is added
        $isSingleTestType = count($allTestTypes) === 1;
        
        $updateData = [
            'completed_test_types' => json_encode(array_values($completed)),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        // Auto-complete if all test types have results, or if single test type
        if ($allCompleted || ($isSingleTestType && count($completed) === 1)) {
            $updateData['status'] = 'completed';
        } elseif (!empty($completed) && $record['status'] === 'pending') {
            // Set to in_progress if some types are done but not all
            $updateData['status'] = 'in_progress';
        }
        
        $this->labModel->update($testId, $updateData);
    }

    /**
     * Save results for a single test type
     */
    public function saveTestTypeResult()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied'
            ])->setStatusCode(403);
        }

        try {
            $testId = $this->request->getPost('test_id');
            $testType = $this->request->getPost('test_type');
            
            if (empty($testId) || empty($testType)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Test ID and test type are required'
                ])->setStatusCode(400);
            }

            $testRecord = $this->labModel->find($testId);
            if (!$testRecord) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Test not found'
                ])->setStatusCode(404);
            }

            // Block adding results to completed requests
            if ($testRecord['status'] === 'completed') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This request is already completed and cannot be modified'
                ])->setStatusCode(400);
            }

            // Check if this test type already has results
            if ($this->checkTestTypeHasResults($testId, $testType)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This test type already has results and cannot be modified'
                ])->setStatusCode(400);
            }

            // Verify test type exists in request
            $allTestTypes = $this->parseTestTypes($testRecord['test_type'] ?? '');
            if (!in_array($testType, $allTestTypes)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid test type for this request'
                ])->setStatusCode(400);
            }

            // Process files for this test type
            $resultFiles = $_FILES['result_files'] ?? [];
            $notes = $this->request->getPost('notes') ?? '';
            
            if (!isset($resultFiles['name'][$testType]) || empty($resultFiles['name'][$testType])) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Please upload at least one file for this test type'
                ])->setStatusCode(400);
            }

            $files = $resultFiles['name'][$testType];
            $tmpNames = $resultFiles['tmp_name'][$testType];
            $sizes = $resultFiles['size'][$testType];
            $errors = $resultFiles['error'][$testType];
            
            if (!is_array($files)) {
                $files = [$files];
                $tmpNames = [$tmpNames];
                $sizes = [$sizes];
                $errors = [$errors];
            }

            $allowedExtensions = ['pdf', 'csv', 'txt', 'xml', 'json', 'xls', 'xlsx', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $validFiles = [];
            
            foreach ($files as $idx => $fileName) {
                if ($errors[$idx] !== UPLOAD_ERR_OK || empty($tmpNames[$idx])) {
                    continue;
                }

                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    continue;
                }

                $fileSize = $sizes[$idx];
                if ($fileSize <= 0 || $fileSize > 10 * 1024 * 1024) {
                    continue;
                }

                $validFiles[] = [
                    'name' => $fileName,
                    'tmp_name' => $tmpNames[$idx],
                    'size' => $fileSize,
                    'extension' => $extension,
                ];
            }

            if (empty($validFiles)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No valid files uploaded. Please check file format and size (max 10MB)'
                ])->setStatusCode(400);
            }

            // Get existing manifest if any
            $existingManifest = [];
            $existingDir = $testRecord['result_file_path'] ?? '';
            if (!empty($existingDir)) {
                $existingManifestPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $existingDir) . DIRECTORY_SEPARATOR . 'manifest.json';
                if (file_exists($existingManifestPath)) {
                    $existingManifest = json_decode(file_get_contents($existingManifestPath), true) ?: [];
                }
            }

            // Use existing directory or create new one
            $timestamp = time();
            if (!empty($existingDir) && is_dir(rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $existingDir))) {
                $relativeDir = $existingDir;
                $uploadDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
            } else {
                $relativeDir = 'uploads/lab_results/' . $testId . '_' . $timestamp;
                $uploadDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
            }

            $manifestEntries = [];
            $totalSize = 0;

            // Move files and create manifest entries
            foreach ($validFiles as $idx => $fileInfo) {
                $clientName = $fileInfo['name'];
                $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($clientName, PATHINFO_FILENAME) ?: ('attachment_' . ($idx + 1)));
                $storedName = $safeBase . '_' . uniqid('', true) . '.' . $fileInfo['extension'];

                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $storedName;
                if (move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
                    $storedRelative = rtrim($relativeDir, '/\\') . '/' . $storedName;
                    $mimeType = mime_content_type($targetPath) ?: 'application/octet-stream';
                    
                    $manifestEntries[] = [
                        'label' => $clientName,
                        'stored_path' => $storedRelative,
                        'mime' => $mimeType,
                        'size' => $fileInfo['size'],
                        'test_type' => $testType,
                        'index' => count($existingManifest) + count($manifestEntries),
                    ];
                    
                    $totalSize += $fileInfo['size'];
                }
            }

            if (empty($manifestEntries)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to upload files'
                ])->setStatusCode(500);
            }

            // Merge with existing manifest
            $allManifestEntries = array_merge($existingManifest, $manifestEntries);
            file_put_contents($uploadDir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($allManifestEntries, JSON_PRETTY_PRINT));

            // Update database
            $fileMeta = [
                'result_file_path' => $relativeDir,
                'result_file_name' => count($allManifestEntries) === 1 ? ($allManifestEntries[0]['label'] ?? 'Result File') : 'Multiple files (' . count($allManifestEntries) . ')',
                'result_file_type' => $allManifestEntries[0]['mime'] ?? 'application/octet-stream',
                'result_file_size' => ($testRecord['result_file_size'] ?? 0) + $totalSize,
            ];

            // Store notes per test type in manifest metadata
            // We'll store notes in a separate metadata structure
            $testTypeNotes = [];
            $notesMetadataPath = $uploadDir . DIRECTORY_SEPARATOR . 'test_type_notes.json';
            if (file_exists($notesMetadataPath)) {
                $testTypeNotes = json_decode(file_get_contents($notesMetadataPath), true) ?: [];
            }
            
            if (!empty($notes)) {
                $testTypeNotes[$testType] = trim($notes);
            }
            
            // Save test type notes metadata
            if (!empty($testTypeNotes)) {
                file_put_contents($notesMetadataPath, json_encode($testTypeNotes, JSON_PRETTY_PRINT));
            }
            
            // Keep combined notes for backward compatibility (but won't be used for per-test display)
            $combinedNotes = $testRecord['notes'] ?? '';

            // Update completed test types
            $this->updateCompletedTestTypes($testId, $testType);

            // Reload record to get updated status
            $testRecord = $this->labModel->find($testId);
            $completedTypes = $this->getCompletedTestTypes($testRecord, false);
            $allTestTypes = $this->parseTestTypes($testRecord['test_type'] ?? '');
            $allCompleted = count($completedTypes) === count($allTestTypes) && count($allTestTypes) > 0;

            $updateData = array_merge($fileMeta, [
                'notes' => $combinedNotes,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->labModel->update($testId, $updateData);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Test results saved successfully for ' . $testType,
                'test_type' => $testType,
                'all_completed' => $allCompleted
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to save test type result: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save test result: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Check if patient has pending requests
     * GET /laboratory/request/check-pending?patient_id=xxx
     */
    public function checkPatientPendingRequest()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin', 'nurse'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $patientId = $this->request->getGet('patient_id');
        if (empty($patientId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Patient ID is required']);
        }

        $hasPending = $this->checkPatientHasPendingRequest($patientId);
        
        return $this->response->setJSON([
            'success' => true,
            'has_pending' => $hasPending,
            'message' => $hasPending ? 'This patient already has a pending laboratory request. Please wait until it is completed.' : ''
        ]);
    }

    /**
     * Helper method to check if patient has pending requests
     */
    private function checkPatientHasPendingRequest($patientId)
    {
        $pendingRequests = $this->labModel
            ->where('patient_id', $patientId)
            ->whereIn('status', ['pending', 'in_progress', 'scheduled'])
            ->findAll();
        
        return !empty($pendingRequests);
    }

    /**
     * Cancel a laboratory request
     * POST /laboratory/testresult/cancel/(:segment)
     */
    public function cancelRequest($testId = null)
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied');
        }

        if (empty($testId)) {
            return redirect()->back()->with('error', 'Test ID is required');
        }

        try {
            $testRecord = $this->labModel->find($testId);
            if (!$testRecord) {
                return redirect()->back()->with('error', 'Laboratory request not found');
            }

            $currentStatus = strtolower($testRecord['status'] ?? 'pending');
            if (in_array($currentStatus, ['completed', 'cancelled'])) {
                return redirect()->back()->with('error', 'Cannot cancel a request that is already ' . $currentStatus);
            }

            // Update status to cancelled
            $this->labModel->update($testId, [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->back()->with('success', 'Laboratory request has been cancelled successfully');
        } catch (\Exception $e) {
            log_message('error', 'Failed to cancel laboratory request: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to cancel laboratory request');
        }
    }
}
