<?php
/**
 * Konfigurasi Database MySQL
 * Sesuaikan dengan kredensial MySQL pada Laragon Anda.
 * Default Laragon: user root, tanpa password.
 */

return [
    'host'     => getenv('DB_HOST') ?: '127.0.0.1',
    'port'     => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_NAME') ?: 'bel_sekolah',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASS') ?: '',
    'charset'  => 'utf8mb4',
];