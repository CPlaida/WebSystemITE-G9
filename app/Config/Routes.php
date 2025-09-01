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

// Dashboard Routes - No auth filter on the main dashboard
$routes->get('dashboard', 'Dashboard::index');

// Admin Dashboard
$routes->group('admin', ['filter' => 'auth:hospital administrator'], function($routes) {
    $routes->get('dashboard', 'Admin::index');
});

// Doctor Dashboard
$routes->group('doctor', ['filter' => 'auth:doctor'], function($routes) {
    $routes->get('dashboard', 'Doctor::index');
});

// Nurse Dashboard
$routes->group('nurse', ['filter' => 'auth:nurse'], function($routes) {
    $routes->get('dashboard', 'Nurse::index');
    $routes->get('reports', 'Nurse::reports');
});

// Accountant Dashboard - Multiple URL patterns for compatibility
$routes->group('accounting', ['filter' => 'auth:accountant'], function($routes) {
    $routes->get('dashboard', 'Accountant::index');
    $routes->get('', 'Accountant::index');
});

// Alias for accountant
$routes->group('accountant', ['filter' => 'auth:accountant'], function($routes) {
    $routes->get('dashboard', 'Accountant::index');
    $routes->get('', 'Accountant::index');
});

// Reception Dashboard
$routes->group('reception', ['filter' => 'auth:receptionist'], function($routes) {
    $routes->get('dashboard', 'Reception::index');
});

// IT Staff Dashboard
$routes->group('itstaff', ['filter' => 'auth:it staff'], function($routes) {
    $routes->get('dashboard', 'Itstaff::index');
});

// Laboratory Dashboard
$routes->group('laboratory', ['filter' => 'auth:laboratory staff'], function($routes) {
    $routes->get('dashboard', 'Laboratory::index');
});

// Pharmacy Dashboard
$routes->group('pharmacy', ['filter' => 'auth:pharmacist'], function($routes) {
    $routes->get('dashboard', 'Pharmacy::index');
});

// Logout Route
$routes->get('auth/logout', 'Auth::logout');

// Patient Routes 
$routes->get('patients/view', 'Patients::view');
$routes->get('patients/register', 'Patients::register');

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
$routes->get('laboratory/request', 'Laboratory::index');

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
