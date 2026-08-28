<?php
/**
 * Model Jadwal Bel
 */

namespace App\Models;

use Core\Database;

class Schedule extends BaseModel
{
    protected string $table = 'schedules';

    public const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function allWithRelations(?string $day = null, ?int $active = null): array
    {
        $sql = "
            SELECT s.*, bt.name AS bell_type_name, af.name AS audio_name
            FROM schedules s
            LEFT JOIN bell_types bt ON s.bell_type_id = bt.id
            LEFT JOIN audio_files af ON s.audio_id = af.id
        ";
        $conditions = [];
        $params = [];

        if ($day) {
            $conditions[] = 's.day = ?';
            $params[] = $day;
        }
        if ($active !== null) {
            $conditions[] = 's.is_active = ?';
            $params[] = $active;
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY s.day, s.time';

        return Database::fetchAll($sql, $params);
    }

    public function getDaySchedules(string $day, bool $onlyActive = true): array
    {
        $sql = "
            SELECT s.*, bt.name AS bell_type_name, af.filepath, af.volume, af.duration
            FROM schedules s
            LEFT JOIN bell_types bt ON s.bell_type_id = bt.id
            LEFT JOIN audio_files af ON s.audio_id = af.id
            WHERE s.day = ? AND s.is_active = 1
            ORDER BY s.time
        ";
        $params = [$day];
        if (!$onlyActive) {
            $sql = str_replace(' AND s.is_active = 1', '', $sql);
        }
        return Database::fetchAll($sql, $params);
    }

    public function findByDayAndTime(string $day, string $time): ?array
    {
        return Database::fetch(
            'SELECT id, name FROM schedules WHERE day = ? AND time = ? AND is_active = 1',
            [$day, $time]
        );
    }

    public function findConflict(string $day, string $time, int $excludeId): ?array
    {
        return Database::fetch(
            'SELECT id, name FROM schedules WHERE day = ? AND time = ? AND is_active = 1 AND id != ?',
            [$day, $time, $excludeId]
        );
    }
}