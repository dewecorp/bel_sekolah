(() => {
  const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

  const BASE_URL = (typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/');
  const LS_BELL_KEY = 'bel_admin_last';

  const BOOT_EL = document.getElementById('adminData');
  const ADMIN_BOOT = BOOT_EL ? JSON.parse(BOOT_EL.dataset.json || '{}') : {};

  let SCHEDULES = Array.isArray(ADMIN_BOOT.schedules_today) ? ADMIN_BOOT.schedules_today : [];
  let SYSTEM_ACTIVE = ADMIN_BOOT.system_active === 1 ? 1 : 0;
  let BELL_DURATION = ADMIN_BOOT.bell_duration || 5;
  let DEFAULT_VOLUME = ADMIN_BOOT.default_volume || 0.8;
  let IS_HOLIDAY = !!ADMIN_BOOT.is_holiday;
  const TIMEZONE = window.TIMEZONE || 'Asia/Jakarta';
  let lastBellKey = null;
  let currentAudio = null;

  const esc = (str) => {
    const div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  };

  const todayKey = () => new Date().toDateString();

  const loadLastBellKey = () => {
    try {
      const stored = localStorage.getItem(LS_BELL_KEY);
      if (stored && stored.startsWith(todayKey() + '|')) {
        lastBellKey = stored;
      } else {
        lastBellKey = null;
      }
    } catch (e) {
      lastBellKey = null;
    }
  };

  const persistLastBellKey = (key) => {
    try {
      localStorage.setItem(LS_BELL_KEY, key);
    } catch (e) {}
    lastBellKey = key;
  };

  const playerUrl = (p) => {
    if (!p) return BASE_URL + '/storage/audio/bell-default.wav';
    return p.startsWith('http') ? p : BASE_URL + p;
  };

  const pad = (n) => String(n).padStart(2, '0');

  const formatTime = () => {
    if (App && typeof App.timeHMS === 'function') return App.timeHMS(TIMEZONE);
    const d = new Date();
    return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
  };

  const formatDateIndo = () => {
    if (App && typeof App.wallClock === 'function') {
      const c = App.wallClock(TIMEZONE);
      const cd = new Date(c.y, c.M - 1, c.d);
      return `${DAY_NAMES[cd.getDay()]}, ${c.d} ${MONTH_NAMES[c.M - 1]} ${c.y}`;
    }
    const d = new Date();
    return `${DAY_NAMES[d.getDay()]}, ${d.getDate()} ${MONTH_NAMES[d.getMonth()]} ${d.getFullYear()}`;
  };

  const getCurrentHM = () => {
    if (App && typeof App.timeHM === 'function') return App.timeHM(TIMEZONE);
    const d = new Date();
    return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
  };

  const tick = () => {
    const elClock = document.getElementById('adminClock');
    const elDate = document.getElementById('adminDate');
    const elDay = document.getElementById('adminClockDay');
    const now = new Date();

    if (elClock) elClock.textContent = formatTime();
    if (elDate) elDate.textContent = formatDateIndo();
    if (elDay) elDay.textContent = DAY_NAMES[now.getDay()];
  };

  const getNextBell = () => {
    const now = getCurrentHM();
    const todaySchedules = SCHEDULES.filter(s => s && s.time).sort((a, b) => a.time.localeCompare(b.time));

    if (todaySchedules.length === 0) return null;

    return todaySchedules.find(s => s.time > now) || null;
  };

  const updateNextBellDisplay = () => {
    const elTime = document.getElementById('adminNextTime');
    const elName = document.getElementById('adminNextName');
    const elCountdown = document.getElementById('adminCountdown');

    const next = IS_HOLIDAY ? null : getNextBell();

    if (!next) {
      let nameText = '';
      let noteText = '';
      if (IS_HOLIDAY) {
        nameText = 'Hari Libur';
      } else if (SCHEDULES.length > 0) {
        nameText = 'Sudah Pulang';
        noteText = 'Semua bel untuk hari ini telah selesai';
      } else {
        nameText = 'Tidak ada jadwal';
        noteText = 'Belum ada jadwal bel ditetapkan';
      }
      if (elTime) elTime.textContent = '--:--';
      if (elName) elName.textContent = nameText;
      if (elCountdown) elCountdown.textContent = '--:--:--';
      const noteEl = document.getElementById('adminNextNote');
      if (noteEl) noteEl.textContent = noteText;
      return;
    }

    if (elTime) elTime.textContent = next.time || '--:--';
    if (elName) elName.textContent = next.name || '--';

    if (elCountdown) {
      elCountdown.textContent = (App && typeof App.waktuTersisaTZ === 'function')
        ? App.waktuTersisaTZ(next.time, TIMEZONE).text
        : ((App && typeof App.waktuTersisa === 'function') ? App.waktuTersisa(next.time).text : '--:--:--');
    }
  };

  const renderJadwalList = () => {
    const el = document.getElementById('adminJadwalList');
    if (!el) return;

    const now = getCurrentHM();

    if (IS_HOLIDAY) {
      const sun = (window.App && App.Icon) ? App.Icon('sun', 'w-8 h-8') : '';
      el.innerHTML = `<div class="empty-state"><div class="empty-icon">${sun}</div><div class="empty-title">Hari Libur</div><div class="empty-text">Hari Libur — Tidak ada jadwal bel</div></div>`;
      return;
    }

    if (SCHEDULES.length === 0) {
      const inbox = (window.App && App.Icon) ? App.Icon('inbox', 'w-8 h-8') : '';
      el.innerHTML = `<div class="empty-state"><div class="empty-icon">${inbox}</div><div class="empty-title">Tidak Ada Jadwal</div><div class="empty-text">Belum ada jadwal bel untuk hari ini.</div></div>`;
      return;
    }

    el.innerHTML = SCHEDULES.map(s => {
      const t = s.time || '';
      let badge = 'badge-blue';
      let label = 'Menunggu';
      if (t < now) {
        badge = 'badge-slate';
        label = 'Selesai';
      } else if (t === now) {
        badge = 'badge-green';
        label = 'BERBUNYI';
      }

      const typeInfo = s.bell_type_name
        ? `<div class="jadwal-type">${esc(s.bell_type_name)}</div>`
        : '';

      return `<div class="jadwal-item ${t < now ? 'past' : (t === now ? 'current' : '')}" data-time="${esc(t)}">
        <div class="jadwal-time">${esc(t)}</div>
        <div class="jadwal-info">
          <div class="jadwal-name">${esc(s.name)}</div>
          ${typeInfo}
        </div>
        <span class="badge ${badge}" data-role="status">${label}</span>
      </div>`;
    }).join('');
  };

  const loadToday = async () => {
    try {
      const res = await fetch(BASE_URL + '/api/bell/today');
      if (!res.ok) return;

      const data = await res.json();

      if (Array.isArray(data.schedules)) {
        SCHEDULES = data.schedules;
      }
      if (typeof data.isHoliday !== 'undefined') {
        IS_HOLIDAY = !!data.isHoliday;
      }
      if (data.settings) {
        if (typeof data.settings.system_active !== 'undefined') {
          SYSTEM_ACTIVE = parseInt(data.settings.system_active, 10) === 1 ? 1 : 0;
        }
        if (typeof data.settings.bell_duration !== 'undefined') {
          BELL_DURATION = parseInt(data.settings.bell_duration, 10) || BELL_DURATION;
        }
        if (typeof data.settings.default_volume !== 'undefined') {
          DEFAULT_VOLUME = Number(data.settings.default_volume) || DEFAULT_VOLUME;
        }
      }

      loadLastBellKey();
      renderJadwalList();
      updateNextBellDisplay();
    } catch (e) {
      console.error('Admin loadToday error:', e);
    }
  };

  const setPlayingStatus = (label, cls) => {
    const el = document.getElementById('playingStatus') || document.getElementById('statStatus');
    if (!el) return;
    el.textContent = label;
    if (cls) {
      el.className = cls;
    }
  };

  const playSound = (filepath, volume, duration) => {
    return new Promise((resolve) => {
      if (currentAudio) {
        try { currentAudio.pause(); } catch (e) {}
        currentAudio = null;
      }

      const vol = volume != null && volume !== '' ? Number(volume) : DEFAULT_VOLUME;
      const dur = duration != null && duration !== '' ? Number(duration) : BELL_DURATION;

      const audio = new Audio(playerUrl(filepath));
      audio.volume = vol;
      currentAudio = audio;

      setPlayingStatus('BERBUNYI', 'animate-bell');

      audio.play().catch(e => console.error('Admin audio play error:', e));

      let cleaned = false;
      const cleanup = () => {
        if (cleaned) return;
        cleaned = true;
        if (currentAudio === audio) currentAudio = null;
        setPlayingStatus('SIAP', '');
        resolve();
      };

      audio.addEventListener('ended', cleanup);
      setTimeout(cleanup, dur * 1000);
    });
  };

  const checkBell = async () => {
    if (!SYSTEM_ACTIVE || IS_HOLIDAY) return;

    try {
      const res = await fetch(BASE_URL + '/api/bell/check');
      if (!res.ok) return;

      const data = await res.json();

      if (data.holiday === true) {
        IS_HOLIDAY = true;
        renderJadwalList();
        return;
      }

      if (data.active === false) {
        SYSTEM_ACTIVE = 0;
        return;
      }

      const schedules = data.schedules || [];
      if (!Array.isArray(schedules) || schedules.length === 0) return;

      const ids = schedules.map(s => s.id).sort().join('-');
      const bellKey = todayKey() + '|' + data.time + '|' + ids;
      if (bellKey === lastBellKey) return;

      persistLastBellKey(bellKey);

      for (const s of schedules) {
        const fp = s.filepath || '/storage/audio/bell-default.wav';
        const vol = s.volume != null ? s.volume : DEFAULT_VOLUME;
        const dur = s.duration != null ? s.duration : BELL_DURATION;

        await playSound(fp, vol, dur);

        try {
          await fetch(BASE_URL + '/api/bell/log', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ schedule_id: s.id, status: 'berhasil' })
          });
        } catch (logErr) {
          console.error('Admin bell log error:', logErr);
        }

        if (App && typeof App.toast === 'function') {
          App.toast('Bel: ' + esc(s.name), 'success');
        }
      }

      renderJadwalList();
    } catch (e) {
      console.error('Admin check bell error:', e);
    }
  };

  const triggerManual = async () => {
    const btn = document.getElementById('manualBellBtn');
    if (!btn) return;

    let audioSrc = null;
    let scheduleName = 'Manual';
    let playVolume = DEFAULT_VOLUME;
    let playDuration = BELL_DURATION;

    try {
      const res = await fetch(BASE_URL + '/api/bell/audio');
      if (res.ok) {
        const data = await res.json();
        const defaultAudio = data?.defaultAudio;
        const settings = data?.settings || {};

        if (defaultAudio) {
          audioSrc = defaultAudio.filepath;
          scheduleName = defaultAudio.name || 'Manual';
          playVolume = defaultAudio.volume || DEFAULT_VOLUME;
          playDuration = defaultAudio.duration || BELL_DURATION;
        } else {
          const first = Array.isArray(SCHEDULES) && SCHEDULES.length > 0 ? SCHEDULES[0] : null;
          if (first) {
            audioSrc = first.filepath || '/storage/audio/bell-default.wav';
            scheduleName = first.name || 'Manual';
            playVolume = first.volume || DEFAULT_VOLUME;
            playDuration = first.duration || BELL_DURATION;
          } else {
            audioSrc = '/storage/audio/bell-default.wav';
          }
        }
      } else {
        audioSrc = '/storage/audio/bell-default.wav';
      }
    } catch (e) {
      console.error('Admin manual audio fetch error:', e);
      audioSrc = '/storage/audio/bell-default.wav';
    }

    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Sedang Berbunyi...';

    try {
      await playSound(audioSrc, playVolume, playDuration);

      try {
        await fetch(BASE_URL + '/api/bell/manual', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ schedule_name: scheduleName, bell_type: 'Manual' })
        });
      } catch (logErr) {
        console.error('Admin manual log error:', logErr);
      }

      if (App && typeof App.toast === 'function') {
        App.toast('Bel Manual: ' + esc(scheduleName), 'success');
      }
    } catch (e) {
      console.error('Admin manual play error:', e);
      if (App && typeof App.toast === 'function') {
        App.toast('Gagal memutar bel', 'danger');
      }
    } finally {
      btn.disabled = false;
      btn.innerHTML = originalHtml;
    }
  };

  const init = () => {
    loadLastBellKey();
    tick();
    renderJadwalList();
    updateNextBellDisplay();

    loadToday();

    setInterval(tick, 1000);
    setInterval(checkBell, 5000);
    setInterval(loadToday, 60000);

    const btn = document.getElementById('manualBellBtn');
    if (btn) {
      btn.addEventListener('click', triggerManual);
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();