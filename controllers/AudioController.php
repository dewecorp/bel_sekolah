<?php
/**
 * Manajemen Audio Bel (upload, preview, kelola)
 */

namespace App\Controllers;

use App\Models\Audio;
use App\Models\BellType;
use Core\App;
use Core\Controller;

class AudioController extends Controller
{
    private const ALLOWED_TYPES = [
        'audio/mpeg' => 'mp3',
        'audio/mp3'  => 'mp3',
        'audio/wav'  => 'wav',
        'audio/x-wav' => 'wav',
        'audio/ogg'  => 'ogg',
        'audio/aac'  => 'aac',
    ];

    private const MAX_SIZE = 10 * 1024 * 1024; // 10MB

    public function __construct()
    {
        parent::__construct();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('audio/index', [
            'audioFiles' => (new Audio())->allWithType(),
            'bellTypes'  => (new BellType())->allByCategory(),
        ], 'admin.php');
    }

    public function upload(): void
    {
        $this->requireAuth();
        $audioModel = new Audio();

        // Validasi file
        if (empty($_FILES['file'] ?? null) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => $this->uploadError($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE)], 400);
            return;
        }

        $file = $_FILES['file'];
        $mime = mime_content_type($file['tmp_name']) ?: $file['type'];

        if (!isset(self::ALLOWED_TYPES[$mime])) {
            $this->json(['error' => 'Format file tidak didukung. Gunakan MP3, WAV, OGG, atau AAC'], 400);
            return;
        }

        if ($file['size'] > self::MAX_SIZE) {
            $this->json(['error' => 'Ukuran file maksimal 10MB'], 400);
            return;
        }

        $ext = self::ALLOWED_TYPES[$mime];
        $filename = 'audio_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = App::storagePath('audio');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $dest = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $this->json(['error' => 'Gagal menyimpan file audio'], 500);
            return;
        }

        $name = trim($_POST['name'] ?? '') ?: pathinfo($file['name'], PATHINFO_FILENAME);
        $bellTypeId = !empty($_POST['bell_type_id']) ? (int) $_POST['bell_type_id'] : null;
        $volume = isset($_POST['volume']) ? (float) $_POST['volume'] : 0.8;
        $duration = isset($_POST['duration']) ? max(1, (int) $_POST['duration']) : 5;

        $id = $audioModel->create([
            'name'         => $name,
            'filename'     => $filename,
            'filepath'     => '/storage/audio/' . $filename,
            'bell_type_id' => $bellTypeId,
            'is_default'   => 0,
            'volume'       => $volume,
            'duration'     => $duration,
        ]);

        $this->json(['id' => $id, 'message' => 'Audio berhasil diunggah'], 201);
    }

    public function update(string $id): void
    {
        $this->requireAuth();
        $model = new Audio();
        $audio = $model->find((int) $id);
        if (!$audio) {
            $this->json(['error' => 'Audio tidak ditemukan'], 404);
            return;
        }

        $data = $this->inputAll();
        $updateData = [];

        if (isset($data['name'])) {
            if (empty(trim($data['name']))) {
                $this->json(['error' => 'Nama audio tidak boleh kosong'], 400);
                return;
            }
            $updateData['name'] = trim($data['name']);
        }
        if (array_key_exists('bell_type_id', $data)) {
            $updateData['bell_type_id'] = !empty($data['bell_type_id']) ? (int) $data['bell_type_id'] : null;
        }
        if (isset($data['volume'])) {
            $updateData['volume'] = max(0, min(1, (float) $data['volume']));
        }
        if (isset($data['duration'])) {
            $updateData['duration'] = max(1, (int) $data['duration']);
        }
        if (isset($data['is_default'])) {
            if ((int) $data['is_default'] === 1) {
                $model->unsetAllDefaults();
            }
            $updateData['is_default'] = (int) $data['is_default'];
        }

        $model->update((int) $id, $updateData);
        $this->json(['message' => 'Audio berhasil diperbarui']);
    }

    public function destroy(string $id): void
    {
        $this->requireAuth();
        $model = new Audio();
        $audio = $model->find((int) $id);
        if (!$audio) {
            $this->json(['error' => 'Audio tidak ditemukan'], 404);
            return;
        }

        // Hapus file fisik
        $filePath = App::storagePath('audio') . '/' . $audio['filename'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $model->delete((int) $id);
        $this->json(['message' => 'Audio berhasil dihapus']);
    }

    private function uploadError(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas',
            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian',
            UPLOAD_ERR_NO_FILE => 'File audio harus dipilih',
            default => 'Gagal mengunggah file',
        };
    }
}