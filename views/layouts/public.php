<?php
/**
 * Layout Dashboard Publik
 * Variabel tersedia: $settings, $currentUser, $baseUrl, $content, $title
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
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?> | <?= htmlspecialchars($settings['school_name']) ?></title>
    <link rel="stylesheet" href="<?= App::asset('css/app.css') ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔔</text></svg>">
</head>
<body>
    <div id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>window.BASE_URL = <?= json_encode(App::baseUrl()) ?>;
    window.TIMEZONE = <?= json_encode($settings['timezone'] ?? 'Asia/Jakarta') ?>;</script>
    <?= $content ?>

    <footer style="text-align:center;padding:1.5rem;color:#64748b;font-size:0.8125rem;">
        <?= htmlspecialchars($settings['school_name']) ?> &copy; <?= date('Y') ?> &mdash; Sistem Bel Sekolah Digital
    </footer>

    <script src="<?= App::asset('js/app.js') ?>"></script>
</body>
</html>