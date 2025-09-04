<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Laboratory extends Controller
{
    public function index()
    {
        // Check if user is logged in and has labstaff role
        if (!session()->get('logged_in') || session()->get('role') !== 'labstaff') {
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
        if (!session()->get('logged_in') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Laboratory Request',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('admin/laboratory/LaboratoryReq', $data);
    }

    public function testresult()
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('logged_in') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        $data = [
            'title' => 'Test Results',
            'user' => session()->get('username'),
            'role' => session()->get('role')
        ];

        return view('admin/laboratory/TestResult', $data);
    }

    public function viewTestResult($id = null)
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('logged_in') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        if (empty($id)) {
            return redirect()->to('laboratory/testresult')->with('error', 'Test result ID is required');
        }

        // TODO: Fetch the test result with the given $id from your database
        // $testResult = $this->testResultModel->find($id);
        
        // For now, we'll use sample data
        $testResult = [
            'id' => $id,
            'test_name' => 'Complete Blood Count',
            'patient_name' => 'John Doe',
            'test_date' => '2023-09-04',
            'result_date' => '2023-09-05',
            'status' => 'Completed',
            'results' => [
                'Hemoglobin' => '14.5 g/dL',
                'Hematocrit' => '42.5%',
                'White Blood Cells' => '6.5 x 10³/µL',
                'Red Blood Cells' => '4.8 x 10⁶/µL',
                'Platelets' => '250 x 10³/µL'
            ],
            'notes' => 'Results are within normal ranges.'
        ];

        $data = [
            'title' => 'View Test Result',
            'user' => session()->get('username'),
            'role' => session()->get('role'),
            'testResult' => $testResult
        ];

        return view('admin/laboratory/ViewTestResult', $data);
    }

    public function addTestResult($id = null)
    {
        // Check if user is logged in and has appropriate role
        if (!session()->get('logged_in') || !in_array(session()->get('role'), ['labstaff', 'admin'])) {
            return redirect()->to('/login')->with('error', 'Access denied. You do not have permission to access this page.');
        }

        if (empty($id)) {
            return redirect()->to('laboratory/testresult')->with('error', 'Test ID is required');
        }

        // Handle form submission
        if ($this->request->getMethod() === 'post') {
            // TODO: Validate and save the test result data
            // $testData = $this->request->getPost();
            // $this->testModel->saveResult($id, $testData);
            
            // For now, just redirect back with success message
            return redirect()->to('laboratory/testresult')->with('success', 'Test result added successfully');
        }

        // For GET request, show the add result form
        $data = [
            'title' => 'Add Test Result',
            'user' => session()->get('username'),
            'role' => session()->get('role'),
            'testId' => $id,
            // TODO: Fetch test details from database
            'testDetails' => [
                'id' => $id,
                'patient_name' => 'Maria Santos',
                'test_type' => 'Urinalysis',
                'test_date' => date('Y-m-d'),
                'status' => 'Pending'
            ]
        ];

        return view('admin/laboratory/AddTestResult', $data);
    }
}
