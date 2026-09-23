/* ============================================
   Bel Sekolah Digital - Helper Utama (app.js)
   ============================================ */

const App = (() => {
    // ----- Ikon SVG (Heroicons outline) untuk toast -----
    const IC = (d, extra = '') =>
        `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 svg-icon" aria-hidden="true">${extra}<path stroke-linecap="round" stroke-linejoin="round" d="${d}"/></svg>`;

    const TOAST_ICONS = {
        success: IC('M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'),
        danger: IC('M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z'),
        warning: IC('M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'),
        info: IC('M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z'),
        bell: IC('M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'),
        sun: IC('M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z'),
        moon: IC('M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z'),
    };

    const Icon = (name, cls = 'w-5 h-5') => TOAST_ICONS[name] || TOAST_ICONS.info;

    // ----- Toast notification -----
    function toast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const el = document.createElement('div');
        el.className = `toast toast-${type}`;
        const icon = TOAST_ICONS[type] || TOAST_ICONS.info;
        el.innerHTML = `<span class="toast-icon">${icon}</span><span>${message}</span>`;
        container.appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => {
            el.classList.remove('show');
            setTimeout(() => el.remove(), 300);
        }, 4000);
    }

    function showAlert(target, message, type = 'success') {
        const el = document.createElement('div');
        el.className = `alert alert-${type} animate-slide-in`;
        el.innerHTML = `<span>${message}</span><button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>`;
        target.innerHTML = '';
        target.appendChild(el);
    }

    // ----- Fetch JSON helper -----
    async function api(url, options = {}) {
        const headers = { ...(options.headers || {}) };
        if (options.body && !(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            if (options.body) options.body = JSON.stringify(options.body);
        }
        const res = await fetch(url, { ...options, headers });
        let data = null;
        try { data = await res.json(); } catch (e) { data = null; }
        return { ok: res.ok, status: res.status, data };
    }

    // ----- Local time helpers (zona waktu lokal browser utk display, server utk truth) -----
    function formatTime(date = new Date()) {
        return date.toTimeString().slice(0, 5);
    }

    function formatTimeFull(date = new Date()) {
        return date.toTimeString().slice(0, 8);
    }

    function waktuTersisa(targetTime) {
        // targetTime HH:MM, relatif thd sekarang lokal
        const now = new Date();
        const [h, m] = targetTime.split(':').map(Number);
        let target = new Date(now);
        target.setHours(h, m, 0, 0);
        if (target <= now) target.setDate(target.getDate() + 1);
        let diff = target - now;
        const hours = Math.floor(diff / 3600000);
        const minutes = Math.floor((diff % 3600000) / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);
        return {
            text: `${String(hours).padStart(2,'0')}:${String(minutes).padStart(2,'0')}:${String(seconds).padStart(2,'0')}`,
            seconds: Math.floor(diff / 1000),
        };
    }

    // ----- Jam di zona waktu tertentu (default Asia/Jakarta) -----
    function wallClock(timeZone) {
        const fmt = new Intl.DateTimeFormat('en-GB', {
            timeZone: timeZone || 'Asia/Jakarta',
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            hour12: false,
        });
        const parts = fmt.formatToParts(new Date());
        const get = (t) => {
            const p = parts.find(x => x.type === t);
            return p ? parseInt(p.value, 10) : 0;
        };
        return { y: get('year'), M: get('month'), d: get('day'), h: get('hour'), m: get('minute'), s: get('second'), epoch: Date.now() };
    }

    function pad2(n) { return String(n).padStart(2, '0'); }

    function timeHM(timeZone) {
        const c = wallClock(timeZone);
        return `${pad2(c.h)}:${pad2(c.m)}`;
    }

    function timeHMS(timeZone) {
        const c = wallClock(timeZone);
        return `${pad2(c.h)}:${pad2(c.m)}:${pad2(c.s)}`;
    }

    // Countdown ke targetTime HH:MM di zona waktu tertentu
    function waktuTersisaTZ(targetTime, timeZone) {
        const tz = timeZone || 'Asia/Jakarta';
        const c = wallClock(tz);
        const hh = c.h, mm = c.m, ss = c.s;
        const [th, tm] = (targetTime || '00:00').split(':').map(Number);
        const nowWallAsUTC = Date.UTC(c.y, c.M - 1, c.d, hh, mm, ss);
        const offset = nowWallAsUTC - c.epoch; // offset zona (ms)
        const targetWallAsUTC = Date.UTC(c.y, c.M - 1, c.d, th, tm, 0);
        let targetEpoch = targetWallAsUTC - offset;
        if (targetEpoch <= c.epoch) targetEpoch += 86400000;
        let diff = Math.max(0, Math.floor((targetEpoch - c.epoch) / 1000));
        const s = diff % 60, m = Math.floor(diff / 60) % 60, h = Math.floor(diff / 3600);
        return { text: `${pad2(h)}:${pad2(m)}:${pad2(s)}`, seconds: diff };
    }

    // ----- Confirm dialog (SweetAlert2, fallback native) -----
    function makeConfirm(resolveFn) {
        return function(icon, title, text) {
            if (window.Swal) {
                Swal.fire({
                    title: title || 'Konfirmasi',
                    text: text || '',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(resolveFn);
            } else {
                resolveFn({ isConfirmed: window.confirm(text || 'Apakah Anda yakin?') });
            }
        };
    }

    function confirm(icon, title, text) {
        return new Promise(makeConfirm(r => r.isConfirmed));
    }

    function confirmDelete(text) {
        return new Promise((resolve) => {
            if (window.Swal) {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: text || 'Apakah Anda yakin?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then(r => resolve(r.isConfirmed));
            } else {
                resolve(window.confirm(text || 'Apakah Anda yakin?'));
            }
        });
    }

    // ----- Escape HTML -----
    function esc(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    // ----- Loading state on button -----
    function btnLoading(btn, text = 'Menyimpan...') {
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = text;
        return () => {
            btn.disabled = false;
            btn.innerHTML = original;
        };
    }

    // ----- Delete item with confirmation -----
    async function deleteItem(url, successMsg, refreshFn) {
        const yes = await confirm('🗑️', 'Hapus Data?', 'Data yang dihapus tidak dapat dikembalikan.');
        if (!yes) return;
        const { ok, data } = await api(url, { method: 'DELETE' });
        if (ok) {
            toast(successMsg, 'success');
            if (refreshFn) refreshFn();
        } else {
            toast(data?.error || 'Gagal menghapus data', 'danger');
        }
    }

    // ----- Dark / Light Theme Toggle -----
    function getPreferredTheme() {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark' || saved === 'light') return saved;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function updateThemeToggleIcons() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || getPreferredTheme();
        const isDark = currentTheme === 'dark';
        const btns = document.querySelectorAll('.theme-toggle-btn');
        btns.forEach(btn => {
            btn.innerHTML = isDark ? TOAST_ICONS.sun : TOAST_ICONS.moon;
            btn.title = isDark ? 'Ubah ke Mode Terang' : 'Ubah ke Mode Gelap';
        });
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || getPreferredTheme();
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateThemeToggleIcons();
    }

    function initTheme() {
        const theme = getPreferredTheme();
        document.documentElement.setAttribute('data-theme', theme);
        updateThemeToggleIcons();
    }

    // Event delegation for theme toggle buttons (single path, no inline onclick)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.theme-toggle-btn');
        if (btn) {
            e.preventDefault();
            toggleTheme();
            return;
        }
        // Close floating select panel on outside click
        if (!e.target.closest('.cs-wrap') && !e.target.closest('.cs-float')) {
            csCloseFloat();
        }
    });

    // Close floating select panel on outside scroll/resize (ignore scroll inside panel)
    window.addEventListener('scroll', (e) => {
        if (csFloatPanel && e.target) {
            if (e.target === csFloatPanel) return;
            try {
                if (csFloatPanel.contains(e.target)) return;
                if (e.target.contains && e.target.contains(csFloatPanel)) return;
            } catch (err) {}
        }
        csCloseFloat();
    }, true);
    window.addEventListener('resize', () => csCloseFloat());

    // ----- Custom Select (rounded panel, observer-free, anti-freeze) -----
    const CS_CHEV = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';

    function csLabelText(select) {
        const idx = select.selectedIndex;
        if (idx >= 0 && select.options[idx]) return select.options[idx].textContent;
        return '— Pilih —';
    }

    function refreshCustomSelects(root = document) {
        const wraps = [];
        if (root.classList && root.classList.contains('cs-wrap')) wraps.push(root);
        root.querySelectorAll('.cs-wrap').forEach(w => wraps.push(w));
        wraps.forEach(wrap => {
            const select = wrap.querySelector('select');
            const label = wrap.querySelector('.cs-label');
            if (!select || !label) return;
            label.textContent = csLabelText(select);
            const panel = (csFloatWrap === wrap && csFloatPanel) ? csFloatPanel : wrap.querySelector('.cs-panel');
            if (panel) panel.querySelectorAll('.cs-opt').forEach(o => {
                o.classList.toggle('sel', Number(o.dataset.idx) === select.selectedIndex);
            });
        });
    }

    let csFloatPanel = null;
    let csFloatWrap = null;

    function csCloseFloat() {
        if (csFloatPanel) { try { csFloatPanel.remove(); } catch (e) {} csFloatPanel = null; }
        if (csFloatWrap) { csFloatWrap.classList.remove('open'); csFloatWrap = null; }
    }

    function csOpenFloat(wrap, select, panel) {
        csCloseFloat();
        const r = wrap.querySelector('.cs-trigger').getBoundingClientRect();
        panel.querySelectorAll('.cs-opt').forEach(o => {
            o.classList.toggle('sel', Number(o.dataset.idx) === select.selectedIndex);
        });
        csFloatPanel = panel;
        csFloatWrap = wrap;
        panel.classList.add('cs-float');
        panel.style.minWidth = Math.max(r.width, 180) + 'px';
        panel.style.width = Math.max(r.width, 180) + 'px';
        const place = () => {
            if (!csFloatPanel) return;
            const rr = wrap.querySelector('.cs-trigger').getBoundingClientRect();
            const h = Math.min(230, panel.scrollHeight || 230);
            let top = rr.bottom + 6;
            if (top + h > window.innerHeight - 8) top = Math.max(8, rr.top - h - 6);
            csFloatPanel.style.top = top + 'px';
            csFloatPanel.style.left = Math.max(8, Math.min(rr.left, window.innerWidth - Math.max(rr.width, 180) - 8)) + 'px';
        };
        panel.style.display = 'block';
        document.body.appendChild(panel);
        place();
        panel.dataset.csPlace = '1';
        wrap.classList.add('open');
        const sel = panel.querySelector('.cs-opt.sel');
        if (sel && sel.scrollIntoView) { try { sel.scrollIntoView({ block: 'nearest' }); } catch (e) {} }
    }

    function buildCustomSelect(select) {
        if (select.dataset.csInit === '1') return;
        select.dataset.csInit = '1';

        const wrap = document.createElement('div');
        wrap.className = 'cs-wrap';

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'cs-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');

        const label = document.createElement('span');
        label.className = 'cs-label';
        label.textContent = csLabelText(select);

        const chev = document.createElement('span');
        chev.className = 'cs-chev';
        chev.innerHTML = CS_CHEV;

        trigger.appendChild(label);
        trigger.appendChild(chev);

        const panel = document.createElement('div');
        panel.className = 'cs-panel';
        panel.setAttribute('role', 'listbox');

        const renderPanel = () => {
            panel.innerHTML = '';
            Array.from(select.options).forEach((opt, idx) => {
                const o = document.createElement('div');
                o.className = 'cs-opt' + (idx === select.selectedIndex ? ' sel' : '');
                if (opt.disabled) o.classList.add('dis');
                o.textContent = opt.textContent;
                o.dataset.idx = String(idx);
                o.setAttribute('role', 'option');
                o.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    ev.preventDefault();
                    if (opt.disabled) return;
                    select.selectedIndex = idx;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    refreshCustomSelects(wrap);
                    csCloseFloat();
                });
                panel.appendChild(o);
            });
        };
        renderPanel();

        trigger.addEventListener('click', (ev) => {
            ev.stopPropagation();
            ev.preventDefault();
            if (csFloatWrap === wrap && csFloatPanel) { csCloseFloat(); return; }
            refreshCustomSelects(wrap);
            renderPanel();
            refreshCustomSelects(wrap);
            csOpenFloat(wrap, select, panel);
        });

        trigger.addEventListener('keydown', (ev) => {
            if (ev.key === 'ArrowDown' || ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                trigger.click();
            }
        });

        select.addEventListener('change', () => refreshCustomSelects(wrap));

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(trigger);
        wrap.appendChild(select);
        select.style.display = 'none';
    }

    function initCustomSelects(root = document) {
        root.querySelectorAll('select.form-select').forEach(buildCustomSelect);
        refreshCustomSelects(root);
    }

    // Hook: sinkronkan label custom saat skrip halaman set .value langsung
    function syncCustomSelect(select) {
        if (!select) return;
        const wrap = select.closest ? select.closest('.cs-wrap') : null;
        if (wrap) refreshCustomSelects(wrap);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => { initTheme(); initCustomSelects(); });
    } else {
        initTheme();
        initCustomSelects();
    }

    // Global ESC key handler for modals + custom select
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            csCloseFloat();
            document.querySelectorAll('.modal-backdrop').forEach(mb => {
                mb.classList.remove('open');
                mb.hidden = true;
            });
            document.body.classList.remove('modal-open');
        }
    });

    return {
        toast, showAlert, api, formatTime, formatTimeFull,
        waktuTersisa, waktuTersisaTZ, timeHM, timeHMS, wallClock,
        confirm, confirmDelete, esc, btnLoading, deleteItem, Icon,
        TOAST_ICONS, IC, toggleTheme, initTheme, syncCustomSelect, initCustomSelects,
    };
})();

window.App = App;