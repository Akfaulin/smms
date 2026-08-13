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
// Dashboard — Beranda
// ============================================================
$routes->group('dashboard', function (RouteCollection $routes) {

    // Beranda
    $routes->get('', 'Dashboard::index');
    $routes->get('/', 'Dashboard::index');

    // Profil
    $routes->get('profil', 'Profile::index');
    $routes->post('profil/update', 'Profile::updateProfile');
    $routes->post('profil/ganti-password', 'Profile::gantiPassword');

    // Notifikasi
    $routes->get('notifikasi/unread-count', 'Notifications::unreadCount');
    $routes->get('notifikasi/list', 'Notifications::list');
    $routes->post('notifikasi/baca/(:num)', 'Notifications::baca/$1');
    $routes->post('notifikasi/baca-semua', 'Notifications::bacaSemua');

    // Laporan
    $routes->get('laporan', 'Laporan::index');
    $routes->get('laporan/export', 'Laporan::export');

    // ----------------------------------------------------------
    // Content Plan & Ide Konten
    // ----------------------------------------------------------
    $routes->get('ide-konten', 'IdeKonten::index');
    $routes->post('ide-konten/store', 'IdeKonten::store');
    $routes->get('trend-ai', 'TrendAi::index');
    $routes->post('trend-ai/generate-hook', 'TrendAi::generateHook');
    $routes->get('tugas-creator', 'TugasCreator::index');
    $routes->get('asset-library', 'AssetLibrary::index');
    $routes->post('asset-library/store', 'AssetLibrary::store');
    $routes->post('asset-library/delete/(:num)', 'AssetLibrary::delete/$1');
    $routes->get('approval-manager', 'ApprovalManager::index');
    $routes->post('approval-manager/ai-review/(:num)', 'ApprovalManager::aiReview/$1');
    $routes->post('approval-manager/ai-caption/(:num)', 'ApprovalManager::aiCaption/$1');
    $routes->get('jadwal-upload', 'JadwalUpload::index');
    $routes->post('jadwal-upload/publish/(:num)', 'JadwalUpload::publish/$1');
    $routes->post('jadwal-upload/publish-otomatis/(:num)', 'JadwalUpload::publishOtomatis/$1');
    $routes->get('kalender-tayang', 'KalenderTayang::index');


    $routes->get('content-plan', 'ContentPlan::index');
    $routes->post('content-plan/store', 'ContentPlan::store');
    $routes->post('content-plan/update/(:num)', 'ContentPlan::update/$1');
    $routes->post('content-plan/delete/(:num)', 'ContentPlan::delete/$1');

    // Endpoint kritis: perubahan status HANYA lewat sini (§4, §6)
    $routes->post('content-plan/transition/(:num)', 'ContentPlan::transition/$1');

    // Endpoint AI (Tahap 9)
    $routes->post('content-plan/ai-caption/(:num)', 'ContentPlan::generateCaption/$1');
    $routes->post('content-plan/update-caption/(:num)', 'ContentPlan::updateCaption/$1');
    $routes->post('content-plan/ai-ideas', 'ContentPlan::generateIdeas');
    $routes->post('content-plan/ai-brief', 'ContentPlan::generateBrief');
    $routes->post('content-plan/design-url/(:num)', 'ContentPlan::updateDesignUrl/$1');
    $routes->post('content-plan/image-url/(:num)', 'ContentPlan::updateImageUrl/$1');
    $routes->post('content-plan/upload-image/(:num)', 'ContentPlan::uploadImage/$1');

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

        // Master Data (Platform, Jenis Konten, Pillar)
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

        // Master Bisnis (superadmin/owner only)
        $routes->get('bisnis', 'Bisnis::index');
        $routes->post('bisnis/store', 'Bisnis::store');
        $routes->post('bisnis/update/(:num)', 'Bisnis::update/$1');
        $routes->post('bisnis/delete/(:num)', 'Bisnis::delete/$1');
    });

    // Business Switcher — ganti bisnis aktif via session (POST & GET)
    $routes->match(['get', 'post'], 'bisnis/switch/(:num)', 'Bisnis::switch/$1');
});
