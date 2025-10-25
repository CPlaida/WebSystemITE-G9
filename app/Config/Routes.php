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
$routes->get('doctor/dashboard', 'Dashboard::index', ['filter' => 'auth:doctor,admin']);
$routes->get('nurse/dashboard', 'Dashboard::index', ['filter' => 'auth:nurse,admin']);
$routes->get('receptionist/dashboard', 'Dashboard::index', ['filter' => 'auth:receptionist,admin']);

// Backward-compatibility: legacy admin dashboard URL(admin dashboard page diffrent sa unified okii)
$routes->get('/admin/dashboard', 'Admin::index', ['filter' => 'auth:admin']);

// Admin management pages (not dashboards)
$routes->get('admin/Administration/ManageUser', 'Admin::manageUsers', ['filter' => 'auth:admin']);

// Doctor scheduling routes
$routes->get('/doctor/schedule', 'Doctor\Doctor::schedule', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/addSchedule', 'Doctor\Doctor::addSchedule', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/updateSchedule/(:num)', 'Doctor\Doctor::updateSchedule/$1', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/deleteSchedule/(:num)', 'Doctor\Doctor::deleteSchedule/$1', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/getConflicts', 'Doctor\Doctor::getConflicts', ['filter' => 'auth:admin,doctor']);
$routes->get('/doctor/getScheduleData', 'Doctor\Doctor::getScheduleData', ['filter' => 'auth:admin,doctor']);
$routes->get('/doctor/getDoctors', 'Doctor\Doctor::getDoctors', ['filter' => 'auth:admin,doctor']);
// Doctor app shortcuts
$routes->get('doctor/appointments', 'Appointment::index', ['filter' => 'auth:doctor,admin']);

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
$routes->get('laboratory/request', 'Laboratory::request', ['filter' => 'auth:labstaff,admin']);
$routes->post('laboratory/request/submit', 'Laboratory::submitRequest', ['filter' => 'auth:labstaff,admin']);
// Laboratory: Test Results
$routes->get('laboratory/testresult', 'Laboratory::testresult', ['filter' => 'auth:labstaff,admin']);
$routes->get('laboratory/testresult/view/(:any)', 'Laboratory::viewTestResult/$1', ['filter' => 'auth:labstaff,admin']);
$routes->match(['get', 'post'], 'laboratory/testresult/add/(:any)', 'Laboratory::addTestResult/$1', ['filter' => 'auth:labstaff,admin']);
$routes->get('laboratory/testresult/data', 'Laboratory::getTestResultsData');

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
    $routes->get('transactions', 'Pharmacy::transactions');
    $routes->get('transaction/(:any)', 'Pharmacy::viewTransaction/$1');
    $routes->get('medicines', 'Pharmacy::medicines');
    $routes->get('inventory/medicine', 'Medicine::index');  // Access via /admin/pharmacy/inventory/medicine
});

// Route for admin inventory medicine
$routes->get('admin/inventory/medicine', 'Medicine::index', ['filter' => 'auth:pharmacist,admin']);

// Administration Routes
    $routes->group('admin', ['namespace' => 'App\\Controllers', 'filter' => 'auth:admin'], function($routes) {
        // Unified dashboard is handled via Admin::index (already routed at /admin/dashboard)
        $routes->get('Administration/RoleManagement', 'Admin::roleManagement');
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

// Doctor Routes (directly to views)
$routes->group('doctor/patients', ['namespace' => 'App\\Views', 'filter' => 'auth:doctor,admin'], function($routes) {
    $routes->view('view', 'Roles/doctor/patients/view');
});
