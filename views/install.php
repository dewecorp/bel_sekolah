<?php
/** @var array $dbConfig */
use Core\App;
?>
<div class="auth-card" style="max-width:520px;">
    <div class="auth-logo"><?= App::icon('cog', 'w-7 h-7') ?></div>
    <h1 class="auth-title">Instalasi Database</h1>
    <p class="auth-subtitle">Bel Sekolah Digital &mdash; Langkah pertama</p>

    <div class="alert alert-info">
        <span>
            Database <strong>bel_sekolah</strong> belum terpasang. Klik tombol di bawah untuk membuat database
            beserta tabel, jadwal contoh, dan akun admin secara otomatis.
        </span>
    </div>

    <div class="card card-pad" style="margin-bottom:1rem;">
        <div style="font-size:0.875rem;">
            <p class="font-bold" style="margin-bottom:0.5rem;">Koneksi database terdeteksi:</p>
            <p>Host: <span class="font-mono"><?= htmlspecialchars($dbConfig['host'] . ':' . $dbConfig['port']) ?></span></p>
            <p>User: <span class="font-mono"><?= htmlspecialchars($dbConfig['username']) ?></span></p>
            <p>Database: <span class="font-mono"><?= htmlspecialchars($dbConfig['database']) ?></span></p>
        </div>
    </div>

    <div id="result"></div>

    <button type="button" id="installBtn" class="btn btn-gradient btn-block btn-lg">
        <?= App::icon('bolt') ?> Jalankan Instalasi
    </button>

    <div class="text-center" style="margin-top:1.25rem;font-size:0.8125rem;color:#64748b;">
        Akun default: <span class="font-mono">admin / admin123</span>
    </div>
</div>

<script>
document.getElementById('installBtn').addEventListener('click', async function () {
    const btn = this;
    const restore = App.btnLoading(btn, 'Memasang database...');

    try {
        const res = await fetch(<?= json_encode(App::url('/install')) ?>, { method: 'POST' });
        const data = await res.json();

        if (data.success) {
            App.showAlert(document.getElementById('result'), '✓ Instalasi berhasil! Mengalihkan ke halaman login...', 'success');
            setTimeout(() => { window.location.href = data.redirect; }, 1500);
        } else {
            restore();
            App.showAlert(document.getElementById('result'), '✗ ' + (data.error || 'Instalasi gagal'), 'danger');
        }
    } catch (err) {
        restore();
        App.showAlert(document.getElementById('result'), '✗ Gagal menghubungi server', 'danger');
    }
});
</script>