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
     * Unified Reports Interface - Single page for all reports
     */
    public function index()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

        $reportType = $this->request->getGet('type') ?? 'revenue';
        
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
                if ($role !== 'admin') {
                    return redirect()->to('login')->with('error', 'Unauthorized access');
                }
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
        ];

        return view('Roles/admin/Reports/index', $data);
    }

    /**
     * Financial Reports Dashboard
     */
    public function financial()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

        $data = [
            'title' => 'Financial Reports',
            'active_menu' => 'reports',
        ];

        return view('Roles/admin/Reports/financial', $data);
    }

    /**
     * Revenue Report
     */
    public function revenue()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
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

        return view('Roles/admin/Reports/revenue', $data);
    }

    /**
     * Expenses Report
     */
    public function expenses()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/expenses', $data);
    }

    /**
     * Profit & Loss Statement
     */
    public function profitLoss()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/profit_loss', $data);
    }

    /**
     * Outstanding Payments Report
     */
    public function outstandingPayments()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting', 'receptionist'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/outstanding_payments', $data);
    }

    /**
     * Patient Statistics Report
     */
    public function patientStats()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor', 'nurse'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/patient_statistics', $data);
    }

    /**
     * Patient Visits Report
     */
    public function patientVisits()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor', 'nurse', 'receptionist'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/patient_visits', $data);
    }

    /**
     * Patient Medical History Report
     */
    public function patientHistory()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/patient_history', $data);
    }

    /**
     * Appointment Statistics Report
     */
    public function appointmentStats()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor', 'nurse', 'receptionist'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/appointment_statistics', $data);
    }

    /**
     * Doctor Schedule Utilization Report
     */
    public function doctorScheduleUtilization()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/doctor_schedule_utilization', $data);
    }

    /**
     * Laboratory Tests Report
     */
    public function laboratoryTests()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor', 'labstaff'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/laboratory_tests', $data);
    }

    /**
     * Test Results Summary Report
     */
    public function testResults()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/test_results', $data);
    }

    /**
     * Prescription Report
     */
    public function prescriptions()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor', 'pharmacist'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/prescriptions', $data);
    }

    /**
     * Medicine Inventory Report
     */
    public function medicineInventory()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'pharmacist'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/medicine_inventory', $data);
    }

    /**
     * Medicine Sales Report
     */
    public function medicineSales()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'pharmacist'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/medicine_sales', $data);
    }

    /**
     * Admission Report
     */
    public function admissions()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'nurse', 'receptionist'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/admissions', $data);
    }

    /**
     * Discharge Report
     */
    public function discharges()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'doctor', 'nurse'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/discharges', $data);
    }

    /**
     * Doctor Performance Report
     */
    public function doctorPerformance()
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->to('login')->with('error', 'Unauthorized access');
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

        return view('Roles/admin/Reports/doctor_performance', $data);
    }

    /**
     * Staff Activity Report
     */
    public function staffActivity()
    {
        $role = session()->get('role');
        if ($role !== 'admin') {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/staff_activity', $data);
    }

    /**
     * PhilHealth Claims Report
     */
    public function philhealthClaims()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/philhealth_claims', $data);
    }

    /**
     * HMO Claims Report
     */
    public function hmoClaims()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'accounting'])) {
            return redirect()->to('login')->with('error', 'Unauthorized access');
        }

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

        return view('Roles/admin/Reports/hmo_claims', $data);
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
}

