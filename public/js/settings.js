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

    var alertContainer = document.getElementById('alertContainer');
    var btnSave = document.getElementById('btnSave');
    var togglePass = document.getElementById('togglePass');
    var passFields = document.getElementById('passFields');

    var systemToggle = document.getElementById('system_toggle');
    var fSystemActive = document.getElementById('f_system_active');
    var systemStatus = document.getElementById('systemStatus');

    var fSchoolName = document.getElementById('f_school_name');
    var fSchoolAddress = document.getElementById('f_school_address');
    var fTimezone = document.getElementById('f_timezone');
    var fTimeFormat = document.getElementById('f_time_format');
    var fVolume = document.getElementById('f_volume');
    var fDuration = document.getElementById('f_duration');

    var fOldPass = document.getElementById('f_old_pass');
    var fNewPass = document.getElementById('f_new_pass');
    var fConfirmPass = document.getElementById('f_confirm_pass');

    var volLabel = document.getElementById('volLabel');

    // Preview logo langsung saat memilih file
    var logoInput = document.getElementById('f_school_logo');
    var logoPreviewBox = document.getElementById('logoPreviewBox');
    if (logoInput && logoPreviewBox) {
        logoInput.addEventListener('change', function () {
            var file = logoInput.files && logoInput.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                logoPreviewBox.className = 'brand-logo';
                logoPreviewBox.innerHTML = '<img src="' + e.target.result + '" alt="Preview Logo" style="width:100%;height:100%;object-fit:contain;">';
            };
            reader.readAsDataURL(file);
        });
    }

    function initVolumeLabel() {
        volLabel.textContent = Math.round(parseFloat(fVolume.value || '0') * 100) + '%';
    }

    function updateSystemStatus(active) {
        fSystemActive.value = active ? '1' : '0';
        systemStatus.classList.toggle('alert-success', active);
        systemStatus.classList.toggle('alert-danger', !active);
        systemStatus.querySelector('span').textContent =
            'Sistem bel otomatis ' + (active ? 'AKTIF' : 'NONAKTIF');
    }

    function showAlert(msg, type) {
        if (window.App) {
            App.showAlert(alertContainer, msg, type);
        } else {
            alertContainer.innerHTML = '<div class="alert alert-' + type + ' animate-slide-in"><span>' + msg + '</span></div>';
        }
    }

    fVolume.addEventListener('input', function () {
        var v = parseFloat(fVolume.value || '0');
        volLabel.textContent = Math.round(v * 100) + '%';
    });

    togglePass.addEventListener('click', function () {
        var hidden = passFields.classList.contains('hidden');
        passFields.classList.toggle('hidden', !hidden);
        togglePass.textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
    });

    systemToggle.addEventListener('change', function () {
        updateSystemStatus(systemToggle.checked);
    });

    btnSave.addEventListener('click', async function () {
        var newPass = fNewPass.value;
        var confirmPass = fConfirmPass.value;
        var oldPass = fOldPass.value;

        if (newPass) {
            if (!oldPass) {
                showAlert('Password lama wajib diisi.', 'danger');
                return;
            }
            if (newPass.length < 6) {
                showAlert('Password baru minimal 6 karakter.', 'danger');
                return;
            }
            if (newPass !== confirmPass) {
                showAlert('Konfirmasi password tidak cocok.', 'danger');
                return;
            }
        }

        var body = {
            system_active: parseInt(fSystemActive.value || '0', 10)
        };

        if (newPass) {
            body.old_password = oldPass;
            body.new_password = newPass;
        }

        // Gunakan FormData (agar bisa upload file logo)
        var fd = new FormData();
        fd.append('school_name', fSchoolName.value.trim());
        fd.append('school_address', fSchoolAddress.value.trim());
        fd.append('timezone', fTimezone.value);
        fd.append('time_format', fTimeFormat.value);
        fd.append('default_volume', parseFloat(fVolume.value || '0'));
        fd.append('bell_duration', parseInt(fDuration.value || '5', 10));
        fd.append('system_active', parseInt(fSystemActive.value || '0', 10));
        fd.append('old_password', oldPass || '');
        fd.append('new_password', newPass || '');
        var logoInput = document.getElementById('f_school_logo');
        if (logoInput && logoInput.files && logoInput.files[0]) {
            fd.append('school_logo', logoInput.files[0]);
        }

        var restore = window.App ? App.btnLoading(btnSave, 'Menyimpan...') : function () {};
        try {
            var res = await App.api(BASE_URL + '/admin/pengaturan', {
                method: 'POST',
                body: fd
            });

            if (!res.ok) {
                throw new Error(res.data && res.data.error ? res.data.error : 'Terjadi kesalahan saat menyimpan.');
            }

            App.toast('Pengaturan berhasil disimpan.', 'success');
            showAlert(res.data && res.data.message ? res.data.message : 'Pengaturan berhasil disimpan.', 'success');
            setTimeout(function () { location.reload(); }, 1200);
        } catch (e) {
            restore();
            App.toast(e.message || 'Terjadi kesalahan.', 'danger');
            showAlert(e.message || 'Terjadi kesalahan.', 'danger');
        }
    });

    initVolumeLabel();
})();