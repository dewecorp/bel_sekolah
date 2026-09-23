<?php
$title = 'Dashboard';
use Core\App;
$currentTimeStr = date('H:i');
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
$sysActive = (int)($settings['system_active'] ?? 1) === 1 && !$pubHoliday;
?>

<header class="pub-nav">
  <div class="container pub-nav-inner">
    <div class="brand">
      <div class="brand-logo pub-logo">
        <?php if (App::logoUrl()): ?>
          <img src="<?= htmlspecialchars(App::logoUrl()) ?>" alt="Logo">
        <?php else: ?>
          <div class="brand-logo-icon pub-logo-fallback"><?= App::icon('bell', 'w-6 h-6 text-white') ?></div>
        <?php endif; ?>
      </div>
      <div>
        <div class="brand-title"><?= htmlspecialchars($settings['school_name']) ?></div>
        <div class="brand-sub"><?= htmlspecialchars($settings['school_address'] ?: 'Sistem Bel Sekolah Digital') ?></div>
      </div>
    </div>
    <div class="pub-nav-actions">
      <button type="button" class="theme-toggle-btn" onclick="App.toggleTheme()" title="Ubah Mode Gelap/Terang">
        <?= App::icon('moon', 'w-5 h-5') ?>
      </button>
      <span class="status-pill <?= $sysActive ? 'status-active' : 'status-inactive' ?>" id="systemStatusPill">
        <span class="status-dot"></span><span id="statusText"><?= $pubHoliday ? 'Hari Libur' : ($sysActive ? 'Aktif' : 'Nonaktif') ?></span>
      </span>
      <a href="<?= App::url('/auth/login') ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm pub-admin-btn"><?= App::icon('arrow-right', 'w-4 h-4') ?> Admin</a>
    </div>
  </div>
</header>

<main class="container pub-main">
  <section class="pub-hero">
    <div class="pub-clock animate-slide-in">
      <div class="pub-clock-top">
        <span class="pub-live"><span class="pub-live-dot"></span> LIVE</span>
        <span class="pub-tz"><?= htmlspecialchars($settings['timezone']) ?></span>
      </div>
      <div class="pub-day" id="hariIni"><?= htmlspecialchars($dayName) ?></div>
      <div class="pub-time" id="jamDigital">--:--:--</div>
      <div class="pub-date" id="tanggalHariIni"><?= htmlspecialchars($date) ?></div>
      <div class="pub-clock-stats">
        <div><strong id="statJadwal"><?= count($schedules) ?></strong><span>Jadwal</span></div>
        <div><strong id="statSisa">0</strong><span>Tersisa</span></div>
        <div><strong id="statStatus">SIAP</strong><span>Status</span></div>
      </div>
    </div>

    <div class="pub-next animate-slide-in">
      <div class="pub-next-label"><?= App::icon('bell', 'w-4 h-4') ?> Bel Berikutnya</div>
      <div class="pub-next-time" id="nextBellTime"><?= htmlspecialchars($pubTime) ?></div>
      <div class="pub-next-name" id="nextBellName"><?= htmlspecialchars($pubName) ?></div>
      <div class="badge <?= $pubName === 'Sudah Pulang' ? 'badge-green' : 'badge-slate' ?>" id="nextBellNote" style="margin-top:.5rem;display:<?= !empty($pubNote) ? 'inline-block' : 'none' ?>;"><?= htmlspecialchars($pubNote) ?></div>
      <div class="pub-next-type" id="nextBellType"><?= ($pubNext && !empty($pubNext['bell_type_name'])) ? htmlspecialchars($pubNext['bell_type_name']) : '' ?></div>
      <div class="pub-countdown-box">
        <div class="pub-countdown-label"><?= App::icon('clock', 'w-4 h-4') ?> Menghitung mundur</div>
        <div class="countdown pub-countdown" id="countdown"><?= $pubCountdown ?></div>
      </div>
      <div class="pub-sys">
        <span>Sistem <strong class="<?= $sysActive ? 'text-success' : 'text-danger' ?>" id="statSistem"><?= $sysActive ? 'ON' : 'OFF' ?></strong></span>
        <span>Vol <strong><?= round((float)($settings['default_volume'] ?? 0.8) * 100) ?>%</strong></span>
        <span>Durasi <strong><?= (int)($settings['bell_duration'] ?? 5) ?> dtk</strong></span>
      </div>
    </div>
  </section>

  <section class="pub-grid">
    <div class="card pub-schedule-card">
      <div class="pub-card-head">
        <div class="section-title" style="margin:0;">Jadwal Hari Ini</div>
        <span class="badge badge-blue"><?= htmlspecialchars($dayName) ?></span>
      </div>
      <?php if ($isHoliday): ?>
        <div class="empty-state"><div class="empty-icon"><?= App::icon('sun', 'w-8 h-8') ?></div><div class="empty-title">Hari Libur</div><div class="empty-text">Tidak ada jadwal bel hari ini</div></div>
      <?php elseif (empty($schedules)): ?>
        <div class="empty-state"><div class="empty-icon"><?= App::icon('inbox', 'w-8 h-8') ?></div><div class="empty-title">Tidak Ada Jadwal</div><div class="empty-text">Belum ada jadwal bel untuk hari ini</div></div>
      <?php else: ?>
        <div id="jadwalList" class="pub-timeline">
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

    <div class="card pub-side-card">
      <div class="section-title">Kontrol Cepat</div>
      <p class="text-muted" style="font-size:.82rem;margin-bottom:1rem;">Bunyikan bel manual untuk pengujian suara.</p>
      <button class="big-bell-btn" id="manualBellBtn"><?= App::icon('bell', 'w-6 h-6') ?> Bunyikan Bel</button>
      <div class="pub-info">
        <div><span>Zona Waktu</span><strong><?= htmlspecialchars($settings['timezone']) ?></strong></div>
        <div><span>Volume</span><strong><?= round((float)($settings['default_volume'] ?? 0.8) * 100) ?>%</strong></div>
        <div><span>Durasi</span><strong><?= (int)($settings['bell_duration'] ?? 5) ?> dtk</strong></div>
      </div>
    </div>
  </section>
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
<script src="<?= App::asset('js/dashboard.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/js/dashboard.js') ?: time() ?>"></script>
