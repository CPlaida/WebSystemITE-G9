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
$routes->get('auth/logout', 'Auth::logout');

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
$routes->get('doctor/dashboard', 'Doctor\Doctor::index', ['filter' => 'auth:doctor,admin']);
$routes->get('nurse/dashboard', 'Dashboard::index', ['filter' => 'auth:nurse,admin']);
$routes->get('receptionist/dashboard', 'Dashboard::index', ['filter' => 'auth:receptionist,admin']);

// Backward-compatibility: legacy admin dashboard URL(admin dashboard page diffrent sa unified okii)
$routes->get('/admin/dashboard', 'Admin::index', ['filter' => 'auth:admin']);

// Admin management pages (not dashboards)
$routes->get('admin/Administration/ManageUser', 'Admin::manageUsers', ['filter' => 'auth:admin']);
// Admin user CRUD endpoints
$routes->post('admin/users/create', 'Admin::createUser', ['filter' => 'auth:admin']);
$routes->post('admin/users/update/(:num)', 'Admin::updateUser/$1', ['filter' => 'auth:admin']);
$routes->post('admin/users/delete/(:num)', 'Admin::deleteUser/$1', ['filter' => 'auth:admin']);
$routes->post('admin/users/reset-password/(:num)', 'Admin::resetPassword/$1', ['filter' => 'auth:admin']);

// Doctor scheduling routes
$routes->get('/doctor/schedule', 'Doctor\Doctor::schedule', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/addSchedule', 'Doctor\Doctor::addSchedule', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/updateSchedule/(:num)', 'Doctor\Doctor::updateSchedule/$1', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/deleteSchedule/(:num)', 'Doctor\Doctor::deleteSchedule/$1', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/getConflicts', 'Doctor\Doctor::getConflicts', ['filter' => 'auth:admin,doctor']);
$routes->get('/doctor/getScheduleData', 'Doctor\Doctor::getScheduleData', ['filter' => 'auth:admin,doctor']);
$routes->get('/doctor/getDoctors', 'Doctor\Doctor::getDoctors', ['filter' => 'auth:admin,doctor']);
// Doctor app shortcuts
$routes->get('doctor/appointments', 'Doctor\Doctor::appointments', ['filter' => 'auth:doctor,admin']);

// Admin OR Nurse allowed
$routes->get('/nurse/reports', 'Nurse::reports', ['filter' => 'auth:admin,nurse']);

// Frontend Patient Routes
$routes->group('patients', ['namespace' => 'App\\Controllers'], function($routes) {
    $routes->get('register', 'Patients::register');
    $routes->post('register', 'Patients::processRegister');
    $routes->get('view', 'Patients::view');
    $routes->get('search', 'Patients::search');
    $routes->get('get/(:num)', 'Patients::getPatient/$1');
});

// Appointment Routes
$routes->get('appointments/book', 'Appointment::book');
$routes->get('appointments/list', 'Appointment::index');
$routes->get('appointments/schedule', 'Appointment::schedule');
$routes->post('appointments/create', 'Appointment::create');
$routes->get('appointments/show/(:num)', 'Appointment::show/$1');
$routes->post('appointments/update/(:num)', 'Appointment::update/$1');
$routes->post('appointments/cancel/(:num)', 'Appointment::cancel/$1');
$routes->post('appointments/complete/(:num)', 'Appointment::complete/$1');
$routes->post('appointments/no-show/(:num)', 'Appointment::noShow/$1');
$routes->post('appointments/delete/(:num)', 'Appointment::delete/$1');

// Appointment Query Routes
$routes->get('appointments/doctor/(:num)', 'Appointment::getByDoctor/$1');
$routes->get('appointments/patient/(:num)', 'Appointment::getByPatient/$1');
$routes->get('appointments/today', 'Appointment::getTodays');
$routes->get('appointments/upcoming', 'Appointment::getUpcoming');
$routes->get('appointments/search', 'Appointment::search');
$routes->get('appointments/stats', 'Appointment::getStats');


// Billing Routes
$routes->get('billing', 'Billing::index', ['filter' => 'auth']);
$routes->get('billing/process', 'Billing::process', ['filter' => 'auth']);
$routes->post('billing/save', 'Billing::save', ['filter' => 'auth']);
// New normalized Billing routes
$routes->post('billing/store', 'Billing::store', ['filter' => 'auth']);
// New: store header + items in a single transaction
$routes->post('billing/store-with-items', 'Billing::storeWithItems', ['filter' => 'auth']);
$routes->get('billing/edit/(:num)', 'Billing::edit/$1', ['filter' => 'auth']);
$routes->post('billing/update/(:num)', 'Billing::update/$1', ['filter' => 'auth']);
$routes->get('billing/delete/(:num)', 'Billing::delete/$1', ['filter' => 'auth']);
$routes->post('billing/delete/(:num)', 'Billing::delete/$1', ['filter' => 'auth']);
$routes->get('billing/show/(:num)', 'Billing::show/$1', ['filter' => 'auth']);

// Laboratory Routes
$routes->get('laboratory/request', 'Laboratory::request', ['filter' => 'auth:labstaff,doctor,admin']);
$routes->post('laboratory/request/submit', 'Laboratory::submitRequest', ['filter' => 'auth:labstaff,admin']);
    // Laboratory: Test Results (Lab staff/admin views)
    $routes->get('laboratory/testresult', 'Laboratory::testresult', ['filter' => 'auth:labstaff,doctor,admin']);
    $routes->get('laboratory/testresult/view/(:any)', 'Laboratory::viewTestResult/$1', ['filter' => 'auth:labstaff,doctor,admin']);
    $routes->match(['get', 'post'], 'laboratory/testresult/add/(:any)', 'Laboratory::addTestResult/$1', ['filter' => 'auth:labstaff,doctor,admin']);
    // Alternate POST endpoint accepting test_id from body to avoid path-based redirects
    $routes->post('laboratory/testresult/add', 'Laboratory::addTestResult', ['filter' => 'auth:labstaff,doctor,admin']);
    $routes->get('laboratory/testresult/data', 'Laboratory::getTestResultsData');

// Doctor-facing lab result routes (read-only access under doctor features)
$routes->get('doctor/laboratory/request', 'Laboratory::request', ['filter' => 'auth:doctor,admin']);
$routes->get('doctor/laboratory/testresult', 'Laboratory::testresult', ['filter' => 'auth:doctor,admin']);
$routes->get('doctor/laboratory/testresult/view/(:any)', 'Laboratory::viewTestResult/$1', ['filter' => 'auth:doctor,admin']);

 //Medicine Routes
$routes->get('/medicines', 'Medicine::index');
$routes->post('/medicines/store', 'Medicine::store');
$routes->get('/medicines/edit/(:num)', 'Medicine::edit/$1');
$routes->post('/medicines/update/(:num)', 'Medicine::update/$1');
$routes->get('/medicines/delete/(:num)', 'Medicine::delete/$1');
 // Sidebar alias
$routes->get('admin/inventory/medicine', 'Medicine::index');

// Pharmacy Routes under admin
$routes->group('admin/pharmacy', ['namespace' => 'App\\Controllers', 'filter' => 'auth:pharmacist,admin'], function($routes) {
    $routes->get('inventory', 'Pharmacy::inventory');
    $routes->get('prescription-dispensing', 'Pharmacy::prescriptionDispensing');
    $routes->get('transactions', 'Pharmacy::transactions');
    $routes->get('transaction/(:any)', 'Pharmacy::viewTransaction/$1');
    $routes->get('medicines', 'Pharmacy::medicines');
    $routes->get('inventory/medicine', 'Medicine::index');  // Access via /admin/pharmacy/inventory/medicine
});

// Pharmacy API Routes
$routes->group('api/pharmacy', ['namespace' => 'App\\Controllers'], function($routes) {
    // Patient and Medication lookup
    $routes->get('patients', 'Pharmacy::getPatients');
    $routes->get('medications', 'Pharmacy::getMedications');
    $routes->get('medication/(:num)', 'Pharmacy::getMedication/$1');
    
    // Transaction management
    $routes->post('transaction/create', 'Pharmacy::createTransaction');
    $routes->get('transactions', 'Pharmacy::getAllTransactions');
    $routes->get('transaction/(:num)', 'Pharmacy::getTransactionDetails/$1');
    
    // Statistics
    $routes->get('stats', 'Pharmacy::getStats');

    // Expiry endpoints
    $routes->get('medicines/expiring-soon', 'Pharmacy::getExpiringMedicines');
    $routes->get('medicines/expired', 'Pharmacy::getExpiredMedicines');
});

$routes->get('admin/pharmacy/transaction/print/(:num)', 'Pharmacy::printTransaction/$1', ['filter' => 'auth:pharmacist,admin']);

    // View routes
    $routes->get('admin/InventoryMan/PrescriptionDispensing', 'PrescriptionController::index', ['filter' => 'auth:pharmacist,admin']);

    // Transaction pages (normalized to Pharmacy controller)
    // Duplicate routes removed to avoid conflicts and 404s from non-existent TransactionController
    // Use existing routes:
    // - /admin/pharmacy/transactions -> Pharmacy::transactions (defined above in admin/pharmacy group)
    // - /admin/pharmacy/transaction/(:any) -> Pharmacy::viewTransaction/$1 (defined above)
    // - /admin/pharmacy/transaction/print/(:num) -> Pharmacy::printTransaction/$1 (defined above)

    // Route for admin inventory medicine
    $routes->get('admin/inventory/medicine', 'Medicine::index', ['filter' => 'auth:pharmacist,admin']);

// Administration Routes
    $routes->group('admin', ['namespace' => 'App\\Controllers', 'filter' => 'auth:admin'], function($routes) {
        // Unified dashboard is handled via Admin::index (already routed at /admin/dashboard)
        $routes->get('billing', 'Billing::index');
        $routes->get('billing/receipt/(:num)', 'Billing::receipt/$1');
        
        // Inventory Management Routes
        $routes->get('InventoryMan/PrescriptionDispencing', 'InventoryMan::PrescriptionDispencing');
        
        $routes->group('patients', function($routes) {
            $routes->get('', 'Admin\Patients::index');
            $routes->get('register', 'Admin\Patients::register');
            $routes->get('inpatient', 'Admin\Patients::inpatient');
            $routes->post('register', 'Admin\Patients::processRegister');  
            $routes->get('view/(:num)', 'Admin\Patients::view/$1');
            $routes->get('edit/(:num)', 'Admin\Patients::edit/$1');
            $routes->post('update/(:num)', 'Admin\Patients::update/$1');
            $routes->get('delete/(:num)', 'Admin\Patients::delete/$1');
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

// Nurse Routes (directly to views)
$routes->group('nurse/appointments', ['namespace' => 'App\\Views', 'filter' => 'auth:nurse,admin'], function($routes) {
    $routes->view('list', 'Roles/nurse/appointments/Appointmentlist');
    $routes->view('staff-schedule', 'Roles/nurse/appointments/StaffSchedule');
});

$routes->group('nurse/patients', ['namespace' => 'App\\Views', 'filter' => 'auth:nurse,admin'], function($routes) {
    $routes->view('view', 'Roles/nurse/patients/view');
});

$routes->group('nurse/laboratory', ['namespace' => 'App\\Views', 'filter' => 'auth:nurse,admin'], function($routes) {
    $routes->view('request', 'Roles/nurse/laboratory/LaboratoryReq');
    $routes->view('testresult', 'Roles/nurse/laboratory/TestResult');
});

// Doctor Patients route (controller-powered to load data)
$routes->get('doctor/patients/view', 'Doctor\Doctor::patientsView', ['filter' => 'auth:doctor,admin']);
// Doctor EHR endpoints
$routes->get('doctor/prescription', 'Doctor\Doctor::getPrescription', ['filter' => 'auth:doctor,admin']);
$routes->post('doctor/prescription/save', 'Doctor\Doctor::savePrescription', ['filter' => 'auth:doctor,admin']);
$routes->get('doctor/lab-results', 'Doctor\Doctor::labResults', ['filter' => 'auth:doctor,admin']);
