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
            // Insert directly into database
            $db = \Config\Database::connect();
            $builder = $db->table('laboratory');
            
            // Add timestamps
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $insertResult = $builder->insert($data);
            
            if ($insertResult) {
                // Get the inserted numeric ID
                $insertId = $db->insertID();
                
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
                throw new \Exception('Failed to insert lab request');
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

            // test_id column removed; expose numeric id as test_id for UI compatibility
            $results = $builder->select('id, id as test_id, test_name as patient_name, test_type, test_date, status, notes')
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
                    'notes' => $this->request->getPost('notes'),
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

            return view('Roles/admin/laboratory/AddTestResult', $data);
        } catch (\Exception $e) {
            log_message('error', 'Failed to load test for result entry: ' . $e->getMessage());
            return redirect()->to('laboratory/testresult')->with('error', 'Failed to load test');
        }
    }

                

    
}
