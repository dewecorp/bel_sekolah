<?php
/**
 * Riwayat Bel
 */

namespace App\Controllers;

use App\Models\BellHistory;
use Core\Controller;

class RiwayatController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void
    {
        $date = $_GET['date'] ?? null;
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = null;
        }

        $model = new BellHistory();

        // Retensi otomatis: hapus riwayat > 24 jam
        $model->pruneOlderThan(24);

        $this->view('riwayat/index', [
            'history'  => $model->latest(200, $date),
            'filterDate' => $date,
            'stats'    => $model->stats(),
        ], 'admin.php');
    }

    public function destroy(string $id): void
    {
        $model = new BellHistory();
        if (!$model->find((int) $id)) {
            $this->json(['error' => 'Riwayat tidak ditemukan'], 404);
            return;
        }
        $model->delete((int) $id);
        $this->json(['message' => 'Riwayat berhasil dihapus']);
    }

    public function clear(): void
    {
        $model = new BellHistory();
        $date = $this->input('date', null);
        $count = $model->clear($date ?: null);
        $this->json(['message' => "{$count} riwayat berhasil dihapus"]);
    }
}