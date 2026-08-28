<?php
/**
 * Dashboard: publik & admin
 */

namespace App\Controllers;

use App\Models\Schedule;
use App\Models\BellHistory;
use App\Models\BellType;
use App\Models\Holiday;
use Core\Auth;
use Core\Controller;
use Core\App;

class DashboardController extends Controller
{
    public function publicIndex(): void
    {
        $scheduleModel = new Schedule();
        $today = App::todayDate();
        $dayName = App::dayName();

        $isHoliday = (new Holiday())->isHoliday($today);
        $schedules = $scheduleModel->getDaySchedules($dayName);

        // Bel berikutnya (hanya yang belum lewat hari ini)
        $nowTime = App::todayTime();
        $nextBell = null;
        foreach ($schedules as $s) {
            if ($s['time'] > $nowTime) {
                $nextBell = $s;
                break;
            }
        }

        $this->view('dashboard/public', [
            'dayName'    => $dayName,
            'date'       => $today,
            'isHoliday' => $isHoliday,
            'schedules' => $schedules,
            'nextBell'  => $nextBell,
            'currentTime' => $nowTime,
        ], 'public.php');
    }

    public function adminIndex(): void
    {
        $this->requireAuth();

        $historyModel = new BellHistory();
        $scheduleModel = new Schedule();
        $today = App::todayDate();
        $dayName = App::dayName();
        $isHoliday = (new Holiday())->isHoliday($today);
        $schedules = $scheduleModel->getDaySchedules($dayName);

        // Retensi otomatis: hapus riwayat > 24 jam
        $historyModel->pruneOlderThan(24);

        // Bel berikutnya (hanya jadwal yang belum lewat hari ini)
        $nowTime = App::todayTime();
        $nextBell = null;
        foreach ($schedules as $s) {
            if ($s['time'] > $nowTime) {
                $nextBell = $s;
                break;
            }
        }

        $this->view('dashboard/admin', [
            'stats' => [
                'totalSchedules' => (new Schedule())->count(),
                'totalBellTypes' => (new BellType())->count(),
                'totalHolidays'  => (new Holiday())->count(),
            ],
            'history' => $historyModel->latest(10),
            'today'   => [
                'dayName'    => $dayName,
                'date'       => $today,
                'isHoliday'  => $isHoliday,
                'currentTime'=> $nowTime,
                'schedules'  => $schedules,
                'nextBell'   => $nextBell,
            ],
        ], 'admin.php');
    }
}