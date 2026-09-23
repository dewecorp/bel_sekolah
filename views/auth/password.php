<?php
use Core\App; $title = 'Ubah Password'; $activeMenu = 'password'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Ubah Password</h1>
        <p class="page-header-sub">Ganti password akun admin.</p>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card card-pad" style="max-width:560px;margin:0 auto;">
    <form id="passwordForm">
        <?= App::csrfField() ?>
        <div class="form-group">
            <label class="form-label" for="f_old_pass">Password Lama</label>
            <div style="position:relative;">
                <input type="password" id="f_old_pass" class="form-input" autocomplete="current-password" required style="padding-right:2.5rem;">
                <button type="button" class="toggle-pass-btn" data-target="f_old_pass" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);display:flex;align-items:center;" title="Tampilkan/Sembunyikan password">
                    <?= App::icon('eye', 'w-5 h-5') ?>
                </button>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" for="f_new_pass">Password Baru</label>
            <div style="position:relative;">
                <input type="password" id="f_new_pass" class="form-input" autocomplete="new-password" required style="padding-right:2.5rem;">
                <button type="button" class="toggle-pass-btn" data-target="f_new_pass" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);display:flex;align-items:center;" title="Tampilkan/Sembunyikan password">
                    <?= App::icon('eye', 'w-5 h-5') ?>
                </button>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:1.25rem;">
            <label class="form-label" for="f_confirm_pass">Konfirmasi Password Baru</label>
            <div style="position:relative;">
                <input type="password" id="f_confirm_pass" class="form-input" autocomplete="new-password" required style="padding-right:2.5rem;">
                <button type="button" class="toggle-pass-btn" data-target="f_confirm_pass" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);display:flex;align-items:center;" title="Tampilkan/Sembunyikan password">
                    <?= App::icon('eye', 'w-5 h-5') ?>
                </button>
            </div>
        </div>
        <div style="text-align:right;">
            <button type="submit" id="btnSavePass" class="btn btn-primary">Simpan Password</button>
        </div>
    </form>
</div>

<script>
document.querySelectorAll('.toggle-pass-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        this.innerHTML = isPass ? '<?= App::icon('eye-slash', 'w-5 h-5') ?>' : '<?= App::icon('eye', 'w-5 h-5') ?>';
    });
});
</script>

<script src="<?= App::asset('js/password.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/js/password.js') ?: time() ?>"></script>
