<?php
/** @var array $settings @var array $currentUser */
use Core\App;
$error = $error ?? null;
?>
<div class="auth-card">
    <div class="auth-logo"><?= App::icon('bell', 'w-8 h-8') ?></div>
    <h1 class="auth-title">Bel Sekolah Digital</h1>
    <p class="auth-subtitle">Masuk ke panel administrator</p>

    <?php if ($error): ?>
        <div class="alert alert-danger" id="errorAlert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="<?= App::url('/auth/login') ?>">
        <?= App::csrfField() ?>
        <div class="form-group">
            <label class="form-label" for="username">Username</label>
            <input class="form-input" type="text" id="username" name="username" placeholder="Masukkan username"
                   autocomplete="username" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input class="form-input" type="password" id="password" name="password" placeholder="Masukkan password"
                   autocomplete="current-password" required>
        </div>
        <button type="submit" id="loginBtn" class="btn btn-primary btn-block btn-lg">Masuk</button>
    </form>

    <div class="text-center" style="margin-top:1.25rem;">
        <a href="<?= App::url('/') ?>" class="btn btn-ghost btn-sm"><?= App::icon('arrow-left', 'w-4 h-4') ?>  Kembali ke Dashboard</a>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = document.getElementById('loginBtn');
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    if (!username || !password) {
        App.toast('Username dan password harus diisi', 'warning');
        return;
    }

    const restore = App.btnLoading(btn, 'Masuk...');

    fetch(this.action, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                App.toast('Login berhasil!', 'success');
                setTimeout(() => { window.location.href = data.redirect; }, 600);
            } else {
                restore();
                App.toast(data.error || 'Login gagal', 'danger');
            }
        })
        .catch(() => {
            restore();
            App.toast('Terjadi kesalahan koneksi', 'danger');
        });
});
</script>