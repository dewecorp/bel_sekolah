<?php
/**
 * Layout Admin
 * Variabel tersedia: $settings, $currentUser, $baseUrl, $content, $title, $activeMenu
 */
use Core\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(App::csrfToken()) ?>">
    <meta name="base-url" content="<?= htmlspecialchars(App::baseUrl()) ?>">
    <title><?= htmlspecialchars($title ?? 'Panel Admin') ?> | <?= htmlspecialchars($settings['school_name']) ?></title>
    <link rel="stylesheet" href="<?= App::asset('css/app.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔔</text></svg>">
</head>
<body class="admin-body">
    <div id="toastContainer"></div>

    <!-- Overlay mobile -->
    <div class="overlay" id="overlay" onclick="toggleSidebar(false)"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand-logo" style="width:40px;height:40px;">
                <?php if (App::logoUrl()): ?>
                    <img src="<?= htmlspecialchars(App::logoUrl()) ?>" alt="Logo" style="width:100%;height:100%;object-fit:contain;">
                <?php else: ?>
                    <div class="brand-logo-icon" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"><?= App::icon('bell', 'w-5 h-5 text-white') ?></div>
                <?php endif; ?>
            </div>
            <div>
                <div class="sidebar-brand"><?= htmlspecialchars($settings['school_name']) ?></div>
                <div class="sidebar-brand-sm"><?= htmlspecialchars($settings['school_address'] ?: 'Sistem Bel Sekolah Digital') ?></div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= App::url('/admin/dashboard') ?>" class="nav-item <?= ($activeMenu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon"><?= App::icon('chart') ?></span> Dashboard
            </a>
            <a href="<?= App::url('/admin/jadwal') ?>" class="nav-item <?= ($activeMenu ?? '') === 'jadwal' ? 'active' : '' ?>">
                <span class="nav-icon"><?= App::icon('calendar') ?></span> Jadwal Bel
            </a>
            <a href="<?= App::url('/admin/bel') ?>" class="nav-item <?= ($activeMenu ?? '') === 'bel' ? 'active' : '' ?>">
                <span class="nav-icon"><?= App::icon('bell') ?></span> Jenis Bel
            </a>
            <a href="<?= App::url('/admin/audio') ?>" class="nav-item <?= ($activeMenu ?? '') === 'audio' ? 'active' : '' ?>">
                <span class="nav-icon"><?= App::icon('music') ?></span> Audio Bel
            </a>
            <a href="<?= App::url('/admin/libur') ?>" class="nav-item <?= ($activeMenu ?? '') === 'libur' ? 'active' : '' ?>">
                <span class="nav-icon"><?= App::icon('calendar-days') ?></span> Hari Libur
            </a>
            <a href="<?= App::url('/admin/riwayat') ?>" class="nav-item <?= ($activeMenu ?? '') === 'riwayat' ? 'active' : '' ?>">
                <span class="nav-icon"><?= App::icon('clipboard') ?></span> Riwayat Bel
            </a>
            <a href="<?= App::url('/admin/pengaturan') ?>" class="nav-item <?= ($activeMenu ?? '') === 'pengaturan' ? 'active' : '' ?>">
                <span class="nav-icon"><?= App::icon('cog') ?></span> Pengaturan
            </a>
        </nav>

        <div class="sidebar-footer">
            <div style="padding:0 0.5rem 0.75rem;font-size:0.75rem;color:#a7f3d0;">
                Masuk sebagai: <strong><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></strong>
            </div>
            <?php if (App::url('/admin/logout') !== false): ?>
            <a href="<?= App::url('/admin/pengaturan') ?>" class="nav-item">
                <span class="nav-icon"><?= App::icon('user') ?></span> Ubah Password
            </a>
            <?php endif; ?>
            <a href="javascript:void(0)" class="nav-item" id="logoutBtn">
                <span class="nav-icon"><?= App::icon('logout') ?></span> Logout
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-wrapper">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:1rem;">
                <button class="hamburger" onclick="toggleSidebar()"><?= App::icon('bars', 'w-6 h-6') ?></button>
                <span class="topbar-title"><?= htmlspecialchars($title ?? 'Dashboard') ?></span>
            </div>
            <div class="topbar-actions">
                <span class="status-pill <?= (int)($settings['system_active'] ?? 1) === 1 ? 'status-active' : 'status-inactive' ?>">
                    <span class="status-dot"></span>
                    <?= (int)($settings['system_active'] ?? 1) === 1 ? 'Sistem Aktif' : 'Sistem Nonaktif' ?>
                </span>
            </div>
        </header>

        <main class="main-content">
            <?= $content ?>
        </main>
    </div>

    <form id="logoutForm" method="POST" action="<?= App::url('/auth/logout') ?>" class="hidden">
        <?= App::csrfField() ?>
    </form>

    <script>
        window.BASE_URL = <?= json_encode(App::baseUrl()) ?>;
        window.TIMEZONE = <?= json_encode($settings['timezone'] ?? 'Asia/Jakarta') ?>;
        const BASE_URL = window.BASE_URL;
        function toggleSidebar(force) {
            const sb = document.getElementById('sidebar');
            const ov = document.getElementById('overlay');
            const open = force === undefined ? !sb.classList.contains('open') : force;
            sb.classList.toggle('open', open);
            ov.classList.toggle('show', open);
        }
        document.getElementById('logoutBtn')?.addEventListener('click', async () => {
            const ok = await App.confirmDelete('Anda akan keluar dari panel admin. Lanjutkan?');
            if (ok) document.getElementById('logoutForm').submit();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= App::asset('js/app.js') ?>"></script>
</body>
</html>