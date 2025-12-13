<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home Controller Routes
// Main Routes
$routes->get('/', 'Home::index');
$routes->get('services', 'Home::services');
$routes->get('doctors', 'Home::doctors');
$routes->get('contact', 'Home::contact');

// Auth Routes
$routes->get('login', 'Auth::login');
$routes->post('auth/process_login', 'Auth::process_login');
$routes->get('register', 'Auth::register');
$routes->post('auth/process_register', 'Auth::process_register');
$routes->get('logout', 'Auth::logout');
$routes->get('auth/logout', 'Auth::logout'); // Keep for backward compatibility

// Remove index.php from URL
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// Role Routes
// Only logged-in users can see this (unified dashboard)
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);
// Convenience role-specific dashboards (reuse unified dashboard)
$routes->get('doctor/dashboard', 'Dashboard::index', ['filter' => 'auth:doctor,admin']);
$routes->get('doctor/my-schedule', 'Doctor::mySchedule', ['filter' => 'auth:doctor']);
$routes->get('nurse/dashboard', 'Dashboard::index', ['filter' => 'auth:nurse,admin']);
$routes->get('receptionist/dashboard', 'Dashboard::index', ['filter' => 'auth:receptionist,admin']);
$routes->get('pharmacist/dashboard', 'Dashboard::index', ['filter' => 'auth:pharmacist,admin']);
$routes->get('labstaff/laboratory/request', 'Laboratory::laboratoryRequest', ['filter' => 'auth:labstaff,admin']);
$routes->get('labstaff/laboratory/testresult', 'Laboratory::testResult', ['filter' => 'auth:labstaff,admin']);
// Accountant Routes (unified with main controllers)
$routes->group('accountant', ['filter' => 'auth:accounting,admin'], function($routes) {
    $routes->get('dashboard', 'Dashboard::accountant');
    
    // Billing routes (unified)
    $routes->group('billing', function($routes) {
        $routes->get('process', 'Billing::process');
        $routes->get('create', 'Billing::create');
    });
    
    // Reports routes (unified)
    $routes->group('reports', function($routes) {
        $routes->get('income', 'Reports::income');
        $routes->get('expenses', 'Reports::expenses');
        $routes->get('export-income-pdf', 'Reports::exportIncomePdf');
        $routes->get('export-expenses-pdf', 'Reports::exportExpensesPdf');
        $routes->get('export-expenses-excel', 'Reports::exportExpensesExcel');
    });
});

// Backward-compatibility: legacy admin dashboard URL(admin dashboard page diffrent sa unified okii)
$routes->get('/admin/dashboard', 'Admin::index', ['filter' => 'auth:admin']);

// Admin management pages (not dashboards) - Admin and IT Staff
$routes->get('admin/Administration/ManageUser', 'Admin::manageUsers', ['filter' => 'auth:admin,itstaff']);
$routes->get('admin/Administration/StaffManagement', 'Admin::manageStaff', ['filter' => 'auth:admin,itstaff']);
// Admin user CRUD endpoints - Admin and IT Staff
$routes->post('admin/users/create', 'Admin::createUser', ['filter' => 'auth:admin,itstaff']);
$routes->get('admin/users/update', 'Admin::manageUsers', ['filter' => 'auth:admin,itstaff']);
$routes->get('admin/users/update/(:segment)', 'Admin::manageUsers', ['filter' => 'auth:admin,itstaff']);
$routes->post('admin/users/update/(:segment)', 'Admin::updateUser/$1', ['filter' => 'auth:admin,itstaff']);
$routes->post('admin/users/delete/(:segment)', 'Admin::deleteUser/$1', ['filter' => 'auth:admin,itstaff']);
$routes->post('admin/users/reset-password/(:segment)', 'Admin::resetPassword/$1', ['filter' => 'auth:admin,itstaff']);

// Admin staff management CRUD endpoints - Admin and IT Staff
$routes->post('admin/staff/create', 'Admin::createStaff', ['filter' => 'auth:admin,itstaff']);
$routes->post('admin/staff/update/(:num)', 'Admin::updateStaff/$1', ['filter' => 'auth:admin,itstaff']);
$routes->post('admin/staff/delete/(:num)', 'Admin::deleteStaff/$1', ['filter' => 'auth:admin,itstaff']);

// Doctor scheduling routes
$routes->get('/doctor/schedule', 'Doctor::schedule', ['filter' => 'auth:admin,doctor']);
$routes->get('/doctor/schedules-by-date', 'Doctor::getSchedulesByDate', ['filter' => 'auth']);
$routes->post('/doctor/addSchedule', 'Doctor::addSchedule', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/updateSchedule/(:num)', 'Doctor::updateSchedule/$1', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/deleteSchedule/(:num)', 'Doctor::deleteSchedule/$1', ['filter' => 'auth:admin,doctor']);

// Admin OR Nurse allowed
$routes->get('/nurse/reports', 'Nurse::reports', ['filter' => 'auth:admin,nurse']);

// Frontend Patient Routes
$routes->group('patients', ['namespace' => 'App\\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('register', 'Patients::register');
    $routes->post('register', 'Patients::processRegister');
    $routes->get('inpatient', 'Patients::inpatient');
    // Map to Patients index to display list and avoid 404
    $routes->get('view', 'Patients::index');
    $routes->get('search', 'Patients::search');
    $routes->get('get/(:segment)', 'Patients::getPatient/$1');
});

// Appointment Routes
$routes->get('appointments/book', 'Appointment::book');
$routes->get('appointments/list', 'Appointment::index');
$routes->get('appointments/schedule', 'Appointment::schedule');
$routes->post('appointments/create', 'Appointment::create');
$routes->get('appointments/show/(:any)', 'Appointment::show/$1');
$routes->post('appointments/update/(:any)', 'Appointment::update/$1');
$routes->post('appointments/cancel/(:any)', 'Appointment::cancel/$1');
$routes->post('appointments/complete/(:any)', 'Appointment::complete/$1');
$routes->post('appointments/no-show/(:any)', 'Appointment::noShow/$1');
$routes->post('appointments/delete/(:any)', 'Appointment::delete/$1');

// Dynamic schedule-driven endpoints for booking form
$routes->get('appointments/available-dates', 'Appointment::getAvailableDates');
$routes->get('appointments/doctors-by-date', 'Appointment::getDoctorsByDate');
$routes->get('appointments/times-by-doctor', 'Appointment::getTimesByDoctorAndDate');
$routes->get('appointments/check-patient', 'Appointment::checkPatientAppointment');

// Appointment Query Routes
$routes->get('appointments/doctor/(:num)', 'Appointment::getByDoctor/$1');
$routes->get('appointments/patient/(:num)', 'Appointment::getByPatient/$1');
$routes->get('appointments/today', 'Appointment::getTodays');
$routes->get('appointments/upcoming', 'Appointment::getUpcoming');
$routes->get('appointments/search', 'Appointment::search');
$routes->get('appointments/stats', 'Appointment::getStats');
$routes->get('appointments/by-date-range', 'Appointment::byDateRange');


// Billing Routes
$routes->get('billing', 'Billing::index', ['filter' => 'auth']);
$routes->get('billing/process', 'Billing::process', ['filter' => 'auth']);
$routes->post('billing/save', 'Billing::save', ['filter' => 'auth']);
// New normalized Billing routes
$routes->post('billing/store', 'Billing::store', ['filter' => 'auth']);
// New: store header + items in a single transaction
$routes->post('billing/store-with-items', 'Billing::storeWithItems', ['filter' => 'auth']);
$routes->get('billing/patient-services', 'Billing::patientServices', ['filter' => 'auth']);
$routes->get('billing/edit/(:num)', 'Billing::edit/$1', ['filter' => 'auth']);
$routes->post('billing/update/(:num)', 'Billing::update/$1', ['filter' => 'auth']);
$routes->get('billing/delete/(:num)', 'Billing::delete/$1', ['filter' => 'auth']);
$routes->post('billing/delete/(:num)', 'Billing::delete/$1', ['filter' => 'auth']);
$routes->get('billing/show/(:num)', 'Billing::show/$1', ['filter' => 'auth']);
$routes->get('billing/payment/(:num)', 'Billing::paymentPage/$1', ['filter' => 'auth']);
// PhilHealth case rates endpoint
$routes->get('billing/caseRates', 'Billing::caseRates', ['filter' => 'auth']);
// Payment routes
$routes->post('billing/processPayment/(:num)', 'Billing::processPayment/$1', ['filter' => 'auth']);
$routes->post('billing/processPayment', 'Billing::processPayment', ['filter' => 'auth']); // Alternative format
$routes->get('billing/getPayments/(:num)', 'Billing::getPayments/$1', ['filter' => 'auth']);
$routes->get('billing/getPayments', 'Billing::getPayments', ['filter' => 'auth']); // Alternative format
$routes->get('billing/paymentReceipt/(:num)', 'Billing::paymentReceipt/$1', ['filter' => 'auth']);

// Reports Routes - Unified interface
$routes->group('reports', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Reports::index');
    $routes->get('financial', 'Reports::financial');
    $routes->get('revenue', 'Reports::revenue');
    $routes->get('income', 'Reports::income');
    $routes->get('expenses', 'Reports::expenses');
    $routes->get('profit-loss', 'Reports::profitLoss');
    $routes->get('outstanding-payments', 'Reports::outstandingPayments');
    $routes->get('patient-stats', 'Reports::patientStats');
    $routes->get('patient-visits', 'Reports::patientVisits');
    $routes->get('patient-history', 'Reports::patientHistory');
    $routes->get('appointment-stats', 'Reports::appointmentStats');
    $routes->get('doctor-schedule-utilization', 'Reports::doctorScheduleUtilization');
    $routes->get('laboratory-tests', 'Reports::laboratoryTests');
    $routes->get('test-results', 'Reports::testResults');
    $routes->get('prescriptions', 'Reports::prescriptions');
    $routes->get('medicine-inventory', 'Reports::medicineInventory');
    $routes->get('medicine-sales', 'Reports::medicineSales');
    $routes->get('admissions', 'Reports::admissions');
    $routes->get('discharges', 'Reports::discharges');
    $routes->get('doctor-performance', 'Reports::doctorPerformance');
    $routes->get('staff-activity', 'Reports::staffActivity');
    $routes->get('philhealth-claims', 'Reports::philhealthClaims');
    $routes->get('hmo-claims', 'Reports::hmoClaims');
    // Export routes
    $routes->get('export/pdf/(:segment)', 'Reports::exportPdf/$1');
    $routes->get('export/excel/(:segment)', 'Reports::exportExcel/$1');
    $routes->get('export/income-pdf', 'Reports::exportIncomePdf');
    $routes->get('export/expenses-pdf', 'Reports::exportExpensesPdf');
    $routes->get('export/expenses-excel', 'Reports::exportExpensesExcel');
});

// Laboratory Routes
$routes->get('laboratory/request', 'Laboratory::request', ['filter' => 'auth:labstaff,doctor,admin,nurse']);
$routes->post('laboratory/request/submit', 'Laboratory::submitRequest', ['filter' => 'auth:labstaff,admin']);
    // Laboratory: Test Results (Lab staff/admin/doctor/nurse can view)
    $routes->get('laboratory/testresult', 'Laboratory::testresult', ['filter' => 'auth:labstaff,doctor,admin,nurse']);
    $routes->get('laboratory/testresult/view/(:any)', 'Laboratory::viewTestResult/$1', ['filter' => 'auth:labstaff,doctor,admin,nurse']);
    $routes->match(['get', 'post'], 'laboratory/testresult/add/(:any)', 'Laboratory::addTestResult/$1', ['filter' => 'auth:labstaff,admin']);
    $routes->get('laboratory/testresult/download/(:any)', 'Laboratory::downloadResultFile/$1', ['filter' => 'auth:labstaff,doctor,admin,nurse']);
    $routes->get('laboratory/testresult/data', 'Laboratory::getTestResultsData');
    $routes->get('laboratory/patient/suggest', 'Laboratory::patientSuggest');
    $routes->get('laboratory/patient/lab-records', 'Laboratory::patientLabRecords');
    $routes->get('laboratory/patient/completed-lab-records', 'Laboratory::patientCompletedLabRecords');
     $routes->post('laboratory/testresult/add', 'Laboratory::addTestResult', ['filter' => 'auth:labstaff,admin']);

 // Doctor-facing lab result routes (read-only access under doctor features)
$routes->get('doctor/laboratory/request', 'Laboratory::request', ['filter' => 'auth:doctor,admin']);
$routes->get('doctor/laboratory/testresult', 'Laboratory::testresult', ['filter' => 'auth:doctor,admin']);
$routes->get('doctor/laboratory/testresult/view/(:any)', 'Laboratory::viewTestResult/$1', ['filter' => 'auth:doctor,admin']);

// Medicine Routes
$routes->get('/medicines', 'Medicine::index');
$routes->get('/medicines/stock-out', 'Medicine::stockOut');
$routes->get('/medicines/out-of-stock', 'Medicine::outOfStock');
$routes->post('/medicines/restock', 'Medicine::restock');
$routes->post('/medicines/store', 'Medicine::store');
$routes->get('/medicines/edit/(:segment)', 'Medicine::edit/$1');
$routes->post('/medicines/update/(:segment)', 'Medicine::update/$1');

// Unified Pharmacy Routes (for all roles)
$routes->group('pharmacy', ['namespace' => 'App\\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('prescription', 'Pharmacy::prescriptionDispensing');
    $routes->get('transactions', 'Pharmacy::transactions');
    $routes->get('transaction/(:any)', 'Pharmacy::viewTransaction/$1');
    $routes->get('medicine', 'Pharmacy::medicine');
});

// Pharmacy Routes under admin (backward compatibility)
$routes->group('admin/pharmacy', ['namespace' => 'App\\Controllers', 'filter' => 'auth:pharmacist,admin'], function($routes) {
    $routes->get('inventory', 'Pharmacy::inventory');
    $routes->get('prescription-dispensing', 'Pharmacy::prescriptionDispensing');
    $routes->get('transactions', 'Pharmacy::transactions');
    $routes->get('transaction/(:any)', 'Pharmacy::viewTransaction/$1');
    $routes->get('medicines', 'Pharmacy::medicines');
    $routes->get('inventory/medicine', 'Medicine::index');  // Access via /admin/pharmacy/inventory/medicine
    $routes->get('inventory/medicine/stock-out', 'Medicine::stockOut');  // Stock out via /admin/pharmacy/inventory/medicine/stock-out
});

// Pharmacy API Routes
$routes->group('api/pharmacy', ['namespace' => 'App\\Controllers'], function($routes) {
    // Patient and Medication lookup
    $routes->get('patients', 'Pharmacy::getPatients');
    $routes->get('medications', 'Pharmacy::getMedications');
    $routes->get('medication/(:num)', 'Pharmacy::getMedication/$1');
    
    // Admitted patients and prescriptions
    $routes->get('admitted-patients', 'Pharmacy::getAdmittedPatients');
    $routes->get('patient-prescriptions', 'Pharmacy::getPatientPrescriptions');
    
    // Transaction management
    $routes->post('transaction/create', 'Pharmacy::createTransaction');
    $routes->get('transactions', 'Pharmacy::getAllTransactions');
    $routes->get('transaction/(:num)', 'Pharmacy::getTransactionDetails/$1');
    
    // Statistics
    $routes->get('stats', 'Pharmacy::getStats');

    // Expiry endpoints
    $routes->get('medicines/expiring-soon', 'Pharmacy::getExpiringMedicines');
    $routes->get('medicines/expired', 'Pharmacy::getExpiredMedicines');
    
    // Stock management for cart
    $routes->post('stock/reserve', 'Pharmacy::reserveStock');
    $routes->post('stock/restore', 'Pharmacy::restoreStock');
});

// Unified Admissions Routes (for all roles)
$routes->group('admissions', ['namespace' => 'App\\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('create', 'Admissions::create');
    $routes->get('check', 'Admissions::checkAdmission');
    $routes->post('store', 'Admissions::store');
    $routes->post('(:num)/discharge', 'Admissions::discharge/$1');
});

// Unified Pharmacy Routes (for all roles)
$routes->group('pharmacy', ['namespace' => 'App\\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('prescription', 'Pharmacy::prescriptionDispensing');
    $routes->get('transactions', 'Pharmacy::transactions');
    $routes->get('transaction/(:any)', 'Pharmacy::viewTransaction/$1');
    $routes->get('medicine', 'Pharmacy::medicine');
});

// Unified Rooms Routes (for all roles)
$routes->group('rooms', ['namespace' => 'App\\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('general-inpatient', 'Rooms::generalInpatient');
    $routes->get('critical-care', 'Rooms::criticalCare');
    $routes->get('specialized', 'Rooms::specialized');
    $routes->get('pedia-ward', 'Rooms::pediaWard');
    $routes->get('male-ward', 'Rooms::maleWard');
    $routes->get('female-ward', 'Rooms::femaleWard');
    $routes->post('beds/update-status', 'Rooms::updateBedStatus');
});

// Unified Admissions Routes (for all roles)
$routes->group('admissions', ['namespace' => 'App\\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('create', 'Admissions::create');
    $routes->get('check', 'Admissions::checkAdmission');
    $routes->post('store', 'Admissions::store');
    $routes->post('(:num)/discharge', 'Admissions::discharge/$1');
});

// Unified Rooms Routes (for all roles)
$routes->group('rooms', ['namespace' => 'App\\Controllers', 'filter' => 'auth'], function($routes) {
    $routes->get('general-inpatient', 'Rooms::generalInpatient');
    $routes->get('critical-care', 'Rooms::criticalCare');
    $routes->get('specialized', 'Rooms::specialized');
    $routes->get('pedia-ward', 'Rooms::pediaWard');
    $routes->get('male-ward', 'Rooms::maleWard');
    $routes->get('female-ward', 'Rooms::femaleWard');
    $routes->post('beds/update-status', 'Rooms::updateBedStatus');
});

// Beds/Wards availability API for inpatient assignment
$routes->group('api/rooms', ['namespace' => 'App\\Controllers'], function($routes) {
    $routes->get('wards', 'Rooms::apiWards');
    $routes->get('rooms/(:segment)', 'Rooms::apiRooms/$1');
    $routes->get('beds/(:segment)/(:segment)', 'Rooms::apiBeds/$1/$2');
});

// Location API Routes (Provinces → Cities/Municipalities → Barangays)
$routes->group('api/locations', ['namespace' => 'App\\Controllers'], function($routes) {
    $routes->get('provinces', 'Api\\Locations::provinces');
    $routes->get('cities/(:segment)', 'Api\\Locations::cities/$1');
    $routes->get('barangays/(:segment)', 'Api\\Locations::barangays/$1');
});

$routes->get('admin/pharmacy/transaction/print/(:num)', 'Pharmacy::printTransaction/$1', ['filter' => 'auth:pharmacist,admin']);

// Administration Routes
    $routes->group('admin', ['namespace' => 'App\\Controllers', 'filter' => 'auth:admin'], function($routes) {
        // Unified dashboard is handled via Admin::index (already routed at /admin/dashboard)
        $routes->get('billing', 'Billing::index');
        $routes->get('billing/receipt/(:num)', 'Billing::receipt/$1');
        
        // Inventory routes
        $routes->get('inventory/medicine', 'Medicine::index');
        $routes->get('InventoryMan/PrescriptionDispencing', 'InventoryMan::PrescriptionDispencing');
        
        $routes->group('patients', function($routes) {
            $routes->get('', 'Patients::index');
            $routes->get('register', 'Patients::register');
            $routes->get('inpatient', 'Patients::inpatient');
            $routes->get('admission', 'Admissions::create');
            $routes->get('admission/check', 'Admissions::checkAdmission');
            $routes->post('admission/store', 'Admissions::store');
            $routes->post('admission/(:num)/discharge', 'Admissions::discharge/$1');
            $routes->post('register', 'Patients::processRegister');  
            $routes->get('view/(:num)', 'Patients::view/$1');
            $routes->get('edit/(:num)', 'Patients::edit/$1');
            $routes->post('update/(:num)', 'Patients::update/$1');
            $routes->get('delete/(:num)', 'Patients::delete/$1');
        });
        // Admin ward/room pages (unified controller)
        $routes->group('rooms', function($routes) {
            $routes->get('pedia-ward', 'Rooms::pediaWard');
            $routes->get('male-ward', 'Rooms::maleWard');
            $routes->get('female-ward', 'Rooms::femaleWard');
            $routes->get('general-inpatient', 'Rooms::generalInpatient');
            $routes->get('critical-care', 'Rooms::criticalCare');
            $routes->get('specialized', 'Rooms::specialized');
            $routes->post('beds/update-status', 'Rooms::updateBedStatus');
        });
    });

// Receptionist Routes (directly to views)
$routes->view('receptionist/dashboard', 'Roles/Reception/dashboard', ['filter' => 'auth:receptionist,admin']);

$routes->group('receptionist/appointments', ['namespace' => 'App\\Views', 'filter' => 'auth:receptionist,admin'], function($routes) {
    $routes->view('list', 'Roles/Reception/appointments/Appointmentlist');
    $routes->view('book', 'Roles/Reception/appointments/Bookappointment');
    $routes->view('staff-schedule', 'Roles/Reception/appointments/StaffSchedule');
});

$routes->group('receptionist/patients', ['namespace' => 'App\\Views', 'filter' => 'auth:receptionist,admin'], function($routes) {
    $routes->view('register', 'Roles/Reception/patients/register');
    $routes->view('inpatient', 'Roles/Reception/patients/Inpatient');
    $routes->view('view', 'Roles/Reception/patients/view');
});

// Receptionist ward/room pages (unified controller)
$routes->group('receptionist/rooms', ['filter' => 'auth:receptionist,admin'], function($routes) {
    $routes->get('general-inpatient', 'Rooms::generalInpatient');
    $routes->get('critical-care', 'Rooms::criticalCare');
    $routes->get('specialized', 'Rooms::specialized');
    $routes->get('pedia-ward', 'Rooms::pediaWard');
    $routes->get('male-ward', 'Rooms::maleWard');
    $routes->get('female-ward', 'Rooms::femaleWard');
});

// Doctor Routes
// Patient list (doctor read-only view) - now uses unified Patients controller
$routes->get('doctor/patients/view', 'Patients::index', ['filter' => 'auth:doctor,admin']);
// Doctor's own appointments
$routes->get('doctor/appointments', 'Appointment::index', ['filter' => 'auth:doctor']);
$routes->get('doctor/appointments/list', 'Appointment::index', ['filter' => 'auth:doctor']);
// Doctor prescription notes (EHR)
$routes->get('doctor/prescription', 'Doctor::prescription', ['filter' => 'auth:doctor,admin,nurse,receptionist']);
$routes->post('doctor/prescription/save', 'Doctor::savePrescription', ['filter' => 'auth:doctor,admin']);
// Medical records (admissions history)
$routes->get('doctor/medical-records', 'Doctor::medicalRecords', ['filter' => 'auth:doctor,admin,nurse,receptionist']);
// Vitals API for EHR modal (viewable by all clinical roles; writable by doctor/nurse/admin)
$routes->get('doctor/vitals', 'Doctor::vitals', ['filter' => 'auth:doctor,admin,nurse,receptionist']);
$routes->post('doctor/vitals/save', 'Doctor::saveVitals', ['filter' => 'auth:doctor,admin,nurse']);

// Nurse Routes (unified controllers)
$routes->group('nurse/appointments', ['filter' => 'auth:nurse,admin'], function($routes) {
    $routes->get('list', 'Appointment::index');
    $routes->view('staff-schedule', 'Roles/nurse/appointments/StaffSchedule');
});

$routes->group('nurse/patients', ['filter' => 'auth:nurse,admin'], function($routes) {
    $routes->get('view', 'Patients::index');
});

$routes->group('nurse/laboratory', ['filter' => 'auth:nurse,admin'], function($routes) {
    $routes->get('request', 'Laboratory::request');
    $routes->get('testresult', 'Laboratory::testresult');
});

// Pharmacy Routes
$routes->group('pharmacist', ['filter' => 'auth:pharmacist,admin'], function($routes) {
    $routes->get('inventory', 'Pharmacy::medicine');
    $routes->get('transactions', 'Pharmacy::transactions');
});
