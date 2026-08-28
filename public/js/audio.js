(function () {
    'use strict';

    var editingId = null;
    var currentAudio = null;
    var playingCard = null;
    var stopTimer = null;

    var BASE_URL = '';
    if (typeof BASE_URL !== 'undefined') {
        BASE_URL = String(BASE_URL);
    }
    if (!BASE_URL) {
        var meta = document.querySelector('meta[name="base-url"]');
        if (meta) {
            BASE_URL = meta.getAttribute('content');
        } else {
            BASE_URL = window.location.origin;
        }
    }

    var modal = document.getElementById('audioModal');
    var btnUpload = document.getElementById('btnUpload');
    var btnSave = document.getElementById('btnSave');
    var btnBatal = document.getElementById('btnBatal');
    var modalClose = document.getElementById('modalClose');
    var formError = document.getElementById('formError');
    var modalTitle = document.getElementById('modalTitle');
    var fileGroup = document.getElementById('fileGroup');
    var fFile = document.getElementById('f_file');
    var fName = document.getElementById('f_name');
    var fBellType = document.getElementById('f_bell_type_id');
    var fVolume = document.getElementById('f_volume');
    var fDuration = document.getElementById('f_duration');
    var volLabel = document.getElementById('volLabel');

    function esc(v) {
        return window.App && App.esc ? App.esc(v) : String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

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
        fFile.value = '';
        fName.value = '';
        fBellType.value = '';
        fVolume.value = '0.8';
        updateVolLabel();
        fDuration.value = '5';
        formError.hidden = true;
        fileGroup.style.display = '';
    }

    function showError(msg) {
        formError.textContent = msg;
        formError.hidden = false;
    }

    function updateVolLabel() {
        var v = parseFloat(fVolume.value);
        if (isNaN(v)) { v = 0; }
        volLabel.textContent = Math.round(v * 100) + '%';
    }

    function playerUrl(card) {
        var filepath = card.getAttribute('data-filepath') || '';
        if (filepath) {
            return BASE_URL + filepath;
        }
        return BASE_URL + '/storage/audio/bell-default.wav';
    }

    function stopPreview() {
        if (stopTimer) {
            clearTimeout(stopTimer);
            stopTimer = null;
        }
        if (currentAudio) {
            currentAudio.pause();
            currentAudio.src = '';
            currentAudio = null;
        }
        if (playingCard) {
            var card = playingCard;
            playingCard = null;
            var icon = card.querySelector('.card-icon');
            if (icon) {
                icon.textContent = card.getAttribute('data-icon-default') || '\u{1F3B5}';
            }
            var btn = card.querySelector('.btn-preview');
            if (btn) {
                btn.textContent = 'Preview';
            }
        }
    }

    function startPreview(card, btn, icon, volume, duration) {
        stopPreview();

        var audio = new Audio(playerUrl(card));
        audio.volume = Math.max(0, Math.min(1, volume));
        currentAudio = audio;
        playingCard = card;
        icon.textContent = '\u{1F50A}';
        btn.textContent = 'Stop';

        audio.addEventListener('ended', function () {
            if (currentAudio === audio) { stopPreview(); }
        });

        audio.play().catch(function () {
            if (window.App) { App.toast('Gagal memutar audio.', 'danger'); }
            if (currentAudio === audio) { stopPreview(); }
        });

        if (duration > 0) {
            stopTimer = setTimeout(stopPreview, duration * 1000);
        }
    }

    function bindCard(card) {
        if (card.getAttribute('data-bound') === '1') { return; }
        card.setAttribute('data-bound', '1');

        var id = card.getAttribute('data-id');

        var previewBtn = card.querySelector('.btn-preview');
        if (previewBtn) {
            previewBtn.addEventListener('click', function () {
                if (playingCard === card) {
                    stopPreview();
                    return;
                }
                var icon = card.querySelector('.card-icon');
                var volume = parseFloat(card.getAttribute('data-volume'));
                if (isNaN(volume)) { volume = 0.8; }
                var duration = parseInt(card.getAttribute('data-duration'), 10);
                if (isNaN(duration)) { duration = 5; }
                startPreview(card, previewBtn, icon, volume, duration);
            });
        }

        var editBtn = card.querySelector('.btn-edit');
        if (editBtn) {
            editBtn.addEventListener('click', function () {
                stopPreview();
                editingId = id;
                fFile.value = '';
                fName.value = card.getAttribute('data-name');
                fBellType.value = card.getAttribute('data-bell-type-id');
                fVolume.value = card.getAttribute('data-volume') || '0.8';
                updateVolLabel();
                fDuration.value = card.getAttribute('data-duration') || '5';
                formError.hidden = true;
                fileGroup.style.display = 'none';
                openModal('Edit Audio');
            });
        }

        var delBtn = card.querySelector('.btn-delete');
        if (delBtn) {
            delBtn.addEventListener('click', async function () {
                var name = card.getAttribute('data-name');
                var ok = await App.confirmDelete('Apakah Anda yakin ingin menghapus data ini?');
                if (!ok) { return; }
                stopPreview();
                var fd = new FormData();
                fd.append('_method', 'DELETE');
                var restore = window.App ? App.btnLoading(delBtn, '...') : function () {};
                try {
                    var res = await App.api(BASE_URL + '/admin/audio/' + id, { method: 'POST', body: fd });
                    if (!res.ok) {
                        throw new Error(res.data && res.data.error ? res.data.error : 'Gagal menghapus audio.');
                    }
                    App.toast('Audio berhasil dihapus.', 'success');
                    setTimeout(function () { location.reload(); }, 600);
                } catch (e) {
                    restore();
                    App.toast(e.message || 'Terjadi kesalahan.', 'danger');
                }
            });
        }
    }

    var cards = document.querySelectorAll('#audioGrid .audio-card');
    for (var i = 0; i < cards.length; i++) {
        bindCard(cards[i]);
    }

    btnUpload.addEventListener('click', function () {
        editingId = null;
        resetForm();
        fileGroup.style.display = '';
        openModal('Upload Audio');
    });

    btnBatal.addEventListener('click', closeModal);
    modalClose.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) { closeModal(); }
    });

    fVolume.addEventListener('input', updateVolLabel);

    btnSave.addEventListener('click', async function () {
        formError.hidden = true;

        var name = fName.value.trim();
        var bellTypeId = fBellType.value;
        var volume = fVolume.value || '0.8';
        var duration = parseInt(fDuration.value, 10);

        if (!name) { showError('Nama audio wajib diisi.'); return; }
        if (isNaN(duration)) { showError('Durasi harus berupa angka.'); return; }
        if (duration < 1 || duration > 300) { showError('Durasi harus antara 1 sampai 300 detik.'); return; }

        var fd = new FormData();
        fd.append('name', name);
        fd.append('bell_type_id', bellTypeId);
        fd.append('volume', volume);
        fd.append('duration', String(duration));

        var url = BASE_URL + '/admin/audio';

        if (editingId) {
            fd.append('_method', 'PUT');
            url = url + '/' + editingId;
        } else {
            if (!fFile.files || !fFile.files.length) {
                showError('File audio wajib dipilih.');
                return;
            }
            fd.append('file', fFile.files[0]);
        }

        var restore = window.App ? App.btnLoading(btnSave, 'Menyimpan...') : function () {};
        try {
            var res = await App.api(url, { method: 'POST', body: fd });
            if (!res.ok) {
                throw new Error(res.data && res.data.error ? res.data.error : 'Terjadi kesalahan saat menyimpan.');
            }

            App.toast(editingId ? 'Audio diperbarui.' : 'Audio berhasil diunggah.', 'success');
            closeModal();
            setTimeout(function () { location.reload(); }, 700);
        } catch (e) {
            restore();
            showError(e.message || 'Terjadi kesalahan.');
        }
    });
})();