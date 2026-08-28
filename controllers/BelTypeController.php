<?php
/**
 * Manajemen Jenis Bel
 */

namespace App\Controllers;

use App\Models\BellType;
use Core\Controller;

class BelTypeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void
    {
        $this->view('bel-types/index', [
            'bellTypes' => (new BellType())->allByCategory(),
            'categories' => BellType::CATEGORIES,
        ], 'admin.php');
    }

    public function store(): void
    {
        $data = $this->inputAll();

        if (empty(trim($data['name'] ?? ''))) {
            $this->json(['error' => 'Nama jenis bel harus diisi'], 400);
            return;
        }
        if (empty($data['category']) || !in_array($data['category'], BellType::CATEGORIES)) {
            $this->json(['error' => 'Kategori tidak valid'], 400);
            return;
        }

        $id = (new BellType())->create([
            'name'     => trim($data['name']),
            'category' => $data['category'],
        ]);

        $this->json(['id' => $id, 'message' => 'Jenis bel berhasil ditambahkan'], 201);
    }

    public function update(string $id): void
    {
        $model = new BellType();
        if (!$model->find((int) $id)) {
            $this->json(['error' => 'Jenis bel tidak ditemukan'], 404);
            return;
        }

        $data = $this->inputAll();
        $updateData = [];

        if (isset($data['name'])) {
            if (empty(trim($data['name']))) {
                $this->json(['error' => 'Nama tidak boleh kosong'], 400);
                return;
            }
            $updateData['name'] = trim($data['name']);
        }
        if (isset($data['category'])) {
            if (!in_array($data['category'], BellType::CATEGORIES)) {
                $this->json(['error' => 'Kategori tidak valid'], 400);
                return;
            }
            $updateData['category'] = $data['category'];
        }

        $model->update((int) $id, $updateData);
        $this->json(['message' => 'Jenis bel berhasil diperbarui']);
    }

    public function destroy(string $id): void
    {
        $model = new BellType();
        if (!$model->find((int) $id)) {
            $this->json(['error' => 'Jenis bel tidak ditemukan'], 404);
            return;
        }

        $model->delete((int) $id);
        $this->json(['message' => 'Jenis bel berhasil dihapus']);
    }
}