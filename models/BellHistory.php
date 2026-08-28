<?php
/**
 * Model Riwayat Bel
 */

namespace App\Models;

use Core\Database;

class BellHistory extends BaseModel
{
    protected string $table = 'bell_history';

    public function add(string $date, string $time, string $scheduleName, string $bellType = 'Umum', string $status = 'berhasil', string $mode = 'otomatis'): int
    {
        return $this->create([
            'date'          => $date,
            'time'          => $time,
            'schedule_name' => $scheduleName,
            'bell_type'     => $bellType,
            'status'        => $status,
            'mode'          => $mode,
        ]);
    }

    public function latest(int $limit = 50, ?string $date = null): array
    {
        $sql = 'SELECT * FROM bell_history';
        $params = [];
        if ($date) {
            $sql .= ' WHERE date = ?';
            $params[] = $date;
        }
        $sql .= ' ORDER BY id DESC LIMIT ' . (int) $limit;
        return Database::fetchAll($sql, $params);
    }

    /**
     * Hapus otomatis riwayat yang lebih lama dari X jam (retensi data).
     */
    public function pruneOlderThan(int $hours = 24): int
    {
        return Database::execute(
            'DELETE FROM bell_history WHERE created_at < DATE_SUB(NOW(), INTERVAL ? HOUR)',
            [$hours]
        );
    }

    public function stats(?int $days = 7): array
    {
        $from = date('Y-m-d', strtotime("-{$days} days"));
        return [
            'total'    => (int) $this->count(),
            'berhasil' => (int) (Database::fetch(
                "SELECT COUNT(*) as c FROM bell_history WHERE status = 'berhasil'"
            )['c'] ?? 0),
            'otomatis' => (int) (Database::fetch(
                "SELECT COUNT(*) as c FROM bell_history WHERE mode = 'otomatis'"
            )['c'] ?? 0),
            'manual'   => (int) (Database::fetch(
                "SELECT COUNT(*) as c FROM bell_history WHERE mode = 'manual'"
            )['c'] ?? 0),
        ];
    }

    public function clear(?string $date = null): int
    {
        if ($date) {
            return Database::execute('DELETE FROM bell_history WHERE date = ?', [$date]);
        }
        return Database::execute('DELETE FROM bell_history');
    }
}