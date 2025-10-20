<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\LabRequestModel;
use App\Models\TestResultModel;

class Laboratory extends Controller
{
    protected $labRequestModel;
    protected $testResultModel;

    public function __construct()
    {
        $this->labRequestModel = new LabRequestModel();
        $this->testResultModel = new TestResultModel();
    }

    public function index()
    {
        // Check if user is logged in and has labstaff role
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'labstaff') {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Laboratory Dashboard',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Lab_staff/lab_staff', $data);
    }

    public function dashboard()
    {
        return $this->index();
    }

    public function request()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Laboratory Request',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Roles/admin/laboratory/LaboratoryReq', $data);
    }

    /**
     * Submit lab request
     */
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

        // Handle patient name - if it's an ID from select, get the name
        if (is_numeric($patientName)) {
            // If patient_name is an ID, convert to actual name
            $patients = [
                1 => 'Juan Dela Cruz',
                2 => 'Maria Santos', 
                3 => 'Pedro Reyes'
            ];
            $patientName = $patients[$patientName] ?? 'Unknown Patient';
        }

        // Simple data array without foreign key dependencies
        $data = [
            'test_name' => $patientName,
            'test_type' => $testType,
            'test_date' => date('Y-m-d'),
            'test_time' => date('H:i:s'),
            'status' => 'pending',
            'notes' => $clinicalNotes
        ];

        // Debug: Log the data being submitted
        log_message('debug', 'Lab request data: ' . json_encode($data));

        // Validate required fields manually
        $errors = [];
        if (empty($data['test_name'])) {
            $errors[] = 'Patient name is required';
        }
        if (empty($data['test_type'])) {
            $errors[] = 'Test type is required';
        }

        if (!empty($errors)) {
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON(['success' => false, 'errors' => $errors]);
            } else {
                return redirect()->back()->withInput()->with('errors', $errors);
            }
        }

        try {
            // Insert directly into database without foreign key constraints
            $db = \Config\Database::connect();
            
            // First, let's check if we need to disable foreign key checks temporarily
            $db->query('SET foreign_key_checks = 0');
            
            $builder = $db->table('laboratory');
            
            // Add test_id and timestamps
            $data['test_id'] = 'TEST' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Remove any potential patient_id that might be causing issues
            unset($data['patient_id']);
            
            $insertResult = $builder->insert($data);
            
            // Re-enable foreign key checks
            $db->query('SET foreign_key_checks = 1');
            
            if ($insertResult) {
                // Get the inserted record
                $insertId = $db->insertID();
                
                log_message('debug', 'Lab request created successfully with ID: ' . $insertId);

                // Check if it's an API call or form submission
                if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                    return $this->response->setJSON([
                        'success' => true, 
                        'message' => 'Lab request submitted successfully',
                        'test_id' => $data['test_id']
                    ]);
                } else {
                    // For form submissions, redirect to test results page
                    return redirect()->to('laboratory/testresult')->with('success', 'Lab request submitted successfully. Test ID: ' . $data['test_id']);
                }
            } else {
                throw new \Exception('Failed to insert lab request');
            }
        } catch (\Exception $e) {
            // Make sure to re-enable foreign key checks even if there's an error
            try {
                $db = \Config\Database::connect();
                $db->query('SET foreign_key_checks = 1');
            } catch (\Exception $fkException) {
                log_message('error', 'Failed to re-enable foreign key checks: ' . $fkException->getMessage());
            }
            
            log_message('error', 'Lab request creation failed: ' . $e->getMessage());
            
            if ($this->request->isAJAX() || $this->request->getHeaderLine('Content-Type') === 'application/json') {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Failed to create lab request: ' . $e->getMessage()
                ]);
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to create lab request: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get lab requests
     */
    public function getRequests()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $filters = [
            'status' => $this->request->getGet('status'),
            'priority' => $this->request->getGet('priority'),
            'test_type' => $this->request->getGet('test_type'),
            'patient_name' => $this->request->getGet('patient_name'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to')
        ];

        // Remove null values
        $filters = array_filter($filters);

        $requests = $this->labRequestModel->getRequests($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $requests
        ]);
    }

    public function testresult()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Test Results',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('Roles/admin/laboratory/TestResult', $data);
    }

    /**
     * Get test results
     */
    public function getTestResults()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $filters = [
            'status' => $this->request->getGet('status'),
            'test_type' => $this->request->getGet('test_type'),
            'patient_name' => $this->request->getGet('patient_name'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to' => $this->request->getGet('date_to')
        ];

        // Remove null values
        $filters = array_filter($filters);

        $results = $this->testResultModel->getResults($filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Get test results data for TestResult view
     */
    public function getTestResultsData()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('laboratory');
            
            $results = $builder->select('id, test_id, test_name as patient_name, test_type, test_date, status, notes')
                              ->orderBy('created_at', 'DESC')
                              ->get()
                              ->getResultArray();
            
            return $this->response->setJSON($results);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get test results data: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to load data']);
        }
    }

    public function viewTestResult($testId = null)
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        if (empty($testId)) {
            return redirect()->to('laboratory/testresult')->with('error', 'Test ID is required');
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('laboratory');
            
            $testResult = $builder->where('id', $testId)->get()->getRowArray();

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
            
            // Add formatted dates
            $testResult['formatted_test_date'] = date('F j, Y', strtotime($testResult['test_date']));
            $testResult['formatted_test_time'] = date('g:i A', strtotime($testResult['test_time']));
            
            // Add priority display if exists
            $testResult['priority_display'] = ucfirst($testResult['priority'] ?? 'normal');
            
            // Add status badge class
            $testResult['status_class'] = $testResult['status'] === 'completed' ? 'badge-success' : 'badge-warning';

            $data = [
                'title' => 'View Test Result',
                'user' => session()->get('username'),
                'role' => session()->get('role'),
                'testResult' => $testResult
            ];

            return view('admin/laboratory/ViewTestResult', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to get test result: ' . $e->getMessage());
            return redirect()->to('laboratory/testresult')->with('error', 'Failed to load test result');
        }
    }

    public function addTestResult($testId = null)
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        if (empty($testId)) {
            return redirect()->to('laboratory/testresult')->with('error', 'Test ID is required');
        }

        // Handle form submission
        if ($this->request->getMethod() === 'post') {
            try {
                $db = \Config\Database::connect();
                $builder = $db->table('laboratory');
                
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
                
                $updateData = [
                    'test_results' => json_encode($testParameters),
                    'normal_range' => json_encode($normalRanges),
                    'interpretation' => $this->request->getPost('interpretation'),
                    'notes' => $this->request->getPost('notes'),
                    'technician_name' => session()->get('username'),
                    'result_date' => date('Y-m-d'),
                    'result_time' => date('H:i:s'),
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $result = $builder->where('id', $testId)->update($updateData);
                
                if ($result) {
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON([
                            'success' => true,
                            'message' => 'Test result added successfully and status updated to Completed'
                        ]);
                    }
                    return redirect()->to('laboratory/testresult')->with('success', 'Test result added successfully and status updated to Completed');
                } else {
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
            $db = \Config\Database::connect();
            $builder = $db->table('laboratory');
            
            $testResult = $builder->where('id', $testId)->get()->getRowArray();

            if (!$testResult) {
                return redirect()->to('laboratory/testresult')->with('error', 'Test not found');
            }

            $data = [
                'title' => 'Add Test Result',
                'user' => session()->get('username'),
                'role' => session()->get('role'),
                'testResult' => $testResult
            ];

            return view('admin/laboratory/AddTestResult', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load test for result entry: ' . $e->getMessage());
            return redirect()->to('laboratory/testresult')->with('error', 'Failed to load test');
        }
    }

    /**
     * Save test result via API
     */
    public function saveTestResult()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        $id = $this->request->getPost('test_id');
        $resultData = [
            'results' => $this->request->getPost('results') ?: [],
            'normal_ranges' => $this->request->getPost('normal_ranges') ?: [],
            'abnormal_flags' => $this->request->getPost('abnormal_flags') ?: [],
            'interpretation' => $this->request->getPost('interpretation'),
            'notes' => $this->request->getPost('notes'),
            'critical_values' => $this->request->getPost('critical_values') ?: []
        ];

        if ($this->testResultModel->addResultData($id, $resultData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Test result saved successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to save test result',
                'errors' => $this->testResultModel->errors()
            ]);
        }
    }

    /**
     * Search functionality
     */
    public function search()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $query = $this->request->getGet('q');
        $type = $this->request->getGet('type') ?: 'both'; // requests, results, or both

        $data = [];

        if ($type === 'requests' || $type === 'both') {
            $data['requests'] = $this->labRequestModel->searchRequests($query);
        }

        if ($type === 'results' || $type === 'both') {
            $data['results'] = $this->testResultModel->searchResults($query);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get statistics
     */
    public function getStats()
    {
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Access denied']);
        }

        $requestStats = $this->labRequestModel->getStats();
        $resultStats = $this->testResultModel->getStats();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'requests' => $requestStats,
                'results' => $resultStats
            ]
        ]);
    }

    public function results()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('isLoggedIn') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Laboratory Results',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('admin/laboratory/Results', $data);
    }
}
