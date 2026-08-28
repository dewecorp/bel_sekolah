<?php
/**
 * Front Controller - Semua request masuk lewat sini
 */

use Core\App;
use Core\Router;

define('BASE_PATH', dirname(__DIR__));

require __DIR__ . '/../core/App.php';
App::init();

// Session untuk auth
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$router = new Router();

// --- Rute yang harus selalu bisa diakses tanpa DB ---
$router->get('/install', 'InstallController@index');
$router->post('/install', 'InstallController@run');

// Cek status instalasi database
$installed = false;
try {
    $installed = App::isInstalled();
} catch (\Throwable $e) {
    $installed = false;
}

if (!$installed) {
    // Cek apakah request menuju /install
    $routeUri = strtok(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '?');
    if (!preg_match('#/install$#', $routeUri)) {
        header('Location: ' . App::url('/install'));
        exit;
    }
}

// Atur zona waktu
date_default_timezone_set(App::settings()['timezone'] ?? 'Asia/Jakarta');

/**
 * ================== ROUTE PUBLIC ==================
 */
$router->get('/', 'DashboardController@publicIndex');

// Login
$router->get('/auth/login', 'AuthController@loginPage');
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/logout', 'AuthController@logout');
$router->get('/auth/logout', 'AuthController@logout');

/**
 * ================== API PUBLIK ==================
 * Dipakai dashboard publik & sistem auto-bell
 */
$router->get('/api/bell/today', 'BellController@today');
$router->get('/api/bell/check', 'BellController@checkAuto');
$router->post('/api/bell/log', 'BellController@logAuto');
$router->post('/api/bell/manual', 'BellController@logManual');
$router->get('/api/bell/audio', 'BellController@audioPack');

// Streaming file audio yang diupload
$router->get('/storage/audio/{filename}', 'FileController@streamAudio');

/**
 * ================== ROUTE ADMIN ==================
 */
$router->get('/admin/dashboard', 'DashboardController@adminIndex');

$router->get('/admin/jadwal', 'JadwalController@index');
$router->post('/admin/jadwal', 'JadwalController@store');
$router->put('/admin/jadwal/{id}', 'JadwalController@update');
$router->delete('/admin/jadwal/{id}', 'JadwalController@destroy');

$router->get('/admin/bel', 'BelTypeController@index');
$router->post('/admin/bel', 'BelTypeController@store');
$router->put('/admin/bel/{id}', 'BelTypeController@update');
$router->delete('/admin/bel/{id}', 'BelTypeController@destroy');

$router->get('/admin/audio', 'AudioController@index');
$router->post('/admin/audio', 'AudioController@upload');
$router->put('/admin/audio/{id}', 'AudioController@update');
$router->delete('/admin/audio/{id}', 'AudioController@destroy');

$router->get('/admin/libur', 'HolidayController@index');
$router->post('/admin/libur', 'HolidayController@store');
$router->put('/admin/libur/{id}', 'HolidayController@update');
$router->delete('/admin/libur/{id}', 'HolidayController@destroy');

$router->get('/admin/riwayat', 'RiwayatController@index');
$router->delete('/admin/riwayat/{id}', 'RiwayatController@destroy');
$router->post('/admin/riwayat/clear', 'RiwayatController@clear');

$router->get('/admin/pengaturan', 'SettingsController@index');
$router->post('/admin/pengaturan', 'SettingsController@update');

$router->dispatch();