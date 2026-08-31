<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ==========================================
// 1. RUTE PUBLIK
// ==========================================
$routes->get('login', 'AuthController::index');   // Tampilkan View HTML
$routes->post('login', 'AuthController::login');   // Proses AJAX Login
$routes->get('logout', 'AuthController::logout');
$routes->get('regions/data', 'Regions::data');
$routes->get('regions/get-list', 'Regions::getList');
$routes->get('/', function () {
    return view('portal');
});
// ==========================================
// 2. RUTE TERPROTEKSI (Wajib Login)
// ==========================================
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // --------------------------------------
    // A. BISA DIAKSES ADMIN & PETUGAS
    // --------------------------------------
    $routes->group('', ['filter' => 'role:admin,petugas'], function ($routes) {
        
        // Dashboard
        $routes->get('dashboard', 'Dashboard::index');

        // Visitasi / Monitoring
        $routes->get('visits', 'Visits::index');
        $routes->get('visits/data', 'Visits::data');
        $routes->get('visits/schools', 'Visits::schools');
        $routes->get('visits/officers', 'Visits::officers');
        $routes->post('visits/create', 'Visits::create');
        $routes->post('visits/delete/(:num)', 'Visits::delete/$1');
        $routes->get('visits/form/(:num)', 'Visits::form/$1');
        $routes->get('visits/instruments/(:num)', 'Visits::instrumentData/$1');
        $routes->post('visits/save-answers/(:num)', 'Visits::saveAnswers/$1');
        $routes->post('visits/start/(:num)', 'Visits::start/$1');
        $routes->post('visits/complete/(:num)', 'Visits::complete/$1');
        $routes->get('visits/edit/(:num)', 'Visits::edit/$1');
        $routes->post('visits/update', 'Visits::update');

        // Reports / Rekap Monev
        $routes->get('reports','Reports::index');
        $routes->get('reports/regions','Reports::regions');
        $routes->get('reports/data','Reports::data');
        $routes->get('reports/export-excel','Reports::exportExcel');
        $routes->get('reports/pdf/(:num)','Reports::pdf/$1');
        $routes->get('reports/exportAllPdf', 'Reports::exportAllPdf');
    });

    // --------------------------------------
    // B. KHUSUS ADMIN SAJA (Master Data)
    // --------------------------------------
    $routes->group('', ['filter' => 'role:admin'], function ($routes) {

        // Kelola Users
        $routes->get('users', 'Users::index');
        $routes->get('users/data', 'Users::data');
        $routes->get('users/show/(:num)', 'Users::show/$1');
        $routes->post('users/store', 'Users::store');
        $routes->post('users/update', 'Users::update');
        $routes->post('users/resetPassword', 'Users::resetPassword');
        $routes->post('users/toggleStatus', 'Users::toggleStatus');
        $routes->post('users/delete', 'Users::delete');

        // Kelola Schools
        $routes->get('schools', 'Schools::index');
        $routes->get('schools/data', 'Schools::data');
        $routes->get('schools/city', 'Schools::city');
        $routes->get('schools/district', 'Schools::district');
        $routes->get('schools/detail/(:num)', 'Schools::detail/$1');
        $routes->post('schools/store', 'Schools::store');
        $routes->post('schools/update/(:num)', 'Schools::update/$1');
        $routes->post('schools/delete/(:num)', 'Schools::delete/$1');

        // Kelola Instruments
        $routes->get('instruments', 'Instruments::index');
        $routes->get('instruments/data', 'Instruments::data');
        $routes->get('instruments/sections', 'Instruments::sections');
        $routes->get('instruments/detail/(:num)', 'Instruments::detail/$1');
        $routes->post('instruments/store', 'Instruments::store');
        $routes->post('instruments/update/(:num)', 'Instruments::update/$1');
        $routes->post('instruments/delete/(:num)', 'Instruments::delete/$1');
        $routes->post('instruments/section-store', 'Instruments::sectionStore');
        $routes->post('instruments/section-update/(:num)', 'Instruments::sectionUpdate/$1');
        $routes->post('instruments/section-delete/(:num)', 'Instruments::sectionDelete/$1');
    });

});