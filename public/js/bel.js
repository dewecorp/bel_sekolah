(function () {
    'use strict';

    var editingId = null;
    var activeCategory = 'Semua';
    var BASE_URL = window.BASE_URL || '';

    if (!BASE_URL) {
        var meta = document.querySelector('meta[name="base-url"]');
        if (meta) {
            BASE_URL = meta.getAttribute('content');
        } else {
            BASE_URL = window.location.origin;
        }
    }

    var modal = document.getElementById('belModal');
    var btnTambah = document.getElementById('btnTambah');
    var btnSave = document.getElementById('btnSave');
    var btnBatal = document.getElementById('btnBatal');
    var modalClose = document.getElementById('modalClose');
    var formError = document.getElementById('formError');
    var modalTitle = document.getElementById('modalTitle');

    var fName = document.getElementById('f_name');
    var fCategory = document.getElementById('f_category');

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
        fName.value = '';
        fCategory.value = '';
        formError.hidden = true;
    }

    function showError(msg) {
        formError.textContent = msg;
        formError.hidden = false;
    }

    function filterByCategory(cat) {
        var cards = document.querySelectorAll('.bel-card[data-category]');
        for (var i = 0; i < cards.length; i++) {
            cards[i].style.display = (cat === 'Semua' || cards[i].getAttribute('data-category') === cat) ? '' : 'none';
        }
    }

    var tabItems = document.querySelectorAll('#categoryTabs .tab-item');
    for (var t = 0; t < tabItems.length; t++) {
        tabItems[t].addEventListener('click', function () {
            for (var j = 0; j < tabItems.length; j++) {
                tabItems[j].classList.remove('active');
            }
            this.classList.add('active');
            activeCategory = this.getAttribute('data-category');
            filterByCategory(activeCategory);
        });
    }

    btnTambah.addEventListener('click', function () {
        editingId = null;
        resetForm();
        openModal('Tambah Jenis Bel');
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
            fName.value = this.getAttribute('data-name');
            fCategory.value = this.getAttribute('data-category');
            openModal('Edit Jenis Bel');
        });
    }

    var deleteBtns = document.querySelectorAll('.btn-delete');
    for (var i = 0; i < deleteBtns.length; i++) {
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
                    var res = await App.api(BASE_URL + '/admin/bel/' + id, { method: 'POST', body: fd });
                    if (!res.ok) {
                        throw new Error(res.data && res.data.error ? res.data.error : 'Gagal menghapus jenis bel.');
                    }
                    App.toast('Jenis bel berhasil dihapus.', 'success');
                    setTimeout(function () { location.reload(); }, 600);
                } catch (e) {
                    restore();
                    App.toast(e.message || 'Terjadi kesalahan.', 'danger');
                }
            });
        })(deleteBtns[i]);
    }

    btnSave.addEventListener('click', async function () {
        formError.hidden = true;

        var name = fName.value.trim();
        var category = fCategory.value;

        if (!name) { showError('Nama jenis bel wajib diisi.'); return; }
        if (!category) { showError('Kategori wajib dipilih.'); return; }

        var restore = window.App ? App.btnLoading(btnSave, 'Menyimpan...') : function () {};
        try {
            var url = BASE_URL + '/admin/bel';
            var res;
            if (editingId) {
                var fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('name', name);
                fd.append('category', category);
                res = await App.api(url + '/' + editingId, { method: 'POST', body: fd });
            } else {
                res = await App.api(url, {
                    method: 'POST',
                    body: JSON.stringify({ name: name, category: category }),
                    headers: { 'Content-Type': 'application/json' }
                });
            }

            if (!res.ok) {
                throw new Error(res.data && res.data.error ? res.data.error : 'Terjadi kesalahan saat menyimpan.');
            }

            App.toast(editingId ? 'Jenis bel diperbarui.' : 'Jenis bel ditambahkan.', 'success');
            closeModal();
            setTimeout(function () { location.reload(); }, 800);
        } catch (e) {
            restore();
            showError(e.message || 'Terjadi kesalahan.');
        }
    });
})();
