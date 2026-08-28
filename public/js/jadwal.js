(function () {
    'use strict';

    var editingId = null;
    var activeDayTab = 'Semua';
    var BASE_URL = window.__BASE_URL || '';

    if (!BASE_URL) {
        var meta = document.querySelector('meta[name="base-url"]');
        if (meta) {
            BASE_URL = meta.getAttribute('content');
        } else {
            BASE_URL = window.location.origin;
        }
    }

    var modal = document.getElementById('jadwalModal');
    var btnTambah = document.getElementById('btnTambah');
    var btnSave = document.getElementById('btnSave');
    var btnBatal = document.getElementById('btnBatal');
    var modalClose = document.getElementById('modalClose');
    var formError = document.getElementById('formError');
    var modalTitle = document.getElementById('modalTitle');

    var fDay = document.getElementById('f_day');
    var fTime = document.getElementById('f_time');
    var fName = document.getElementById('f_name');
    var fBellType = document.getElementById('f_bell_type_id');

    var editBtnAttr = 'data-action="edit"';

    function esc(v) {
        return window.App && App.esc ? App.esc(v) : String(v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function openModal(title) {
        formError.textContent = '';
        formError.hidden = true;
        modalTitle.textContent = title;
        modal.classList.add('open');
        modal.hidden = false;
        document.body.classList.add('modal-open');
    }

    function closeModal() {
        modal.classList.remove('open');
        modal.hidden = true;
        document.body.classList.remove('modal-open');
        editingId = null;
    }

    function resetForm() {
        fDay.value = '';
        fTime.value = '';
        fName.value = '';
        fBellType.value = '';
        formError.hidden = true;
    }

    function showError(msg) {
        formError.textContent = msg;
        formError.hidden = false;
        formError.style.display = 'block';
    }

    function filterByDay(day) {
        var cards = document.querySelectorAll('#jadwalGroups .card[data-group-day]');
        for (var i = 0; i < cards.length; i++) {
            cards[i].style.display = (day === 'Semua' || cards[i].getAttribute('data-group-day') === day) ? '' : 'none';
        }
    }

    var tabItems = document.querySelectorAll('#dayTabs .tab-item');
    for (var t = 0; t < tabItems.length; t++) {
        tabItems[t].addEventListener('click', function () {
            for (var j = 0; j < tabItems.length; j++) {
                tabItems[j].classList.remove('active');
            }
            this.classList.add('active');
            activeDayTab = this.getAttribute('data-day');
            filterByDay(activeDayTab);
        });
    }

    function handleRowButtons(row) {
        var id = row.getAttribute('data-id');
        var name = row.getAttribute('data-name');

        var toggleBtn = row.querySelector('[data-action="toggle"]');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', async function () {
                var current = toggleBtn.classList.contains('btn-toggle-on') ? 1 : 0;
                var next = current === 1 ? 0 : 1;
                var fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('is_active', String(next));
                var restore = window.App ? App.btnLoading(toggleBtn, '...') : function () {};
                try {
                    var res = await App.api(BASE_URL + '/admin/jadwal/' + id, { method: 'POST', body: fd });
                    if (!res.ok) {
                        throw new Error(res.data && res.data.error ? res.data.error : 'Gagal mengubah status.');
                    }
                    App.toast('Status jadwal diperbarui.', 'success');
                    setTimeout(function () { location.reload(); }, 600);
                } catch (e) {
                    restore();
                    App.toast(e.message || 'Terjadi kesalahan.', 'danger');
                }
            });
        }
    }

    function bindRowActions() {
        var rows = document.querySelectorAll('#jadwalGroups table tbody tr[data-id]');
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            if (row.getAttribute('data-bound') === '1') { continue; }
            row.setAttribute('data-bound', '1');

            handleRowButtons(row);

            var editBtn = row.querySelector('[data-action="edit"]');
            if (editBtn) {
                editBtn.addEventListener('click', function () {
                    editingId = row.getAttribute('data-id');
                    fDay.value = row.getAttribute('data-day');
                    fTime.value = row.getAttribute('data-time');
                    fName.value = row.getAttribute('data-name');
                    var bell = row.getAttribute('data-bell');
                    fBellType.value = bell ? bell.replace('#', '') : '';
                    openModal('Edit Jadwal');
                });
            }

            var delBtn = row.querySelector('[data-action="delete"]');
            if (delBtn) {
                delBtn.addEventListener('click', async function () {
                    var ok = await App.confirmDelete('Apakah Anda yakin ingin menghapus data ini?');
                    if (!ok) { return; }
                    var fd = new FormData();
                    fd.append('_method', 'DELETE');
                    var restore = window.App ? App.btnLoading(delBtn, '...') : function () {};
                    try {
                        var res = await App.api(BASE_URL + '/admin/jadwal/' + id, { method: 'POST', body: fd });
                        if (!res.ok) {
                            throw new Error(res.data && res.data.error ? res.data.error : 'Gagal menghapus jadwal.');
                        }
                        App.toast('Jadwal dihapus.', 'success');
                        setTimeout(function () { location.reload(); }, 600);
                    } catch (e) {
                        restore();
                        App.toast(e.message || 'Terjadi kesalahan.', 'danger');
                    }
                });
            }
        }
    }

    btnTambah.addEventListener('click', function () {
        editingId = null;
        resetForm();
        openModal('Tambah Jadwal Baru');
    });

    btnBatal.addEventListener('click', closeModal);
    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) { closeModal(); }
    });

    btnSave.addEventListener('click', async function () {
        formError.hidden = true;

        var day = fDay.value;
        var time = fTime.value;
        var name = fName.value.trim();
        var bellTypeId = fBellType.value;

        if (!day) { showError('Hari wajib dipilih.'); return; }
        if (!/^([01]\d|2[0-3]):[0-5]\d$/.test(time)) { showError('Format waktu tidak valid (HH:MM).'); return; }
        if (!name) { showError('Nama bel wajib diisi.'); return; }
        if (!bellTypeId) { showError('Jenis bel wajib dipilih.'); return; }

        var restore = window.App ? App.btnLoading(btnSave, 'Menyimpan...') : function () {};
        try {
            var url = BASE_URL + '/admin/jadwal';
            var res;
            if (editingId) {
                var fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('day', day);
                fd.append('time', time);
                fd.append('name', name);
                fd.append('bell_type_id', bellTypeId);
                res = await App.api(url + '/' + editingId, { method: 'POST', body: fd });
            } else {
                res = await App.api(url, {
                    method: 'POST',
                    body: JSON.stringify({ day: day, time: time, name: name, bell_type_id: bellTypeId }),
                    headers: { 'Content-Type': 'application/json' }
                });
            }

            if (!res.ok) {
                throw new Error(res.data && res.data.error ? res.data.error : 'Terjadi kesalahan saat menyimpan.');
            }

            App.toast(editingId ? 'Jadwal diperbarui.' : 'Jadwal ditambahkan.', 'success');
            closeModal();
            setTimeout(function () { location.reload(); }, 800);
        } catch (e) {
            restore();
            showError(e.message || 'Terjadi kesalahan.');
        }
    });

    bindRowActions();
})();
