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

// Only doctors can see doctor dashboard
$routes->get('/doctor/dashboard', 'Doctor::index', ['filter' => 'auth:doctor']);

// Admin OR Nurse allowed
$routes->get('/nurse/reports', 'Nurse::reports', ['filter' => 'auth:admin,nurse']);
