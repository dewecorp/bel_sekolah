<?php
/**
 * Manajemen Hari Libur
 */

namespace App\Controllers;

use App\Models\Holiday;
use Core\Controller;

class HolidayController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void
    {
        $this->view('holidays/index', [
            'holidays' => (new Holiday())->findAll('date'),
        ], 'admin.php');
    }

    public function store(): void
    {
        $data = $this->inputAll();

        if (empty($data['date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
            $this->json(['error' => 'Format tanggal tidak valid (YYYY-MM-DD)'], 400);
            return;
        }
        if (empty(trim($data['name'] ?? ''))) {
            $this->json(['error' => 'Nama hari libur harus diisi'], 400);
            return;
        }

        $model = new Holiday();
        if ($model->findByDate($data['date'])) {
            $this->json(['error' => 'Tanggal libur sudah ada'], 409);
            return;
        }

        $id = $model->create([
            'date'        => $data['date'],
            'name'        => trim($data['name']),
            'description' => trim($data['description'] ?? ''),
        ]);

        $this->json(['id' => $id, 'message' => 'Hari libur berhasil ditambahkan'], 201);
    }

    public function update(string $id): void
    {
        $model = new Holiday();
        if (!$model->find((int) $id)) {
            $this->json(['error' => 'Hari libur tidak ditemukan'], 404);
            return;
        }

        $data = $this->inputAll();
        $updateData = [];

        if (isset($data['date'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
                $this->json(['error' => 'Format tanggal tidak valid'], 400);
                return;
            }
            $updateData['date'] = $data['date'];
        }
        if (isset($data['name'])) {
            if (empty(trim($data['name']))) {
                $this->json(['error' => 'Nama tidak boleh kosong'], 400);
                return;
            }
            $updateData['name'] = trim($data['name']);
        }
        if (array_key_exists('description', $data)) {
            $updateData['description'] = trim($data['description']);
        }

        $model->update((int) $id, $updateData);
        $this->json(['message' => 'Hari libur berhasil diperbarui']);
    }

    public function destroy(string $id): void
    {
        $model = new Holiday();
        if (!$model->find((int) $id)) {
            $this->json(['error' => 'Hari libur tidak ditemukan'], 404);
            return;
        }
        $model->delete((int) $id);
        $this->json(['message' => 'Hari libur berhasil dihapus']);
    }
}