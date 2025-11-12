<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Labstaff extends BaseController
{
    public function __construct()
    {
        // Load any required helpers or models here
        helper(['url', 'form']);
    }

    public function index()
    {
        return redirect()->to('labstaff/dashboard');
    }

    public function laboratoryRequest()
    {
        $data = [
            'title' => 'Laboratory Request',
            // Add any data you want to pass to the view
        ];

        return view('Roles/Lab_staff/laboratory/LaboratoryReq', $data);
    }

    public function testResult()
    {
        $data = [
            'title' => 'Test Results',
            // Add any test result data you want to pass to the view
        ];

        return view('Roles/Lab_staff/laboratory/TestResult', $data);
    }
}
