<?php
/**
 * Manajemen Jadwal Bel
 */

namespace App\Controllers;

use App\Models\Schedule;
use App\Models\BellType;
use Core\Controller;

class JadwalController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    public function index(): void
    {
        $this->view('jadwal/index', [
            'schedules' => (new Schedule())->allWithRelations(),
            'bellTypes' => (new BellType())->allByCategory(),
            'days'      => Schedule::DAYS,
        ], 'admin.php');
    }

    public function store(): void
    {
        $data = $this->inputAll();
        $schedule = new Schedule();

        $errors = $this->validate($data);

        if (!empty($errors)) {
            $this->json(['error' => $errors[0]], 400);
            return;
        }

        // Cek bentrok
        $conflict = $schedule->findByDayAndTime($data['day'], $data['time']);
        if ($conflict) {
            $this->json(['error' => "Bentrok dengan jadwal \"{$conflict['name']}\" pada jam yang sama"], 409);
            return;
        }

        $id = $schedule->create([
            'day'          => $data['day'],
            'time'         => $data['time'],
            'name'         => trim($data['name']),
            'bell_type_id' => !empty($data['bell_type_id']) ? $data['bell_type_id'] : null,
            'audio_id'     => null,
            'is_active'    => isset($data['is_active']) ? (int) $data['is_active'] : 1,
        ]);

        $this->json(['id' => $id, 'message' => 'Jadwal berhasil ditambahkan'], 201);
    }

    public function update(string $id): void
    {
        $schedule = new Schedule();
        if (!$schedule->find((int) $id)) {
            $this->json(['error' => 'Jadwal tidak ditemukan'], 404);
            return;
        }

        $data = $this->inputAll();
        $errors = $this->validate($data, true);

        if (!empty($errors)) {
            $this->json(['error' => $errors[0]], 400);
            return;
        }

        // Cek bentrok (kecuali jadwal itu sendiri)
        if (!empty($data['day']) && !empty($data['time'])) {
            $conflict = $schedule->findConflict($data['day'], $data['time'], (int) $id);
            if ($conflict) {
                $this->json(['error' => "Bentrok dengan jadwal \"{$conflict['name']}\""], 409);
                return;
            }
        }

        $updateData = [];
        foreach (['day', 'time', 'name', 'is_active', 'bell_type_id'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }
        if (array_key_exists('bell_type_id', $updateData) && empty($updateData['bell_type_id'])) {
            $updateData['bell_type_id'] = null;
        }

        $schedule->update((int) $id, $updateData);
        $this->json(['message' => 'Jadwal berhasil diperbarui']);
    }

    public function destroy(string $id): void
    {
        $schedule = new Schedule();
        if (!$schedule->find((int) $id)) {
            $this->json(['error' => 'Jadwal tidak ditemukan'], 404);
            return;
        }

        $schedule->delete((int) $id);
        $this->json(['message' => 'Jadwal berhasil dihapus']);
    }

    private function validate(array $data, bool $partial = false): array
    {
        $errors = [];

        if (!$partial || isset($data['day'])) {
            if (empty($data['day']) || !in_array($data['day'], Schedule::DAYS)) {
                $errors[] = 'Hari tidak valid';
            }
        }

        if (!$partial || isset($data['time'])) {
            if (empty($data['time']) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $data['time'])) {
                $errors[] = 'Format waktu tidak valid (HH:MM, 24 jam)';
            }
        }

        if (!$partial || isset($data['name'])) {
            if (empty(trim($data['name'] ?? ''))) {
                $errors[] = 'Nama bel harus diisi';
            }
        }

        return $errors;
    }
}