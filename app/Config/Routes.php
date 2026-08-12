<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//dashboard page
$routes->get('/', 'Dashboard::index');
$routes->get('dashboard', 'Dashboard::index');

//users page
$routes->get('users', 'Users::index');
$routes->get('users/data', 'Users::data');
$routes->get('users/show/(:num)', 'Users::show/$1');
$routes->post('users/store', 'Users::store');
$routes->post('users/update', 'Users::update');
$routes->post('users/resetPassword', 'Users::resetPassword');
$routes->post('users/toggleStatus', 'Users::toggleStatus');
$routes->post('users/delete', 'Users::delete');

//school
$routes->get('schools','Schools::index');
$routes->get('schools/data','Schools::data');
$routes->get('schools/city','Schools::city');
$routes->get('schools/district','Schools::district');
$routes->get('schools/detail/(:num)','Schools::detail/$1');
$routes->post('schools/store','Schools::store');
$routes->post('schools/update/(:num)','Schools::update/$1');
$routes->post('schools/delete/(:num)','Schools::delete/$1');

//visitasi
$routes->get('visits','Visits::index');
$routes->get('visits/data','Visits::data');
$routes->get('visits/schools','Visits::schools');
$routes->get('visits/officers','Visits::officers');
$routes->post('visits/create','Visits::create');
$routes->post('visits/delete/(:num)','Visits::delete/$1');
$routes->get('visits/form/(:num)','Visits::form/$1');
$routes->get('visits/instruments/(:num)','Visits::instrumentData/$1');
$routes->post('visits/save-answers/(:num)','Visits::saveAnswers/$1');
$routes->post('visits/start/(:num)','Visits::start/$1');
$routes->post('visits/complete/(:num)','Visits::complete/$1');

//instruments
$routes->get('instruments','Instruments::index');
$routes->get('instruments/data','Instruments::data');
$routes->get('instruments/sections','Instruments::sections');
$routes->get('instruments/detail/(:num)','Instruments::detail/$1');
$routes->post('instruments/store','Instruments::store');
$routes->post('instruments/update/(:num)','Instruments::update/$1');
$routes->post('instruments/delete/(:num)','Instruments::delete/$1');
$routes->post('instruments/section-store','Instruments::sectionStore');
$routes->post('instruments/section-update/(:num)','Instruments::sectionUpdate/$1');
$routes->post('instruments/section-delete/(:num)','Instruments::sectionDelete/$1');

// Reports / Rekap Monev
$routes->get('reports', 'ReportController::index');
$routes->post('reports/data', 'ReportController::getData');
$routes->post('reports/update-followup-status', 'ReportController::updateFollowupStatus');
$routes->post('reports/export-excel', 'ReportController::exportExcel');
$routes->post('reports/export-pdf', 'ReportController::exportPdf');
