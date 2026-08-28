<?php $title = 'Dashboard Admin'; ?>
<?php $activeMenu = 'dashboard'; ?>
<?php use Core\App; ?>

<div class="page-header">
  <div>
    <div class="page-header-title">Dashboard Admin</div>
    <div class="page-header-sub">Ringkasan sistem bel sekolah</div>
  </div>
  <div class="page-header-actions">
    <button class="btn btn-gradient" id="manualBellBtn"><?= App::icon('bell', 'w-5 h-5') ?> Bunyikan Bel</button>
  </div>
</div>

<div class="grid grid-4" style="margin-bottom:1.5rem;">
  <div class="card stat-card card-emerald">
    <div class="stat-icon stat-icon-blue"><?= App::icon('calendar', 'w-6 h-6') ?></div>
    <div>
      <div class="stat-value"><?= (int)$stats['totalSchedules'] ?></div>
      <div class="stat-label">Total Jadwal</div>
    </div>
  </div>
  <div class="card stat-card card-amber">
    <div class="stat-icon stat-icon-amber"><?= App::icon('bell', 'w-6 h-6') ?></div>
    <div>
      <div class="stat-value"><?= (int)$stats['totalBellTypes'] ?></div>
      <div class="stat-label">Jenis Bel</div>
    </div>
  </div>
  <div class="card stat-card card-purple">
    <div class="stat-icon stat-icon-purple"><?= App::icon('calendar-days', 'w-6 h-6') ?></div>
    <div>
      <div class="stat-value"><?= (int)$stats['totalHolidays'] ?></div>
      <div class="stat-label">Hari Libur</div>
    </div>
  </div>
  <div class="card stat-card card-rose">
    <div class="stat-icon stat-icon-red"><?= App::icon('bolt', 'w-6 h-6') ?></div>
    <div>
      <div class="stat-value <?= (int)$settings['system_active'] === 1 ? 'text-success' : 'text-danger' ?>" id="adminStatusText">
        <?= (int)$settings['system_active'] === 1 ? 'ON' : 'OFF' ?>
      </div>
      <div class="stat-label">Status Sistem</div>
    </div>
  </div>
</div>

<div class="grid grid-3" style="margin-bottom:1.5rem;">
  <div class="card card-pad card-blue clock-card" style="grid-column:span 2;">
    <div class="clock-day" style="margin-bottom:0.5rem;" id="adminClockDay"></div>
    <div id="adminClock" style="font-size:clamp(4rem, 12vw, 7rem);font-weight:800;font-family:'Consolas','Courier New',monospace;color:var(--primary);letter-spacing:3px;text-shadow:0 0 40px rgba(52,211,153,0.3);line-height:1;">--:--:--</div>
    <div class="clock-date" style="margin-top:0.75rem;font-size:1rem;" id="adminDate"></div>
  </div>
  <div class="card card-pad card-emerald next-bell">
    <div class="section-title">Bel Berikutnya</div>
    <?php
        $adminNext = $today['nextBell'] ?? null;
        $adminHoliday = !empty($today['isHoliday'] ?? false);
        $adminNow = $today['currentTime'] ?? date('H:i');
        $adminCountdownText = '--:--:--';
        if (!$adminHoliday && $adminNext) {
            $tz = new DateTimeZone(($settings['timezone'] ?? 'Asia/Jakarta'));
            $nowDt = new DateTime('now', $tz);
            [$hh, $mm] = array_pad(explode(':', $adminNext['time']), 2, '00');
            $targetDt = new DateTime(sprintf('today %s:%s', $hh, $mm), $tz);
            if ($targetDt <= $nowDt) $targetDt->modify('+1 day');
            $diff = $nowDt->diff($targetDt);
            $adminCountdownText = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
        }
        if ($adminHoliday) {
            $adminNextTime = '--:--';
            $adminNextName = 'Hari Libur';
            $adminNextNote = '';
        } elseif ($adminNext) {
            $adminNextTime = $adminNext['time'];
            $adminNextName = $adminNext['name'];
            $adminNextNote = '';
        } elseif (!empty($today['schedules'])) {
            $adminNextTime = '--:--';
            $adminNextName = 'Sudah Pulang';
            $adminNextNote = 'Semua bel untuk hari ini telah selesai';
        } else {
            $adminNextTime = '--:--';
            $adminNextName = 'Tidak ada jadwal';
            $adminNextNote = 'Belum ada jadwal bel ditetapkan';
        }
    ?>
    <div class="next-bell-time" style="margin-top:0.75rem;">
      <div class="text-muted">Waktu</div>
      <div id="adminNextTime"><?= htmlspecialchars($adminNextTime) ?></div>
    </div>
    <div class="next-bell-name" style="margin-top:0.5rem;">
      <div class="text-muted">Nama Bel</div>
      <div id="adminNextName"><?= htmlspecialchars($adminNextName) ?></div>
      <?php if (!empty($adminNextNote)): ?>
        <div class="badge <?= $adminNextName === 'Sudah Pulang' ? 'badge-green' : 'badge-slate' ?>" id="adminNextNote" style="margin-top:0.4rem;"><?= htmlspecialchars($adminNextNote) ?></div>
      <?php else: ?>
        <div id="adminNextNote" style="display:none;"></div>
      <?php endif; ?>
    </div>
    <div class="countdown-wrap" style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border);">
      <div class="text-muted" style="font-size:0.75rem;margin-bottom:0.25rem;"><?= App::icon('clock', 'w-4 h-4 inline-block align-text-bottom') ?> Countdown</div>
      <div id="adminCountdown"><?= $adminCountdownText ?></div>
    </div>
  </div>
</div>

<div class="card card-pad card-emerald" style="margin-bottom:1.5rem;">
  <div class="section-title">Jadwal Hari Ini</div>
  <div id="adminJadwalList">
    <?php if ($adminHoliday): ?>
      <div class="empty-state">
        <div class="empty-icon"><?= App::icon('sun', 'w-8 h-8') ?></div>
        <div class="empty-title">Hari Libur</div>
        <div class="empty-text">Tidak ada jadwal bel hari ini.</div>
      </div>
    <?php elseif (empty($today['schedules'])): ?>
      <div class="empty-state">
        <div class="empty-icon"><?= App::icon('inbox', 'w-8 h-8') ?></div>
        <div class="empty-title">Tidak Ada Jadwal</div>
        <div class="empty-text">Belum ada jadwal bel untuk hari ini.</div>
      </div>
    <?php else: ?>
      <?php foreach ($today['schedules'] as $sch): $isPast = $sch['time'] < $adminNow; $isCur = $sch['time'] === $adminNow; ?>
        <div class="jadwal-item <?= $isCur ? 'current' : ($isPast ? 'past' : '') ?>" data-time="<?= htmlspecialchars($sch['time']) ?>">
          <div class="jadwal-time"><?= htmlspecialchars($sch['time']) ?></div>
          <div class="jadwal-info">
            <div class="jadwal-name"><?= htmlspecialchars($sch['name']) ?></div>
            <?php if (!empty($sch['bell_type_name'])): ?>
              <div class="jadwal-type"><?= htmlspecialchars($sch['bell_type_name']) ?></div>
            <?php endif; ?>
          </div>
          <?php if ($isCur): ?>
            <span class="badge badge-green" data-role="status">BERBUNYI</span>
          <?php elseif ($isPast): ?>
            <span class="badge badge-slate" data-role="status">Selesai</span>
          <?php else: ?>
            <span class="badge badge-blue" data-role="status">Menunggu</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div id="adminData" class="hidden" data-json='<?= htmlspecialchars(json_encode([
    'schedules_today' => $today['schedules'] ?? [],
    'system_active' => (int)$settings['system_active'],
    'bell_duration' => (int)$settings['bell_duration'],
    'default_volume' => (float)$settings['default_volume'],
    'is_holiday' => !empty($today['isHoliday'] ?? false),
])) ?>'></div>

<script src="<?= App::asset('js/admin-dashboard.js') ?>"></script>
