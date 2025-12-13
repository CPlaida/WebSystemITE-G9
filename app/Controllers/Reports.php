<?php

namespace App\Controllers;

use App\Models\BillingModel;
use App\Models\PatientModel;
use App\Models\AppointmentModel;
use App\Models\LaboratoryModel;
use App\Models\PrescriptionModel;
use App\Models\MedicineModel;
use App\Models\AdmissionModel;
use App\Models\DoctorModel;
use App\Models\ServiceModel;
use App\Models\Financial\PhilHealthAuditModel;
use App\Models\Financial\HmoAuthorizationModel;
use App\Models\Financial\HmoProviderModel;
use App\Models\TransactionModel;
use App\Models\PatientVitalModel;

class Reports extends BaseController
{
    protected $billingModel;
    protected $patientModel;
    protected $appointmentModel;
    protected $laboratoryModel;
    protected $prescriptionModel;
    protected $medicineModel;
    protected $admissionModel;
    protected $doctorModel;

    public function __construct()
    {
        $this->billingModel = new BillingModel();
        $this->patientModel = new PatientModel();
        $this->appointmentModel = new AppointmentModel();
        $this->laboratoryModel = new LaboratoryModel();
        $this->prescriptionModel = new PrescriptionModel();
        $this->medicineModel = new MedicineModel();
        $this->admissionModel = new AdmissionModel();
        $this->doctorModel = new DoctorModel();
        helper(['form', 'url']);
    }

    /**
     * Get role-based view path
     */
    protected function getRoleViewPath(string $viewName): string
    {
        $role = session('role');
        $roleMap = [
            'admin' => 'admin',
            'accounting' => 'admin', // Accountants use admin views (unified)
            'accountant' => 'admin', // Accountants use admin views (unified)
            'doctor' => 'admin',
            'nurse' => 'admin',
            'receptionist' => 'admin',
            'labstaff' => 'admin',
            'pharmacist' => 'admin',
            'itstaff' => 'admin',
        ];
        $roleFolder = $roleMap[$role] ?? 'admin';
        return "Roles/{$roleFolder}/Reports/{$viewName}";
    }

    /**
     * Get role-to-report mapping
     * 
     * @return array
     */
    protected function getRoleReportMapping(): array
    {
        return [
            'admin' => [
                'revenue',
                'outstanding-payments',
                'patient-statistics',
                'appointment-statistics',
                'laboratory-tests',
                'prescriptions',
                'medicine-inventory',
                'medicine-sales',
                'admissions',
                'discharges',
                'doctor-performance',
                'philhealth-claims',
                'hmo-claims',
            ],
            'itstaff' => [
                'revenue',
                'outstanding-payments',
                'patient-statistics',
                'appointment-statistics',
                'laboratory-tests',
                'prescriptions',
                'medicine-inventory',
                'medicine-sales',
                'admissions',
                'discharges',
                'doctor-performance',
                'philhealth-claims',
                'hmo-claims',
            ],
            'accountant' => [
                'revenue',
                'outstanding-payments',
                'philhealth-claims',
                'hmo-claims',
            ],
            'accounting' => [
                'revenue',
                'outstanding-payments',
                'philhealth-claims',
                'hmo-claims',
            ],
            'doctor' => [
                'patient-statistics',
                'appointment-statistics',
                'prescriptions',
                'doctor-performance',
            ],
            'nurse' => [
                'patient-statistics',
                'admissions',
                'discharges',
            ],
            'receptionist' => [
                'appointment-statistics',
                'admissions',
                'discharges',
            ],
            'labstaff' => [
                'laboratory-tests',
            ],
            'pharmacist' => [
                'medicine-inventory',
                'medicine-sales',
            ],
        ];
    }

    /**
     * Get allowed reports for current user role
     * 
     * @return array
     */
    protected function getAllowedReports(): array
    {
        $role = session('role');
        $mapping = $this->getRoleReportMapping();
        return $mapping[$role] ?? [];
    }

    /**
     * Check if current user can access a specific report
     * 
     * @param string $reportType
     * @return bool
     */
    protected function canAccessReport(string $reportType): bool
    {
        $allowedReports = $this->getAllowedReports();
        return in_array($reportType, $allowedReports, true);
    }

    /**
     * Get report display names
     * 
     * @return array
     */
    protected function getReportNames(): array
    {
        return [
            'revenue' => 'Revenue Report',
            'outstanding-payments' => 'Outstanding Payments',
            'patient-statistics' => 'Patient Statistics',
            'appointment-statistics' => 'Appointment Statistics',
            'laboratory-tests' => 'Laboratory Tests',
            'prescriptions' => 'Prescriptions',
            'medicine-inventory' => 'Medicine Inventory',
            'medicine-sales' => 'Medicine Sales',
            'admissions' => 'Admissions',
            'discharges' => 'Discharges',
            'doctor-performance' => 'Doctor Performance',
            'philhealth-claims' => 'PhilHealth Claims',
            'hmo-claims' => 'HMO Claims',
        ];
    }

    /**
     * Unified Reports Interface - Single page for all reports
     */
    public function index()
    {
        // Allow all authenticated roles to access reports page
        $allowedRoles = ['admin', 'accounting', 'accountant', 'itstaff', 'doctor', 'nurse', 'receptionist', 'labstaff', 'pharmacist'];
        $this->requireRole($allowedRoles);

        $reportType = $this->request->getGet('type') ?? '';
        $allowedReports = $this->getAllowedReports();
        
        // If no report type specified, use first allowed report
        if (empty($reportType) && !empty($allowedReports)) {
            $reportType = $allowedReports[0];
        }
        
        // Validate report access - block unauthorized report requests
        if (!empty($reportType) && !$this->canAccessReport($reportType)) {
            return redirect()->to('reports')->with('error', 'You do not have permission to access this report.');
        }
        
        // If user has no allowed reports, show message
        if (empty($allowedReports)) {
            $data = [
                'title' => 'Reports',
                'active_menu' => 'reports',
                'reportType' => null,
                'reportData' => [],
                'filters' => [],
                'allowedReports' => [],
                'reportNames' => $this->getReportNames(),
            ];
            return view($this->getRoleViewPath('index'), $data);
        }
        
        // Ensure reportType is set to a valid allowed report
        if (empty($reportType) || !in_array($reportType, $allowedReports, true)) {
            $reportType = $allowedReports[0];
        }
        
        // Get report data based on type
        $reportData = [];
        $filters = [];
        
        switch ($reportType) {
            case 'revenue':
                $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
                $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
                $filters = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'payment_method' => $this->request->getGet('payment_method') ?? '',
                    'payment_status' => $this->request->getGet('payment_status') ?? '',
                ];
                $reportData = $this->billingModel->getRevenueReport($startDate, $endDate, $filters);
                break;
                
            case 'outstanding-payments':
                $filters = [
                    'payment_status' => $this->request->getGet('payment_status') ?? '',
                    'patient_id' => $this->request->getGet('patient_id') ?? '',
                    'start_date' => $this->request->getGet('start_date') ?? '',
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                ];
                $reportData = $this->billingModel->getOutstandingPayments($filters);
                break;
                
            case 'patient-statistics':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? '',
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'patient_type' => $this->request->getGet('patient_type') ?? '',
                    'gender' => $this->request->getGet('gender') ?? '',
                ];
                $reportData = $this->patientModel->getPatientStatistics($filters);
                break;
                
            case 'appointment-statistics':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'doctor_id' => $this->request->getGet('doctor_id') ?? '',
                    'status' => $this->request->getGet('status') ?? '',
                    'appointment_type' => $this->request->getGet('appointment_type') ?? '',
                ];
                $reportData = $this->appointmentModel->getAppointmentStatistics($filters);
                break;
                
            case 'laboratory-tests':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'test_type' => $this->request->getGet('test_type') ?? '',
                    'status' => $this->request->getGet('status') ?? '',
                ];
                $reportData = $this->laboratoryModel->getTestStatistics($filters);
                break;
                
            case 'prescriptions':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'doctor_id' => $this->request->getGet('doctor_id') ?? '',
                    'medicine_id' => $this->request->getGet('medicine_id') ?? '',
                ];
                $reportData = $this->prescriptionModel->getPrescriptionStatistics($filters);
                break;
                
            case 'medicine-inventory':
                $filters = [
                    'category' => $this->request->getGet('category') ?? '',
                    'stock_status' => $this->request->getGet('stock_status') ?? '',
                ];
                $reportData = $this->medicineModel->getInventoryReport($filters);
                break;
                
            case 'medicine-sales':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'medicine_id' => $this->request->getGet('medicine_id') ?? '',
                ];
                $reportData = $this->medicineModel->getSalesReport($filters);
                break;
                
            case 'admissions':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'department' => $this->request->getGet('department') ?? '',
                ];
                $reportData = $this->admissionModel->getAdmissionStatistics($filters);
                break;
                
            case 'discharges':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'department' => $this->request->getGet('department') ?? '',
                ];
                $reportData = $this->admissionModel->getDischargeStatistics($filters);
                break;
                
            case 'doctor-performance':
                // Permission check is already done in index() method via canAccessReport()
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'doctor_id' => $this->request->getGet('doctor_id') ?? '',
                ];
                $reportData = $this->doctorModel->getPerformanceReport($filters);
                break;
                
            case 'philhealth-claims':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'claim_status' => $this->request->getGet('claim_status') ?? '',
                ];
                $philhealthModel = new PhilHealthAuditModel();
                $reportData = $philhealthModel->getClaimsReport($filters);
                break;
                
            case 'hmo-claims':
                $filters = [
                    'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
                    'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
                    'hmo_provider_id' => $this->request->getGet('hmo_provider_id') ?? '',
                    'status' => $this->request->getGet('status') ?? '',
                ];
                $hmoAuthModel = new HmoAuthorizationModel();
                $reportData = $hmoAuthModel->getClaimsReport($filters);
                break;
                
        }

        $data = [
            'title' => 'Reports',
            'active_menu' => 'reports',
            'reportType' => $reportType,
            'reportData' => $reportData,
            'filters' => $filters,
            'allowedReports' => $allowedReports,
            'reportNames' => $this->getReportNames(),
        ];

        return view($this->getRoleViewPath('index'), $data);
    }

    /**
     * Financial Reports Dashboard
     */
    public function financial()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);

        $data = [
            'title' => 'Financial Reports',
            'active_menu' => 'reports',
        ];

        return view($this->getRoleViewPath('financial'), $data);
    }

    /**
     * Revenue Report
     */
    /**
     * Revenue Report (Income Report for Accountant)
     */
    public function revenue()
    {
        $allowedRoles = ['admin', 'accounting', 'accountant', 'itstaff', 'doctor', 'nurse', 'receptionist', 'labstaff', 'pharmacist'];
        $this->requireRole($allowedRoles);
        
        // Check if user can access this specific report
        if (!$this->canAccessReport('revenue')) {
            return redirect()->to('reports')->with('error', 'You do not have permission to access this report.');
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        $paymentMethod = $this->request->getGet('payment_method') ?? '';
        $paymentStatus = $this->request->getGet('payment_status') ?? '';

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
        ];

        $reportData = $this->billingModel->getRevenueReport($startDate, $endDate, $filters);

        $data = [
            'title' => 'Revenue Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        $role = session('role');
        $viewName = in_array($role, ['accounting', 'accountant']) ? 'Income' : 'revenue';
        return view($this->getRoleViewPath($viewName), $data);
    }

    /**
     * Income Report (Accountant alias for revenue)
     */
    public function income()
    {
        return $this->revenue();
    }

    /**
     * Expenses Report
     */
    public function expenses()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $reportData = $this->medicineModel->getExpenseReport($startDate, $endDate);

        $data = [
            'title' => 'Expense Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('expenses'), $data);
    }

    /**
     * Profit & Loss Statement
     */
    public function profitLoss()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $revenue = $this->billingModel->getRevenueReport($startDate, $endDate, []);
        $expenses = $this->medicineModel->getExpenseReport($startDate, $endDate);

        $totalRevenue = $revenue['total_revenue'] ?? 0;
        $totalExpenses = $expenses['total_expenses'] ?? 0;
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        $data = [
            'title' => 'Profit & Loss Statement',
            'active_menu' => 'reports',
            'totalRevenue' => $totalRevenue,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        return view($this->getRoleViewPath('profit_loss'), $data);
    }

    /**
     * Outstanding Payments Report
     */
    public function outstandingPayments()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'receptionist']);

        $filters = [
            'payment_status' => $this->request->getGet('payment_status') ?? '',
            'patient_id' => $this->request->getGet('patient_id') ?? '',
            'start_date' => $this->request->getGet('start_date') ?? '',
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
        ];

        $reportData = $this->billingModel->getOutstandingPayments($filters);

        $data = [
            'title' => 'Outstanding Payments Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('outstanding_payments'), $data);
    }

    /**
     * Patient Statistics Report
     */
    public function patientStats()
    {
        $this->requireRole(['admin', 'doctor', 'nurse']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? '',
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'patient_type' => $this->request->getGet('patient_type') ?? '',
            'gender' => $this->request->getGet('gender') ?? '',
        ];

        $reportData = $this->patientModel->getPatientStatistics($filters);

        $data = [
            'title' => 'Patient Statistics Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('patient_statistics'), $data);
    }

    /**
     * Patient Visits Report
     */
    public function patientVisits()
    {
        $this->requireRole(['admin', 'doctor', 'nurse', 'receptionist']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'patient_id' => $this->request->getGet('patient_id') ?? '',
            'doctor_id' => $this->request->getGet('doctor_id') ?? '',
        ];

        $reportData = $this->appointmentModel->getVisitStatistics($filters);

        $data = [
            'title' => 'Patient Visits Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('patient_visits'), $data);
    }

    /**
     * Patient Medical History Report
     */
    public function patientHistory()
    {
        $this->requireRole(['admin', 'doctor']);

        $patientId = $this->request->getGet('patient_id');
        if (!$patientId) {
            return redirect()->back()->with('error', 'Patient ID is required');
        }

        $startDate = $this->request->getGet('start_date') ?? '';
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $patient = $this->patientModel->find($patientId);
        if (!$patient) {
            return redirect()->back()->with('error', 'Patient not found');
        }

        $reportData = $this->patientModel->getPatientHistory($patientId, $startDate, $endDate);

        $data = [
            'title' => 'Patient Medical History',
            'active_menu' => 'reports',
            'patient' => $patient,
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        return view($this->getRoleViewPath('patient_history'), $data);
    }

    /**
     * Appointment Statistics Report
     */
    public function appointmentStats()
    {
        $this->requireRole(['admin', 'doctor', 'nurse', 'receptionist']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'doctor_id' => $this->request->getGet('doctor_id') ?? '',
            'status' => $this->request->getGet('status') ?? '',
            'appointment_type' => $this->request->getGet('appointment_type') ?? '',
        ];

        $reportData = $this->appointmentModel->getAppointmentStatistics($filters);

        $data = [
            'title' => 'Appointment Statistics Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('appointment_statistics'), $data);
    }

    /**
     * Doctor Schedule Utilization Report
     */
    public function doctorScheduleUtilization()
    {
        $this->requireRole(['admin', 'doctor']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'doctor_id' => $this->request->getGet('doctor_id') ?? '',
        ];

        $reportData = $this->appointmentModel->getScheduleUtilization($filters);

        $data = [
            'title' => 'Doctor Schedule Utilization Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('doctor_schedule_utilization'), $data);
    }

    /**
     * Laboratory Tests Report
     */
    public function laboratoryTests()
    {
        $this->requireRole(['admin', 'doctor', 'labstaff']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'test_type' => $this->request->getGet('test_type') ?? '',
            'status' => $this->request->getGet('status') ?? '',
        ];

        $reportData = $this->laboratoryModel->getTestStatistics($filters);

        $data = [
            'title' => 'Laboratory Tests Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('laboratory_tests'), $data);
    }

    /**
     * Test Results Summary Report
     */
    public function testResults()
    {
        $this->requireRole(['admin', 'doctor']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'test_type' => $this->request->getGet('test_type') ?? '',
        ];

        $reportData = $this->laboratoryModel->getTestResultsSummary($filters);

        $data = [
            'title' => 'Test Results Summary Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('test_results'), $data);
    }

    /**
     * Prescription Report
     */
    public function prescriptions()
    {
        $this->requireRole(['admin', 'doctor', 'pharmacist']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'doctor_id' => $this->request->getGet('doctor_id') ?? '',
            'medicine_id' => $this->request->getGet('medicine_id') ?? '',
        ];

        $reportData = $this->prescriptionModel->getPrescriptionStatistics($filters);

        $data = [
            'title' => 'Prescription Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('prescriptions'), $data);
    }

    /**
     * Medicine Inventory Report
     */
    public function medicineInventory()
    {
        $this->requireRole(['admin', 'pharmacist']);

        $filters = [
            'category' => $this->request->getGet('category') ?? '',
            'stock_status' => $this->request->getGet('stock_status') ?? '',
        ];

        $reportData = $this->medicineModel->getInventoryReport($filters);

        $data = [
            'title' => 'Medicine Inventory Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('medicine_inventory'), $data);
    }

    /**
     * Medicine Sales Report
     */
    public function medicineSales()
    {
        $this->requireRole(['admin', 'pharmacist']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'medicine_id' => $this->request->getGet('medicine_id') ?? '',
        ];

        $reportData = $this->medicineModel->getSalesReport($filters);

        $data = [
            'title' => 'Medicine Sales Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('medicine_sales'), $data);
    }

    /**
     * Admission Report
     */
    public function admissions()
    {
        $this->requireRole(['admin', 'nurse', 'receptionist']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'department' => $this->request->getGet('department') ?? '',
        ];

        $reportData = $this->admissionModel->getAdmissionStatistics($filters);

        $data = [
            'title' => 'Admission Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('admissions'), $data);
    }

    /**
     * Discharge Report
     */
    public function discharges()
    {
        $this->requireRole(['admin', 'doctor', 'nurse']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'department' => $this->request->getGet('department') ?? '',
        ];

        $reportData = $this->admissionModel->getDischargeStatistics($filters);

        $data = [
            'title' => 'Discharge Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('discharges'), $data);
    }

    /**
     * Doctor Performance Report
     */
    public function doctorPerformance()
    {
        $allowedRoles = ['admin', 'accounting', 'accountant', 'itstaff', 'doctor', 'nurse', 'receptionist', 'labstaff', 'pharmacist'];
        $this->requireRole($allowedRoles);
        
        // Check if user can access this specific report
        if (!$this->canAccessReport('doctor-performance')) {
            return redirect()->to('reports')->with('error', 'You do not have permission to access this report.');
        }

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'doctor_id' => $this->request->getGet('doctor_id') ?? '',
        ];

        $reportData = $this->doctorModel->getPerformanceReport($filters);

        $data = [
            'title' => 'Doctor Performance Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('doctor_performance'), $data);
    }

    /**
     * Staff Activity Report
     */
    public function staffActivity()
    {
        $this->requireRole(['admin']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'staff_id' => $this->request->getGet('staff_id') ?? '',
        ];

        // This would require activity logging - placeholder implementation
        $reportData = [];

        $data = [
            'title' => 'Staff Activity Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('staff_activity'), $data);
    }

    /**
     * PhilHealth Claims Report
     */
    public function philhealthClaims()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'claim_status' => $this->request->getGet('claim_status') ?? '',
        ];

        $philhealthModel = new PhilHealthAuditModel();
        $reportData = $philhealthModel->getClaimsReport($filters);

        $data = [
            'title' => 'PhilHealth Claims Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('philhealth_claims'), $data);
    }

    /**
     * HMO Claims Report
     */
    public function hmoClaims()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);

        $filters = [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            'hmo_provider_id' => $this->request->getGet('hmo_provider_id') ?? '',
            'status' => $this->request->getGet('status') ?? '',
        ];

        $hmoAuthModel = new HmoAuthorizationModel();
        $reportData = $hmoAuthModel->getClaimsReport($filters);

        $data = [
            'title' => 'HMO Claims Report',
            'active_menu' => 'reports',
            'reportData' => $reportData,
            'filters' => $filters,
        ];

        return view($this->getRoleViewPath('hmo_claims'), $data);
    }

    /**
     * Export PDF
     */
    public function exportPdf($reportType)
    {
        // PDF export implementation would go here
        // For now, redirect back
        return redirect()->back()->with('info', 'PDF export feature coming soon');
    }

    /**
     * Export Excel
     */
    public function exportExcel($reportType)
    {
        // Excel export implementation would go here
        // For now, redirect back
        return redirect()->back()->with('info', 'Excel export feature coming soon');
    }

    /**
     * Export Income PDF (from Accountant controller)
     */
    public function exportIncomePdf()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);
        
        $fromDate = $this->request->getGet('from');
        $toDate = $this->request->getGet('to');
        
        $filters = [
            'start_date' => $fromDate ?? date('Y-m-01'),
            'end_date' => $toDate ?? date('Y-m-d'),
        ];
        
        $reportData = $this->billingModel->getRevenueReport($filters['start_date'], $filters['end_date'], $filters);
        
        $data = [
            'title' => 'Income Report',
            'fromDate' => $filters['start_date'],
            'toDate' => $filters['end_date'],
            'reportData' => $reportData,
        ];
        
        try {
            $dompdf = new \Dompdf\Dompdf();
            $html = view('exports/income_pdf', $data);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("income-report-{$filters['start_date']}-to-{$filters['end_date']}.pdf", ["Attachment" => true]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Expenses PDF (from Accountant controller)
     */
    public function exportExpensesPdf()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);
        
        $fromDate = $this->request->getGet('from');
        $toDate = $this->request->getGet('to');
        $category = $this->request->getGet('category');
        
        $reportData = $this->medicineModel->getExpenseReport($fromDate ?? date('Y-m-01'), $toDate ?? date('Y-m-d'));
        
        $data = [
            'title' => 'Expenses Report',
            'fromDate' => $fromDate ?? date('Y-m-01'),
            'toDate' => $toDate ?? date('Y-m-d'),
            'category' => $category,
            'reportData' => $reportData,
        ];
        
        try {
            $dompdf = new \Dompdf\Dompdf();
            $html = view('exports/expenses_pdf', $data);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("expenses-report-{$data['fromDate']}-to-{$data['toDate']}.pdf", ["Attachment" => true]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Expenses Excel (from Accountant controller)
     */
    public function exportExpensesExcel()
    {
        $this->requireRole(['admin', 'accounting', 'accountant', 'itstaff']);
        
        $fromDate = $this->request->getGet('from');
        $toDate = $this->request->getGet('to');
        $category = $this->request->getGet('category');
        
        $reportData = $this->medicineModel->getExpenseReport($fromDate ?? date('Y-m-01'), $toDate ?? date('Y-m-d'));
        
        $data = [
            'title' => 'Expenses Report',
            'fromDate' => $fromDate ?? date('Y-m-01'),
            'toDate' => $toDate ?? date('Y-m-d'),
            'category' => $category,
            'reportData' => $reportData,
        ];
        
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Add headers
            $sheet->setCellValue('A1', 'Date');
            $sheet->setCellValue('B1', 'Category');
            $sheet->setCellValue('C1', 'Amount');
            
            // Add data (placeholder - implement based on your data structure)
            $row = 2;
            if (isset($reportData['expenses']) && is_array($reportData['expenses'])) {
                foreach ($reportData['expenses'] as $expense) {
                    $sheet->setCellValue('A' . $row, $expense['date'] ?? '');
                    $sheet->setCellValue('B' . $row, $expense['category'] ?? '');
                    $sheet->setCellValue('C' . $row, $expense['amount'] ?? 0);
                    $row++;
                }
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="expenses-report.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }
}

