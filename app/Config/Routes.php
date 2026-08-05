<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', fn() => redirect()->to('/login'));

// ============================================================
// Auth
// ============================================================
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::prosesLogin');
$routes->get('logout', 'Auth::logout');


// ============================================================
// Dashboard — Content Plan
// Sesuai spesifikasi endpoint §6
// ============================================================
$routes->group('dashboard', function (RouteCollection $routes) {

    // ----------------------------------------------------------
    // Content Plan
    // ----------------------------------------------------------
    $routes->get('content-plan', 'ContentPlan::index');
    $routes->post('content-plan/store', 'ContentPlan::store');
    $routes->post('content-plan/update/(:num)', 'ContentPlan::update/$1');
    $routes->post('content-plan/delete/(:num)', 'ContentPlan::delete/$1');

    // Endpoint kritis: perubahan status HANYA lewat sini (§4, §6)
    $routes->post('content-plan/transition/(:num)', 'ContentPlan::transition/$1');

    // Endpoint AI (Tahap 9)
    $routes->post('content-plan/ai-caption/(:num)', 'ContentPlan::generateCaption/$1');

    // Riwayat status & catatan (untuk timeline modal — Tahap 3)
    $routes->get('content-plan/(:num)/log', 'ContentPlan::log/$1');

    // ----------------------------------------------------------
    // Master Data — User Role & Master (Tahap 10)
    // ----------------------------------------------------------
    $routes->group('master', function (RouteCollection $routes) {
        // User Management
        $routes->get('user', 'UserManagement::index');
        $routes->post('user/store', 'UserManagement::store');
        $routes->post('user/update/(:num)', 'UserManagement::update/$1');
        $routes->post('user/delete/(:num)', 'UserManagement::delete/$1');

        // Master Data
        $routes->get('data', 'MasterData::index');
        
        $routes->post('platform/store', 'MasterData::storePlatform');
        $routes->post('platform/update/(:num)', 'MasterData::updatePlatform/$1');
        $routes->post('platform/delete/(:num)', 'MasterData::deletePlatform/$1');
        
        $routes->post('jenis/store', 'MasterData::storeJenis');
        $routes->post('jenis/update/(:num)', 'MasterData::updateJenis/$1');
        $routes->post('jenis/delete/(:num)', 'MasterData::deleteJenis/$1');
        
        $routes->post('pillar/store', 'MasterData::storePillar');
        $routes->post('pillar/update/(:num)', 'MasterData::updatePillar/$1');
        $routes->post('pillar/delete/(:num)', 'MasterData::deletePillar/$1');
    });
});
