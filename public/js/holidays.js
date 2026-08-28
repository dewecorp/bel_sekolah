(function () {
    'use strict';

    var editingId = null;
    var BASE_URL = window.BASE_URL || '';

    if (!BASE_URL) {
        var meta = document.querySelector('meta[name="base-url"]');
        if (meta) {
            BASE_URL = meta.getAttribute('content');
        } else {
            BASE_URL = window.location.origin;
        }
    }

    var modal = document.getElementById('liburModal');
    var btnTambah = document.getElementById('btnTambah');
    var btnSave = document.getElementById('btnSave');
    var btnBatal = document.getElementById('btnBatal');
    var modalClose = document.getElementById('modalClose');
    var formError = document.getElementById('formError');
    var modalTitle = document.getElementById('modalTitle');

    var fDate = document.getElementById('f_date');
    var fName = document.getElementById('f_name');
    var fDescription = document.getElementById('f_description');

    function openModal(title) {
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
        fDate.value = '';
        fName.value = '';
        fDescription.value = '';
        formError.hidden = true;
    }

    function showError(msg) {
        formError.textContent = msg;
        formError.hidden = false;
    }

    btnTambah.addEventListener('click', function () {
        editingId = null;
        resetForm();
        openModal('Tambah Hari Libur');
    });

    btnBatal.addEventListener('click', closeModal);
    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) { closeModal(); }
    });

    var editBtns = document.querySelectorAll('.btn-edit');
    for (var i = 0; i < editBtns.length; i++) {
        editBtns[i].addEventListener('click', function () {
            editingId = this.getAttribute('data-id');
            fDate.value = this.getAttribute('data-date');
            fName.value = this.getAttribute('data-name');
            fDescription.value = this.getAttribute('data-description');
            openModal('Edit Hari Libur');
        });
    }

    var deleteBtns = document.querySelectorAll('.btn-delete');
    for (var d = 0; d < deleteBtns.length; d++) {
        (function (btn) {
            btn.addEventListener('click', async function () {
                var id = btn.getAttribute('data-id');
                var name = btn.getAttribute('data-name');
                var ok = await App.confirmDelete('Apakah Anda yakin ingin menghapus data ini?');
                if (!ok) { return; }
                var fd = new FormData();
                fd.append('_method', 'DELETE');
                var restore = window.App ? App.btnLoading(btn, '...') : function () {};
                try {
                    var res = await App.api(BASE_URL + '/admin/libur/' + id, { method: 'POST', body: fd });
                    if (!res.ok) {
                        throw new Error(res.data && res.data.error ? res.data.error : 'Gagal menghapus hari libur.');
                    }
                    App.toast('Hari libur berhasil dihapus.', 'success');
                    setTimeout(function () { location.reload(); }, 600);
                } catch (e) {
                    restore();
                    App.toast(e.message || 'Terjadi kesalahan.', 'danger');
                }
            });
        })(deleteBtns[d]);
    }

    btnSave.addEventListener('click', async function () {
        formError.hidden = true;

        var date = fDate.value.trim();
        var name = fName.value.trim();
        var description = fDescription.value.trim();

        if (!date) { showError('Tanggal wajib diisi.'); return; }
        if (!name) { showError('Nama hari libur wajib diisi.'); return; }

        var restore = window.App ? App.btnLoading(btnSave, 'Menyimpan...') : function () {};
        try {
            var url = BASE_URL + '/admin/libur';
            var res;
            if (editingId) {
                var fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('date', date);
                fd.append('name', name);
                fd.append('description', description);
                res = await App.api(url + '/' + editingId, { method: 'POST', body: fd });
            } else {
                res = await App.api(url, {
                    method: 'POST',
                    body: JSON.stringify({ date: date, name: name, description: description }),
                    headers: { 'Content-Type': 'application/json' }
                });
            }

            if (!res.ok) {
                throw new Error(res.data && res.data.error ? res.data.error : 'Terjadi kesalahan saat menyimpan.');
            }

            App.toast(editingId ? 'Hari libur diperbarui.' : 'Hari libur ditambahkan.', 'success');
            closeModal();
            setTimeout(function () { location.reload(); }, 800);
        } catch (e) {
            restore();
            showError(e.message || 'Terjadi kesalahan.');
        }
    });
})();
