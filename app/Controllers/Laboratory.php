<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\LaboratoryModel;

class Laboratory extends Controller
{
    protected $labModel;

    public function __construct()
    {
        $this->labModel = new LaboratoryModel();
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
                    'id' => (int)($r['id'] ?? 0),
                    'name' => trim((string)($r['name'] ?? '')),
                    'type' => (string)($r['type'] ?? '')
                ];
            }, $rows ?? []);

            return $this->response->setJSON(['success' => true, 'results' => $results]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['success' => false, 'results' => []]);
        }
    }

    public function request()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Laboratory Request',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Roles/admin/laboratory/LaboratoryReq', $data);
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
        $testType = $this->request->getPost('test_type');
        $priority = $this->request->getPost('priority');
        $clinicalNotes = $this->request->getPost('clinical_notes');

        // patient_name is expected to be the actual name from the form

        // Simple data array without foreign key dependencies
        $data = [
            'test_name' => $patientName,
            'test_type' => $testType,
            'priority' => $priority,
            'test_date' => date('Y-m-d'),
            'test_time' => date('H:i:s'),
            'status' => 'pending',
            'notes' => $clinicalNotes
        ];

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
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin'])) {
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

        return view('Roles/admin/laboratory/TestResult', $data);
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
                ->select('id, id as test_id, test_name as patient_name, test_type, test_date, status, notes')
                ->orderBy('created_at', 'DESC')
                ->findAll();
            
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

        $patientId = (int) ($this->request->getGet('patient_id') ?? 0);
        $name = trim((string) ($this->request->getGet('name') ?? ''));

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('laboratory');
            $builder->select('id, test_type, test_date, status, notes, test_name')
                    ->where('status', 'completed')
                    ->orderBy('test_date', 'DESC')
                    ->limit(20);

            // Build robust name filters based on patient_id and/or provided name
            $fullName = '';
            $first = '';
            $last = '';
            if ($patientId > 0 && $db->tableExists('patients')) {
                try {
                    $pf = $db->getFieldNames('patients');
                    $fn = in_array('first_name',$pf) ? 'first_name' : (in_array('firstname',$pf) ? 'firstname' : null);
                    $ln = in_array('last_name',$pf) ? 'last_name' : (in_array('lastname',$pf) ? 'lastname' : null);
                    $nm = in_array('name',$pf) ? 'name' : (in_array('full_name',$pf) ? 'full_name' : null);
                    $rowP = $db->table('patients')->select($nm?"$nm as name":($fn?"$fn as first_name":'') . ($ln?",".$ln." as last_name":''))
                                   ->where('id', $patientId)->get()->getRowArray();
                    if ($rowP) {
                        if (!empty($rowP['name'])) { $fullName = trim($rowP['name']); }
                        else {
                            $first = trim((string)($rowP['first_name'] ?? ''));
                            $last  = trim((string)($rowP['last_name'] ?? ''));
                            $fullName = trim($first . ' ' . $last);
                        }
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }

            if ($name !== '' || $fullName !== '' || $first !== '' || $last !== '') {
                $builder->groupStart();
                if ($fullName !== '') { $builder->like('test_name', $fullName); }
                if ($first !== '') { $builder->orLike('test_name', $first); }
                if ($last !== '') { $builder->orLike('test_name', $last); }
                if ($name !== '') { $builder->orLike('test_name', $name); }
                $builder->groupEnd();
            }

            $rows = $builder->get()->getResultArray();

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
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin'])) {
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

            $data = [
                'title' => 'View Test Result',
                'user' => session()->get('username'),
                'role' => session()->get('role'),
                'testResult' => $testResult
            ];

            return view('Roles/admin/laboratory/ViewTestResult', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get test result: ' . $e->getMessage());
            return redirect()->to('laboratory/testresult')->with('error', 'Failed to load test result');
        }
    }

    public function addTestResult($testId = null)
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'doctor', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        // Accept test_id from POST body if route parameter is missing
        if (empty($testId)) {
            $testId = $this->request->getPost('test_id');
        }
        if (empty($testId)) {
            return redirect()->to('laboratory/testresult')->with('error', 'Test ID is required');
        }

        // Handle form submission
        if (strtolower($this->request->getMethod()) === 'post') {
            try {

                // Get test parameters from form
                $testParameters = [];
                $normalRanges = [];
                $parameterNames = $this->request->getPost('parameter_name') ?: [];
                $parameterResults = $this->request->getPost('parameter_result') ?: [];
                $parameterRanges = $this->request->getPost('parameter_range') ?: [];

                // Build test results array
                if (is_array($parameterNames)) {
                    foreach ($parameterNames as $index => $name) {
                        if (!empty($name) && isset($parameterResults[$index])) {
                            $testParameters[$name] = $parameterResults[$index];
                            if (isset($parameterRanges[$index])) {
                                $normalRanges[$name] = $parameterRanges[$index];
                            }
                        }
                    }
                }

                // Capture notes and (optional) interpretation from form
                $notes = $this->request->getPost('notes');
                $interpretation = $this->request->getPost('interpretation');
                if ((empty($notes) || trim($notes) === '') && !empty($interpretation)) {
                    $notes = $interpretation;
                }

                $updateData = [
                    'test_results' => json_encode($testParameters),
                    'normal_range' => json_encode($normalRanges),
                    'notes' => $notes,
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $result = $this->labModel->update($testId, $updateData);

                if ($result) {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Test result added successfully and status updated to Completed'
                        ]);
                    }
                    // Redirect to the detailed view so the user immediately sees the saved results
                    return redirect()->to('laboratory/testresult/view/' . $testId)
                        ->with('success', 'Test result added successfully and status updated to Completed');
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

            $data = [
                'title' => 'Add Test Result',
                'user' => session()->get('username'),
                'role' => session()->get('role'),
                'testResult' => $testResult
            ];

            return view('Roles/admin/laboratory/AddTestResult', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load test for result entry: ' . $e->getMessage());
            return redirect()->to('laboratory/testresult')->with('error', 'Failed to load test');
        }
    }

                

    
}
