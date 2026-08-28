<?php
$title = 'Dashboard';
use Core\App;
$currentTimeStr = date('H:i');
?>

<header class="public-header">
  <div class="container public-header-inner">
    <div class="brand">
      <div class="brand-logo">
        <?php if (App::logoUrl()): ?>
          <img src="<?= htmlspecialchars(App::logoUrl()) ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
        <?php else: ?>
          <div class="brand-logo-icon" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><?= App::icon('bell', 'w-7 h-7 text-white') ?></div>
        <?php endif; ?>
      </div>
      <div>
        <div class="brand-title"><?= htmlspecialchars($settings['school_name']) ?></div>
        <div class="brand-sub"><?= htmlspecialchars($settings['school_address'] ?: 'Sistem Bel Sekolah Digital') ?></div>
      </div>
    </div>
    <div class="topbar-actions">
      <span class="status-pill <?= $isHoliday ? 'status-inactive' : ((int)($settings['system_active'] ?? 1) === 1 ? 'status-active' : 'status-inactive') ?>" id="systemStatusPill">
        <span class="status-dot"></span><span id="statusText"><?= $isHoliday ? 'Hari Libur' : ((int)($settings['system_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif') ?></span>
      </span>
      <a href="<?= App::url('/auth/login') ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm"><span class="inline-flex items-center gap-1.5"><?= App::icon('arrow-right', 'w-4 h-4') ?> Admin</span></a>
    </div>
  </div>
</header>

<main class="container" style="padding-top:1.25rem;padding-bottom:2rem;">
  <div class="grid grid-2" style="margin-bottom:1.25rem;">
    <div class="card clock-card card-blue">
      <div class="clock-day" id="hariIni"><?= htmlspecialchars($dayName) ?></div>
      <div class="clock-time" id="jamDigital">--:--:--</div>
      <div class="clock-date" id="tanggalHariIni"><?= htmlspecialchars($date) ?></div>
    </div>
    <div class="card next-bell card-emerald card-pad">
      <div>
        <div class="text-muted" style="font-size:0.8125rem;margin-bottom:0.5rem;"><?= App::icon('bell', 'w-4 h-4 inline-block align-text-bottom') ?> Bel Berikutnya</div>
        <?php
          $pubNext = $nextBell ?? null;
          $pubHoliday = $isHoliday ?? false;
          $pubHasSchedule = !empty($schedules);
          $pubCountdown = '--:--:--';
          if (!$pubHoliday && $pubNext) {
              $tz = new DateTimeZone($settings['timezone'] ?? 'Asia/Jakarta');
              $nowDt = new DateTime('now', $tz);
              [$hh, $mm] = array_pad(explode(':', $pubNext['time']), 2, '00');
              $tDt = new DateTime(sprintf('today %s:%s', $hh, $mm), $tz);
              if ($tDt <= $nowDt) $tDt->modify('+1 day');
              $diff = $nowDt->diff($tDt);
              $pubCountdown = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
          }
          if ($pubHoliday) { $pubName = 'Hari Libur'; $pubTime = '--:--'; $pubNote = ''; }
          elseif ($pubNext) { $pubName = $pubNext['name']; $pubTime = $pubNext['time']; $pubNote = ''; }
          elseif ($pubHasSchedule) { $pubName = 'Sudah Pulang'; $pubTime = '--:--'; $pubNote = 'Semua bel untuk hari ini telah selesai'; }
          else { $pubName = 'Tidak ada jadwal'; $pubTime = '--:--'; $pubNote = 'Belum ada jadwal bel ditetapkan'; }
        ?>
        <div class="next-bell-time" id="nextBellTime"><?= htmlspecialchars($pubTime) ?></div>
        <div class="next-bell-name" id="nextBellName"><?= htmlspecialchars($pubName) ?></div>
        <div class="badge <?= $pubName === 'Sudah Pulang' ? 'badge-green' : 'badge-slate' ?>" id="nextBellNote" style="margin-top:0.4rem;display:<?= !empty($pubNote) ? 'inline-block' : 'none' ?>;"><?= htmlspecialchars($pubNote) ?></div>
        <div class="text-muted" id="nextBellType" style="font-size:0.75rem;"><?= ($pubNext && !empty($pubNext['bell_type_name'])) ? htmlspecialchars($pubNext['bell_type_name']) : '' ?></div>
      </div>
      <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
        <div class="text-muted" style="font-size:0.75rem;margin-bottom:0.25rem;"><?= App::icon('clock', 'w-4 h-4 inline-block align-text-bottom') ?> Countdown</div>
        <div class="countdown" id="countdown"><?= $pubCountdown ?></div>
      </div>
    </div>
  </div>

  <div class="grid grid-4" style="margin-bottom:1.25rem;" id="statsRow">
    <div class="card stat-card card-emerald">
      <div class="stat-icon stat-icon-blue"><?= App::icon('clipboard', 'w-6 h-6') ?></div>
      <div><div class="stat-value" id="statJadwal"><?= count($schedules) ?></div><div class="stat-label">Jadwal Hari Ini</div></div>
    </div>
    <div class="card stat-card card-amber">
      <div class="stat-icon stat-icon-amber"><?= App::icon('clock', 'w-6 h-6') ?></div>
      <div><div class="stat-value" id="statSisa">0</div><div class="stat-label">Bel Tersisa</div></div>
    </div>
    <div class="card stat-card card-emerald">
      <div class="stat-icon stat-icon-green"><?= App::icon('bell', 'w-6 h-6') ?></div>
      <div><div class="stat-value" id="statStatus">SIAP</div><div class="stat-label">Status Bell</div></div>
    </div>
    <div class="card stat-card card-purple">
      <div class="stat-icon stat-icon-purple"><?= App::icon('bolt', 'w-6 h-6') ?></div>
      <div><div class="stat-value <?= (int)($settings['system_active'] ?? 1) === 1 ? 'text-success' : 'text-danger' ?>" id="statSistem"><?= (int)($settings['system_active'] ?? 1) === 1 ? 'ON' : 'OFF' ?></div><div class="stat-label">Sistem</div></div>
    </div>
  </div>

  <div class="grid grid-3">
    <div class="card card-pad card-emerald" style="grid-column: span 2;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
        <div class="section-title">Jadwal Bel Hari Ini</div>
        <span class="badge badge-blue"><?= htmlspecialchars($dayName) ?></span>
      </div>

      <?php if ($isHoliday): ?>
        <div class="empty-state"><div class="empty-icon"><?= App::icon('sun', 'w-8 h-8') ?></div><div class="empty-title">Hari Libur</div><div class="empty-text">Tidak ada jadwal bel hari ini</div></div>
      <?php elseif (empty($schedules)): ?>
        <div class="empty-state"><div class="empty-icon"><?= App::icon('inbox', 'w-8 h-8') ?></div><div class="empty-title">Tidak Ada Jadwal</div><div class="empty-text">Belum ada jadwal bel untuk hari ini</div></div>
      <?php else: ?>
        <div id="jadwalList">
        <?php foreach ($schedules as $s): ?>
          <div class="jadwal-item" data-time="<?= htmlspecialchars($s['time']) ?>" id="jadwalRow-<?= (int)$s['id'] ?>">
            <div class="jadwal-time"><?= htmlspecialchars($s['time']) ?></div>
            <div class="jadwal-info">
              <div class="jadwal-name"><?= htmlspecialchars($s['name']) ?></div>
              <?php if (!empty($s['bell_type_name'])): ?><div class="jadwal-type"><?= htmlspecialchars($s['bell_type_name']) ?></div><?php endif; ?>
            </div>
            <span class="badge badge-slate" data-role="status">Menunggu</span>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="card card-pad">
      <div class="section-title">Kontrol Manual</div>
      <p class="text-muted" style="font-size:0.8125rem;margin-bottom:1rem;">Bunyikan bel secara manual untuk pengujian</p>
      <button class="big-bell-btn" id="manualBellBtn"><?= App::icon('bell', 'w-6 h-6') ?> Bunyikan Bel Sekarang</button>

      <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);font-size:0.8125rem;color:#64748b;">
        <div style="display:flex;justify-content:space-between;padding:0.25rem 0;"><span>Zona Waktu</span><strong><?= htmlspecialchars($settings['timezone']) ?></strong></div>
        <div style="display:flex;justify-content:space-between;padding:0.25rem 0;"><span>Volume Default</span><strong><?= round((float)($settings['default_volume'] ?? 0.8) * 100) ?>%</strong></div>
        <div style="display:flex;justify-content:space-between;padding:0.25rem 0;"><span>Durasi Bel</span><strong><?= (int)($settings['bell_duration'] ?? 5) ?> dtk</strong></div>
      </div>
    </div>
  </div>
</main>

<script>
window.BASE_URL = <?= json_encode($baseUrl) ?>;
window.TIMEZONE = <?= json_encode($settings['timezone'] ?? 'Asia/Jakarta') ?>;
window.SCHEDULES = <?= json_encode($schedules) ?>;
window.SYSTEM_ACTIVE = <?= (int)($settings['system_active'] ?? 1) ?>;
window.BELL_DURATION = <?= (int)($settings['bell_duration'] ?? 5) ?>;
window.DEFAULT_VOLUME = <?= (float)($settings['default_volume'] ?? 0.8) ?>;
window.IS_HOLIDAY = <?= $isHoliday ? 'true' : 'false' ?>;
</script>
<script src="<?= App::asset('js/dashboard.js') ?>"></script>
