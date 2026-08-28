<?php
/**
 * Instalasi Database (schema + seed data + user admin)
 */

namespace App\Controllers;

use Core\App;
use Core\Controller;
use Core\Database;

class InstallController extends Controller
{
    public function index(): void
    {
        // Jika sudah terpasang, redirect ke home
        if (App::isInstalled()) {
            $this->redirect('/');
        }

        $this->view('install', [
            'dbConfig' => require __DIR__ . '/../config/database.php',
        ], 'install.php');
    }

    public function run(): void
    {
        $schema = file_get_contents(BASE_PATH . '/sql/schema.sql');
        $seed   = file_get_contents(BASE_PATH . '/sql/seed.sql');

        try {
            $config = require BASE_PATH . '/config/database.php';

            // 1. Pastikan database ada (buat jika belum)
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['charset']
            );
            $master = new \PDO($dsn, $config['username'], $config['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            $dbName = $config['database'];
            $master->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // 2. Terapkan schema + seed
            $pdo = Database::getConnection();
            $pdo->exec($schema);
            $pdo->exec($seed);

            // Pastikan user admin ada (dengan hash bcrypt yang benar)
            $adminExists = Database::fetch('SELECT id FROM users WHERE username = ?', ['admin']);
            if (!$adminExists) {
                Database::execute(
                    'INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)',
                    ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Administrator', 'admin']
                );
            }

            App::invalidateSettingsCache();

            $this->json(['success' => true, 'message' => 'Instalasi berhasil', 'redirect' => '/auth/login']);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Instalasi gagal: ' . $e->getMessage()], 500);
        }
    }
}