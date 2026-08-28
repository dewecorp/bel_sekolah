<?php
/**
 * Pengaturan Sekolah
 */

namespace App\Controllers;

use App\Models\Settings;
use Core\Auth;
use Core\Controller;

class SettingsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void
    {
        $this->view('settings/index', [
            'settings' => (new Settings())->getAll(),
        ], 'admin.php');
    }

    public function update(): void
    {
        $model = new Settings();
        $data = $this->inputAll();

        // Upload logo sekolah (multipart/form-data)
        if (!empty($_FILES['school_logo']['tmp_name'] ?? null) && is_uploaded_file($_FILES['school_logo']['tmp_name'])) {
            $result = $this->handleLogoUpload();
            if (isset($result['error'])) {
                $this->json(['error' => $result['error']], 400);
                return;
            }
            // Hapus logo lama jika ada
            $old = $model->getAll()['school_logo'] ?? null;
            if ($old) {
                $oldPath = BASE_PATH . '/public/' . ltrim($old, '/');
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $data['school_logo'] = $result['path'];
        }

        // Validasi
        if (isset($data['default_volume'])) {
            $data['default_volume'] = max(0, min(1, (float) $data['default_volume']));
        }
        if (isset($data['bell_duration'])) {
            $data['bell_duration'] = max(1, min(300, (int) $data['bell_duration']));
        }
        if (isset($data['time_format'])) {
            $data['time_format'] = in_array($data['time_format'], ['12', '24']) ? $data['time_format'] : '24';
        }
        if (isset($data['timezone'])) {
            $data['timezone'] = in_array($data['timezone'], ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])
                ? $data['timezone'] : 'Asia/Jakarta';
        }

        $model->update($data);

        // Ganti password jika diminta
        if (!empty($data['new_password'])) {
            $result = Auth::changePassword(
                Auth::id(),
                $data['old_password'] ?? '',
                $data['new_password']
            );
            if (!empty($result['error'])) {
                $this->json(['error' => $result['error']], 400);
                return;
            }
        }

        $this->json(['message' => 'Pengaturan berhasil disimpan']);
    }

    private function handleLogoUpload(): array
    {
        $file = $_FILES['school_logo'];
        $allowed = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $mime = mime_content_type($file['tmp_name']) ?: $file['type'];

        if (!isset($allowed[$mime])) {
            return ['error' => 'Format logo tidak didukung. Gunakan PNG, JPG, GIF, atau WebP'];
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['error' => 'Ukuran logo maksimal 2MB'];
        }

        $ext = $allowed[$mime];
        $filename = 'logo_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir = BASE_PATH . '/public/uploads/logo';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return ['error' => 'Gagal menyimpan file logo'];
        }

        return ['path' => 'uploads/logo/' . $filename];
    }
}