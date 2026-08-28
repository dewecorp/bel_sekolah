(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '';

    if (!BASE_URL) {
        var meta = document.querySelector('meta[name="base-url"]');
        if (meta) {
            BASE_URL = meta.getAttribute('content');
        } else {
            BASE_URL = window.location.origin;
        }
    }

    var filterDate = document.getElementById('filterDate');
    var btnTerapkan = document.getElementById('btnTerapkan');

    function applyFilter() {
        var val = filterDate ? filterDate.value : '';
        var url = BASE_URL + '/admin/riwayat' + (val ? '?date=' + val : '');
        window.location.href = url;
    }

    if (btnTerapkan) {
        btnTerapkan.addEventListener('click', applyFilter);
    }
    if (filterDate) {
        filterDate.addEventListener('change', applyFilter);
    }

    // Hapus satu riwayat
    var deleteBtns = document.querySelectorAll('.btn-delete');
    for (var d = 0; d < deleteBtns.length; d++) {
        (function (btn) {
            btn.addEventListener('click', async function () {
                var id = btn.getAttribute('data-id');
                var scheduleName = btn.getAttribute('data-name') || '';
                var ok = await App.confirmDelete('Hapus riwayat "' + scheduleName + '"?\nData tidak dapat dikembalikan.');
                if (!ok) { return; }
                var fd = new FormData();
                fd.append('_method', 'DELETE');
                var restore = window.App ? App.btnLoading(btn, '...') : function () {};
                try {
                    var res = await App.api(BASE_URL + '/admin/riwayat/' + id, { method: 'POST', body: fd });
                    if (!res.ok) {
                        throw new Error(res.data && res.data.error ? res.data.error : 'Gagal menghapus riwayat.');
                    }
                    App.toast('Riwayat dihapus.', 'success');
                    setTimeout(function () { location.reload(); }, 600);
                } catch (e) {
                    restore();
                    App.toast(e.message || 'Terjadi kesalahan.', 'danger');
                }
            });
        })(deleteBtns[d]);
    }

    // Hapus semua riwayat
    var clearAll = document.getElementById('clearAll');
    if (clearAll) {
        clearAll.addEventListener('click', async function () {
            var ok = await App.confirmDelete('Hapus SEMUA riwayat?\nSeluruh data histori bel akan dihapus permanen.');
            if (!ok) { return; }
            var fd = new FormData();
            fd.append('date', clearAll.getAttribute('data-date') || '');
            var restore = window.App ? App.btnLoading(clearAll, '...') : function () {};
            try {
                var res = await App.api(BASE_URL + '/admin/riwayat/clear', { method: 'POST', body: fd });
                if (!res.ok) {
                    throw new Error(res.data && res.data.error ? res.data.error : 'Gagal menghapus riwayat.');
                }
                App.toast('Semua riwayat berhasil dihapus.', 'success');
                setTimeout(function () { location.reload(); }, 600);
            } catch (e) {
                restore();
                App.toast(e.message || 'Terjadi kesalahan.', 'danger');
            }
        });
    }
})();