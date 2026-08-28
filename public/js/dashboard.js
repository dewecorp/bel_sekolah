(() => {
  const DAY_NAMES = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const MONTH_NAMES = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

  const BASE_URL = (typeof window.BASE_URL !== 'undefined' ? window.BASE_URL : '/');

  let lastBellKey = null;
  const LS_BELL_KEY = 'bel_sekolah_last_bell_key';
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
      lastBellKey = key;
    } catch (e) {
      lastBellKey = key;
    }
  };
  let SCHEDULES = window.SCHEDULES || [];
  let SYSTEM_ACTIVE = window.SYSTEM_ACTIVE || 1;
  let BELL_DURATION = window.BELL_DURATION || 5;
  let DEFAULT_VOLUME = window.DEFAULT_VOLUME || 0.8;
  let IS_HOLIDAY = window.IS_HOLIDAY || false;
  const TIMEZONE = window.TIMEZONE || 'Asia/Jakarta';

  const playerUrl = (p) => {
    if (!p) return BASE_URL + '/storage/audio/bell-default.wav';
    return p.startsWith('http') ? p : BASE_URL + p;
  };

  const formatTime = () => {
    if (App && typeof App.timeHMS === 'function') return App.timeHMS(TIMEZONE);
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
  };

  const dateIndoInTZ = () => {
    const d = new Date();
    const day = DAY_NAMES[d.getDay()];
    const date = d.getDate();
    const month = MONTH_NAMES[d.getMonth()];
    const year = d.getFullYear();
    if (App && typeof App.wallClock === 'function') {
      const c = App.wallClock(TIMEZONE);
      const cDate = new Date(c.y, c.M - 1, c.d);
      const cDay = DAY_NAMES[cDate.getDay()];
      return `${cDay}, ${c.d} ${MONTH_NAMES[c.M - 1]} ${c.y}`;
    }
    return `${day}, ${date} ${month} ${year}`;
  };

  const getCurrentHM = () => {
    if (App && typeof App.timeHM === 'function') return App.timeHM(TIMEZONE);
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
  };

  const updateClock = () => {
    const d = new Date();
    const elTime = document.getElementById('jamDigital');
    const elDate = document.getElementById('tanggalHariIni');
    const elDay = document.getElementById('hariIni');

    if (elTime) elTime.textContent = formatTime();
    if (elDate) elDate.textContent = dateIndoInTZ();
    if (elDay) elDay.textContent = DAY_NAMES[d.getDay()];
  };

  const getNextBell = () => {
    const now = getCurrentHM();
    const todaySchedules = SCHEDULES.filter(s => s && s.time).sort((a, b) => a.time.localeCompare(b.time));

    if (todaySchedules.length === 0) {
      return null;
    }

    return todaySchedules.find(s => s.time > now) || null;
  };

  const updateNextBellDisplay = () => {
    const elTime = document.getElementById('nextBellTime');
    const elName = document.getElementById('nextBellName');
    const elType = document.getElementById('nextBellType');
    const elCountdown = document.getElementById('countdown');

    const next = getNextBell();

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
      if (elType) elType.textContent = ''; // catatan tidak ditampilkan di sini (gunakan badge)
      if (elCountdown) elCountdown.textContent = '--:--:--';
      const noteBadge = document.getElementById('nextBellNote');
      if (noteBadge) {
        noteBadge.textContent = noteText;
        noteBadge.style.display = noteText ? 'inline-block' : 'none';
      }
      return;
    }

    if (elTime) elTime.textContent = next.time || '--:--';
    if (elName) elName.textContent = next.name || '--';
    if (elType) elType.textContent = next.bell_type_name || '';
    const nbBadge = document.getElementById('nextBellNote');
    if (nbBadge) nbBadge.style.display = 'none';

    if (App && typeof App.waktuTersisaTZ === 'function') {
      if (elCountdown) {
        const cd = App.waktuTersisaTZ(next.time, TIMEZONE);
        elCountdown.textContent = cd && cd.text ? cd.text : '--:--:--';
      }
    } else if (App && typeof App.waktuTersisa === 'function') {
      if (elCountdown) {
        const cd = App.waktuTersisa(next.time);
        elCountdown.textContent = cd && cd.text ? cd.text : '--:--:--';
      }
    } else {
      if (elCountdown) elCountdown.textContent = '--:--:--';
    }
  };

  const updateJadwalStatus = () => {
    const now = getCurrentHM();
    const items = document.querySelectorAll('.jadwal-item');
    let futureCount = 0;

    items.forEach(item => {
      const time = item.getAttribute('data-time');
      if (!time) return;

      const badge = item.querySelector('[data-role="status"]');
      if (!badge) return;

      item.classList.remove('past', 'current');
      badge.classList.remove('badge-green', 'badge-blue');

      if (time < now) {
        item.classList.add('past');
        badge.classList.add('badge-slate');
        badge.textContent = 'Selesai';
      } else if (time === now) {
        item.classList.add('current');
        badge.classList.add('badge-green');
        badge.textContent = 'BERBUNYI';
      } else {
        badge.classList.add('badge-blue');
        badge.textContent = 'Menunggu';
        futureCount++;
      }
    });

    const elStatSisa = document.getElementById('statSisa');
    if (elStatSisa) elStatSisa.textContent = futureCount;
  };

  const tick = () => {
    try {
      updateClock();
      updateNextBellDisplay();
      updateJadwalStatus();
    } catch (e) {
      console.error('Dashboard tick error:', e);
    }
  };

  const playAudio = (src, volume = DEFAULT_VOLUME) => {
    return new Promise((resolve) => {
      const audio = new Audio(playerUrl(src));
      audio.volume = volume;
      audio.play().catch(e => console.error('Audio play error:', e));
      audio.addEventListener('ended', () => resolve());
      setTimeout(() => resolve(), BELL_DURATION * 1000);
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
        return;
      }

      if (data.active === false) {
        SYSTEM_ACTIVE = 0;
        return;
      }

      const schedules = data.schedules || [];
      if (!Array.isArray(schedules) || schedules.length === 0) return;

      const ids = schedules.map(s => s.id).sort().join('-');
      const bellKey = todayKey() + '|' + (schedules[0]?.time || '') + '-' + ids;
      if (bellKey === lastBellKey) return;

      persistLastBellKey(bellKey);

      for (const s of schedules) {
        const fp = s.filepath || '/storage/audio/bell-default.wav';
        const volume = s.volume || DEFAULT_VOLUME;

        await playAudio(fp, volume);

        try {
          await fetch(BASE_URL + '/api/bell/log', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ schedule_id: s.id, status: 'berhasil' })
          });
        } catch (logErr) {
          console.error('Bell log error:', logErr);
        }

        const elStatus = document.getElementById('statStatus');
        if (elStatus) {
          elStatus.textContent = 'BERBUNYI';
          elStatus.classList.add('animate-bell');
          setTimeout(() => {
            elStatus.textContent = 'SIAP';
            elStatus.classList.remove('animate-bell');
          }, BELL_DURATION * 1000);
        }

        if (App && typeof App.toast === 'function') {
          App.toast('Bel: ' + s.name, 'success');
        }

        updateJadwalStatus();
      }
    } catch (e) {
      console.error('Check bell error:', e);
    }
  };

  const triggerManual = async () => {
    const btn = document.getElementById('manualBellBtn');
    if (!btn) return;

    let audioSrc = null;
    let scheduleName = 'Manual';
    let playVolume = DEFAULT_VOLUME;

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
        } else {
          const first = Array.isArray(SCHEDULES) && SCHEDULES.length > 0 ? SCHEDULES[0] : null;
          if (first) {
            audioSrc = first.filepath || '/storage/audio/bell-default.wav';
            scheduleName = first.name || 'Manual';
            playVolume = first.volume || DEFAULT_VOLUME;
          } else {
            audioSrc = '/storage/audio/bell-default.wav';
          }
        }
      } else {
        audioSrc = '/storage/audio/bell-default.wav';
      }
    } catch (e) {
      console.error('Manual audio fetch error:', e);
      audioSrc = '/storage/audio/bell-default.wav';
    }

    btn.disabled = true;
    btn.innerHTML = '<span>Sedang Berbunyi...</span>';

    try {
      await playAudio(audioSrc, playVolume);

      try {
        await fetch(BASE_URL + '/api/bell/manual', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ schedule_name: scheduleName, bell_type: 'Manual' })
        });
      } catch (logErr) {
        console.error('Manual log error:', logErr);
      }

      if (App && typeof App.toast === 'function') {
        App.toast('Bel berhasil dibunyikan', 'success');
      }
    } catch (e) {
      console.error('Manual play error:', e);
      if (App && typeof App.toast === 'function') {
        App.toast('Gagal memutar bel', 'danger');
      }
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<span class="animate-bell"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 svg-icon" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg></span> Bunyikan Bel Sekarang';
    }
  };

  const getTodayData = async () => {
    try {
      const res = await fetch(BASE_URL + '/api/bell/today');
      if (!res.ok) return;

      const data = await res.json();

      if (Array.isArray(data.schedules)) {
        SCHEDULES = data.schedules;
      }
      if (typeof data.isHoliday !== 'undefined') {
        IS_HOLIDAY = data.isHoliday;
      }
      if (data.settings) {
        if (typeof data.settings.system_active !== 'undefined') {
          SYSTEM_ACTIVE = (intVal(data.settings.system_active) === 1) ? 1 : 0;
        }
        if (typeof data.settings.bell_duration !== 'undefined') {
          BELL_DURATION = intVal(data.settings.bell_duration);
        }
        if (typeof data.settings.default_volume !== 'undefined') {
          DEFAULT_VOLUME = Number(data.settings.default_volume) || DEFAULT_VOLUME;
        }
      }

      loadLastBellKey();

      updateNextBellDisplay();
      updateJadwalStatus();
    } catch (e) {
      console.error('getTodayData error:', e);
    }
  };

  const intVal = (v) => {
    const n = parseInt(v, 10);
    return Number.isFinite(n) ? n : 0;
  };

  const init = () => {
    loadLastBellKey();
    updateClock();
    updateNextBellDisplay();
    updateJadwalStatus();

    setInterval(tick, 1000);
    setInterval(checkBell, 5000);
    setInterval(getTodayData, 60000);

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
