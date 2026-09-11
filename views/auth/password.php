<?php
use Core\App; $title = 'Ubah Password'; $activeMenu = 'password'; ?>
<div class="page-header">
    <div>
        <h1 class="page-header-title">Ubah Password</h1>
        <p class="page-header-sub">Ganti password akun admin.</p>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card card-pad" style="max-width:560px;">
    <div class="form-group">
        <label class="form-label" for="f_old_pass">Password Lama</label>
        <input type="password" id="f_old_pass" class="form-input" autocomplete="current-password">
    </div>
    <div class="form-group">
        <label class="form-label" for="f_new_pass">Password Baru</label>
        <input type="password" id="f_new_pass" class="form-input" autocomplete="new-password">
    </div>
    <div class="form-group" style="margin-bottom:1.25rem;">
        <label class="form-label" for="f_confirm_pass">Konfirmasi Password Baru</label>
        <input type="password" id="f_confirm_pass" class="form-input" autocomplete="new-password">
    </div>
    <div style="text-align:right;">
        <button type="button" id="btnSavePass" class="btn btn-primary">Simpan Password</button>
    </div>
</div>

<script src="<?= App::asset('js/password.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/js/password.js') ?: time() ?>"></script>
