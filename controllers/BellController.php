<?php
/**
 * Sistem Bel: API endpoint utk frontend
 * - today: jadwal hari ini + status
 * - triggerCheck: cek apakah ada jadwal yg match waktu sekarang (auto-bell)
 * - manual: catat riwayat bel manual
 * - log: catat riwayat bel otomatis
 */

namespace App\Controllers;

use App\Models\Schedule;
use App\Models\Holiday;
use App\Models\BellHistory;
use App\Models\Audio;
use Core\App;
use Core\Controller;

class BellController extends Controller
{
    public function today(): void
    {
        $today = App::todayDate();
        $dayName = App::dayName();

        $isHoliday = (new Holiday())->isHoliday($today);

        $this->json([
            'dayName'    => $dayName,
            'date'       => $today,
            'currentTime'=> App::todayTime(),
            'isHoliday'  => $isHoliday,
            'schedules'  => (new Schedule())->getDaySchedules($dayName),
            'settings'   => App::settings(),
        ]);
    }

    public function checkAuto(): void
    {
        // Cek sistem aktif
        $settings = App::settings();
        if ((int) ($settings['system_active'] ?? 1) === 0) {
            $this->json(['active' => false, 'message' => 'Sistem nonaktif']);
            return;
        }

        $today = App::todayDate();
        $time = App::todayTime();
        $dayName = App::dayName();

        // Cek hari libur
        if ((new Holiday())->isHoliday($today)) {
            $this->json(['active' => true, 'holiday' => true, 'message' => 'Hari libur']);
            return;
        }

        // Cek jadwal match waktu sekarang
        $schedules = (new Schedule())->getDaySchedules($dayName);

        $matched = array_values(array_filter($schedules, fn($s) => $s['time'] === $time));

        $this->json([
            'active'  => true,
            'holiday' => false,
            'time'    => $time,
            'schedules' => $matched,
        ]);
    }

    public function logAuto(): void
    {
        $scheduleId = (int) ($this->input('schedule_id') ?? 0);
        $schedule = (new Schedule())->find($scheduleId);

        if (!$schedule) {
            $this->json(['error' => 'Jadwal tidak ditemukan'], 404);
            return;
        }

        $bellTypeName = 'Umum';
        if ($schedule['bell_type_id']) {
            $bt = (new \App\Models\BellType())->find((int) $schedule['bell_type_id']);
            $bellTypeName = $bt['name'] ?? 'Umum';
        }

        $bellHistory = new BellHistory();
        $bellHistory->add(
            App::todayDate(),
            $schedule['time'],
            $schedule['name'],
            $bellTypeName,
            $this->input('status') ?: 'berhasil',
            'otomatis'
        );

        $this->json(['message' => 'Riwayat dicatat']);
    }

    public function logManual(): void
    {
        $bellHistory = new BellHistory();
        $id = $bellHistory->add(
            App::todayDate(),
            App::todayTime(),
            $this->input('schedule_name') ?: 'Manual',
            $this->input('bell_type') ?: 'Manual',
            $this->input('status') ?: 'berhasil',
            'manual'
        );

        $this->json(['id' => $id, 'time' => App::todayTime(), 'date' => App::todayDate()]);
    }

    public function audioPack(): void
    {
        // Data audio utk suara default manual (pakai default audio)
        $default = (new Audio())->getDefault();
        $this->json([
            'defaultAudio' => $default ? [
                'name'     => $default['name'],
                'filepath' => $default['filepath'],
                'volume'   => $default['volume'],
                'duration' => $default['duration'],
            ] : null,
            'settings' => App::settings(),
        ]);
    }
}