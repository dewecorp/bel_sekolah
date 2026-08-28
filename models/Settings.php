<?php
/**
 * Model Pengaturan Sekolah
 */

namespace App\Models;

use Core\App;
use Core\Database;

class Settings
{
    public function getAll(): array
    {
        return App::settings();
    }

    public function update(array $data): bool
    {
        $allowed = [
            'school_name',
            'school_logo',
            'school_address',
            'timezone',
            'time_format',
            'default_volume',
            'bell_duration',
            'system_active',
        ];

        $clean = array_intersect_key($data, array_flip($allowed));
        if (empty($clean)) {
            return false;
        }

        $existing = Database::fetch('SELECT * FROM settings WHERE id = 1');
        if (!$existing) {
            Database::execute('INSERT INTO settings (id, updated_at) VALUES (1, NOW())');
        }

        $sets = implode(', ', array_map(fn($c) => "`{$c}` = :{$c}", array_keys($clean)));
        $clean['updated_at'] = date('Y-m-d H:i:s');

        // Reset cache
        App::invalidateSettingsCache();

        return Database::execute(
            "UPDATE settings SET {$sets}, updated_at = :updated_at WHERE id = 1",
            $clean
        ) >= 0;
    }
}