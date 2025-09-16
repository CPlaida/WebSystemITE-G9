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

// Dashboard Route
$routes->get('dashboard', 'Dashboard::index');

// Logout Route
$routes->get('auth/logout', 'Auth::logout');

// Remove index.php from URL
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

// Role Routes
// Only logged-in users can see this
$routes->get('/dashboard', 'Dashboard::index', ['filter' => 'auth']);

// Only admin can see admin dashboard
$routes->get('/admin/dashboard', 'Admin::index', ['filter' => 'auth:admin']);
$routes->get('admin/Administration/ManageUser', 'Admin::manageUsers', ['filter' => 'auth:admin']);

// Only doctors can see doctor dashboard
$routes->get('/doctor/dashboard', 'Doctor\Doctor::index', ['filter' => 'auth:doctor']);

// Doctor scheduling routes
$routes->get('/doctor/schedule', 'Doctor\Doctor::schedule', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/addSchedule', 'Doctor\Doctor::addSchedule', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/updateSchedule/(:num)', 'Doctor\Doctor::updateSchedule/$1', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/deleteSchedule/(:num)', 'Doctor\Doctor::deleteSchedule/$1', ['filter' => 'auth:admin,doctor']);
$routes->post('/doctor/getConflicts', 'Doctor\Doctor::getConflicts', ['filter' => 'auth:admin,doctor']);
$routes->get('/doctor/getScheduleData', 'Doctor\Doctor::getScheduleData', ['filter' => 'auth:admin,doctor']);
$routes->get('/doctor/getDoctors', 'Doctor\Doctor::getDoctors', ['filter' => 'auth:admin,doctor']);

// Only nurses can see nurse dashboard
$routes->get('/nurse/dashboard', 'Nurse::index', ['filter' => 'auth:nurse']);

// Admin OR Nurse allowed
$routes->get('/nurse/reports', 'Nurse::reports', ['filter' => 'auth:admin,nurse']);

// Only accountants can see accountant dashboard
$routes->get('/accounting/dashboard', 'Accountant::index', ['filter' => 'auth:accounting']);

$routes->get('/accountant/dashboard', 'Accountant::index', ['filter' => 'auth:accounting']);

$routes->get('/accountant', 'Accountant::index', ['filter' => 'auth:accounting']);

// Only reception can see reception dashboard
$routes->get('/reception/dashboard', 'Reception::index', ['filter' => 'auth:receptionist']);

// Only IT staff can see IT dashboard
$routes->get('/itstaff/dashboard', 'Itstaff::index', ['filter' => 'auth:itstaff']);

// Only laboratory staff can see laboratory dashboard
$routes->get('/laboratory/dashboard', 'Laboratory::index', ['filter' => 'auth:labstaff']);

// Only pharmacists can see pharmacy dashboard
$routes->get('/pharmacy/dashboard', 'Pharmacy::index', ['filter' => 'auth:pharmacist']);

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

// Appointment API Routes
$routes->post('appointments/create', 'Appointment::create');
$routes->get('appointments/show/(:num)', 'Appointment::show/$1');
$routes->post('appointments/update/(:num)', 'Appointment::update/$1');
$routes->post('appointments/cancel/(:num)', 'Appointment::cancel/$1');
$routes->post('appointments/delete/(:num)', 'Appointment::delete/$1');
$routes->post('appointments/complete/(:num)', 'Appointment::complete/$1');
$routes->post('appointments/no-show/(:num)', 'Appointment::noShow/$1');

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
$routes->get('billing/receipt/(:num)', 'Billing::receipt/$1', ['filter' => 'auth']);
$routes->post('billing/delete/(:num)', 'Billing::delete/$1');

// Laboratory Routes
$routes->get('laboratory/request', 'Laboratory::request', ['filter' => 'auth:labstaff,admin']);
$routes->post('laboratory/request/submit', 'Laboratory::submitRequest', ['filter' => 'auth:labstaff,admin']);
// Laboratory: Test Results
$routes->get('laboratory/testresult', 'Laboratory::testresult', ['filter' => 'auth:labstaff,admin']);
$routes->get('laboratory/testresult/view/(:any)', 'Laboratory::viewTestResult/$1', ['filter' => 'auth:labstaff,admin']);
$routes->match(['get', 'post'], 'laboratory/testresult/add/(:any)', 'Laboratory::addTestResult/$1', ['filter' => 'auth:labstaff,admin']);
$routes->get('laboratory/results', 'Laboratory::results');
$routes->get('laboratory/testresult/data', 'Laboratory::getTestResultsData');

// Laboratory API Routes
$routes->group('api/laboratory', ['namespace' => 'App\\Controllers', 'filter' => 'auth:labstaff,admin'], function($routes) {
    // Lab Request API endpoints
    $routes->post('request/submit', 'Laboratory::submitRequest');
    $routes->get('requests', 'Laboratory::getRequests');
    $routes->get('requests/pending', 'Laboratory::getRequests');
    $routes->get('requests/urgent', 'Laboratory::getRequests');
    $routes->get('requests/today', 'Laboratory::getRequests');
    
    // Test Result API endpoints
    $routes->get('results', 'Laboratory::getTestResults');
    $routes->post('results/save', 'Laboratory::saveTestResult');
    $routes->get('results/pending', 'Laboratory::getTestResults');
    $routes->get('results/completed', 'Laboratory::getTestResults');
    $routes->get('results/critical', 'Laboratory::getTestResults');
    
    // Search and Statistics
    $routes->get('search', 'Laboratory::search');
    $routes->get('stats', 'Laboratory::getStats');
});

// Pharmacy Routes
$routes->group('pharmacy', ['namespace' => 'App\\Controllers'], function($routes) {
    $routes->get('inventory', 'Pharmacy::inventory', ['filter' => 'auth:pharmacist,admin']);
    $routes->get('prescriptions/new', 'Pharmacy::new');
    $routes->get('medicines', 'Pharmacy::medicines');
    $routes->get('inventory/medicine', 'Pharmacy::medicine', ['filter' => 'auth:pharmacist,admin']);
});

// Administration Routes
$routes->group('admin', ['namespace' => 'App\\Controllers', 'filter' => 'auth:admin'], function($routes) {
    $routes->get('users', 'Admin::users');
    $routes->get('doctors', 'Admin::doctors');
    $routes->get('settings', 'Admin::settings');
    $routes->get('Administration/RoleManagement', 'Admin::roleManagement');
    $routes->get('billing', 'Billing::index');
    $routes->get('billing/receipt/(:num)', 'Billing::receipt/$1');
    $routes->get('InventoryMan/PrescriptionDispencing', 'InventoryMan::PrescriptionDispencing');
    $routes->get('InventoryMan/medicine', 'Pharmacy::medicine');
    
    $routes->group('patients', function($routes) {
        $routes->get('', 'Admin\Patients::index');
        $routes->get('register', 'Admin\Patients::register');
        $routes->post('register', 'Admin\Patients::processRegister');  
        $routes->get('view/(:num)', 'Admin\Patients::view/$1');
        $routes->get('edit/(:num)', 'Admin\Patients::edit/$1');
        $routes->post('update/(:num)', 'Admin\Patients::update/$1');
        $routes->get('delete/(:num)', 'Admin\Patients::delete/$1');
    });
});
