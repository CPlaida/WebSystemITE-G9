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
$routes->get('/admin/Administration/ManageUser', 'Admin::manageUsers', ['filter' => 'auth:admin']);
$routes->get('/admin/Administration/RoleManagement', 'Admin::roleManagement', ['filter' => 'auth:admin']);

// Only doctors can see doctor dashboard
$routes->get('/doctor/dashboard', 'Doctor::index', ['filter' => 'auth:doctor']);

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

// Billing Management
$routes->get('billing', 'Billing::index');
$routes->get('billing/receipt/(:any)', 'Billing::receipt/$1');
$routes->post('billing/save', 'Billing::save');
$routes->get('billing/get/(:num)', 'Billing::get/$1');
$routes->post('billing/update/(:num)', 'Billing::update/$1');
$routes->post('billing/delete/(:num)', 'Billing::delete/$1');

// Laboratory Routes
$routes->get('laboratory/request', 'Laboratory::request', ['filter' => 'auth:labstaff,admin']);
// Laboratory: Test Results
$routes->get('laboratory/testresult', 'Laboratory::testresult', ['filter' => 'auth:labstaff,admin']);
$routes->get('laboratory/testresult/view/(:num)', 'Laboratory::viewTestResult/$1', ['filter' => 'auth:labstaff,admin']);
$routes->match(['get', 'post'], 'laboratory/testresult/add/(:num)', 'Laboratory::addTestResult/$1', ['filter' => 'auth:labstaff,admin']);
$routes->get('laboratory/results', 'Laboratory::results');

// Pharmacy Routes
$routes->group('pharmacy', ['namespace' => 'App\\Controllers'], function($routes) {
    $routes->get('inventory', 'Pharmacy::inventory');
    $routes->get('prescriptions/new', 'Pharmacy::new');
    $routes->get('medicines', 'Pharmacy::medicines');
    $routes->get('inventory/medicine', 'Pharmacy::medicine', ['filter' => 'auth:pharmacist,admin']);
});

// Administration Routes
$routes->group('admin', ['namespace' => 'App\\Controllers'], function($routes) {
    $routes->get('users', 'Admin::users');
    $routes->get('doctors', 'Admin::doctors');
    $routes->get('settings', 'Admin::settings');
    $routes->get('InventoryMan/PrescriptionDispencing', 'InventoryMan::PrescriptionDispencing', ['filter' => 'auth:admin']);
});
