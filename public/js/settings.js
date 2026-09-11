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

    var systemToggle = document.getElementById('system_toggle');
    var fSystemActive = document.getElementById('f_system_active');
    var systemStatus = document.getElementById('systemStatus');

    var fSchoolName = document.getElementById('f_school_name');
    var fSchoolAddress = document.getElementById('f_school_address');
    var fTimezone = document.getElementById('f_timezone');
    var fTimeFormat = document.getElementById('f_time_format');
    var fVolume = document.getElementById('f_volume');
    var fDuration = document.getElementById('f_duration');
    var fDefaultAudio = document.getElementById('f_default_audio');
    var btnPreviewDefault = document.getElementById('btnPreviewDefault');

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

    var previewAudio = null;
    if (btnPreviewDefault) {
        btnPreviewDefault.addEventListener('click', function () {
            if (!fDefaultAudio) { return; }
            var opt = fDefaultAudio.options[fDefaultAudio.selectedIndex];
            var path = opt ? opt.getAttribute('data-filepath') : '';
            if (!path) {
                path = '/storage/audio/bell-default.wav';
            }
            try { if (previewAudio) { previewAudio.pause(); previewAudio = null; } } catch (e) {}
            if (btnPreviewDefault.getAttribute('data-playing') === '1') {
                btnPreviewDefault.setAttribute('data-playing', '0');
                btnPreviewDefault.textContent = 'Preview';
                return;
            }
            previewAudio = new Audio(BASE_URL + path);
            previewAudio.volume = parseFloat(fVolume.value || '0.8');
            btnPreviewDefault.setAttribute('data-playing', '1');
            btnPreviewDefault.textContent = 'Stop';
            previewAudio.addEventListener('ended', function () {
                btnPreviewDefault.setAttribute('data-playing', '0');
                btnPreviewDefault.textContent = 'Preview';
            });
            previewAudio.play().catch(function () {
                btnPreviewDefault.setAttribute('data-playing', '0');
                btnPreviewDefault.textContent = 'Preview';
            });
            setTimeout(function () {
                try { if (previewAudio) { previewAudio.pause(); previewAudio = null; } } catch (e) {}
                btnPreviewDefault.setAttribute('data-playing', '0');
                btnPreviewDefault.textContent = 'Preview';
            }, 8000);
        });
    }

    systemToggle.addEventListener('change', function () {
        updateSystemStatus(systemToggle.checked);
    });

    btnSave.addEventListener('click', async function () {
        var fd = new FormData();
        fd.append('school_name', fSchoolName.value.trim());
        fd.append('school_address', fSchoolAddress.value.trim());
        fd.append('timezone', fTimezone.value);
        fd.append('time_format', fTimeFormat.value);
        fd.append('default_volume', parseFloat(fVolume.value || '0'));
        fd.append('bell_duration', parseInt(fDuration.value || '5', 10));
        fd.append('system_active', parseInt(fSystemActive.value || '0', 10));
        if (fDefaultAudio) { fd.append('default_audio_id', fDefaultAudio.value || '0'); }
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