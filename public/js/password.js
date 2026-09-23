(function () {
    'use strict';

    var BASE_URL = window.BASE_URL || '';
    if (!BASE_URL) {
        var meta = document.querySelector('meta[name="base-url"]');
        BASE_URL = meta ? meta.getAttribute('content') : window.location.origin;
    }

    var alertContainer = document.getElementById('alertContainer');
    var btnSave = document.getElementById('btnSavePass');
    var fOld = document.getElementById('f_old_pass');
    var fNew = document.getElementById('f_new_pass');
    var fConfirm = document.getElementById('f_confirm_pass');

    function showAlert(msg, type) {
        if (window.App) {
            App.showAlert(alertContainer, msg, type);
        } else {
            alertContainer.innerHTML = '<div class="alert alert-' + type + '"><span>' + msg + '</span></div>';
        }
    }

    var form = document.getElementById('passwordForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            btnSave.click();
        });
    }

    btnSave.addEventListener('click', async function () {
        var oldPass = fOld.value;
        var newPass = fNew.value;
        var confirmPass = fConfirm.value;

        if (!oldPass) { showAlert('Password lama wajib diisi.', 'danger'); return; }
        if (!newPass || newPass.length < 6) { showAlert('Password baru minimal 6 karakter.', 'danger'); return; }
        if (newPass !== confirmPass) { showAlert('Konfirmasi password tidak cocok.', 'danger'); return; }

        var restore = window.App ? App.btnLoading(btnSave, 'Menyimpan...') : function () {};
        try {
            var res = await App.api(BASE_URL + '/admin/password', {
                method: 'POST',
                body: JSON.stringify({ old_password: oldPass, new_password: newPass, confirm_password: confirmPass })
            });
            if (!res.ok) {
                throw new Error(res.data && res.data.error ? res.data.error : 'Gagal mengubah password.');
            }
            App.toast('Password berhasil diubah.', 'success');
            showAlert('Password berhasil diubah.', 'success');
            fOld.value = ''; fNew.value = ''; fConfirm.value = '';
        } catch (e) {
            restore();
            showAlert(e.message || 'Terjadi kesalahan.', 'danger');
        }
    });
})();
