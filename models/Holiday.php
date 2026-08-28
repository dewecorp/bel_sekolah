<?php
/**
 * Model Hari Libur
 */

namespace App\Models;

use Core\Database;

class Holiday extends BaseModel
{
    protected string $table = 'holidays';

    public function isHoliday(string $date): bool
    {
        return Database::fetch('SELECT id FROM holidays WHERE date = ?', [$date]) !== null;
    }

    public function findByDate(string $date): ?array
    {
        return Database::fetch('SELECT * FROM holidays WHERE date = ?', [$date]);
    }
}