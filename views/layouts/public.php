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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= App::asset('css/app.css') ?>?v=<?= @filemtime(BASE_PATH . '/public/css/app.css') ?: time() ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔔</text></svg>">
    <script>(function(){var s=localStorage.getItem('theme');var t=(s==='dark'||s==='light')?s:(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body class="pub-body">
    <div class="pub-bg" aria-hidden="true"><span class="pub-blob b1"></span><span class="pub-blob b2"></span><span class="pub-blob b3"></span></div>
    <div id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>window.BASE_URL = <?= json_encode(App::baseUrl()) ?>;
    window.TIMEZONE = <?= json_encode($settings['timezone'] ?? 'Asia/Jakarta') ?>;</script>
    <?= $content ?>

    <footer class="pub-footer">
        <span class="pub-footer-badge"><?= App::icon('bell', 'w-4 h-4') ?></span>
        <span><strong><?= htmlspecialchars($settings['school_name']) ?></strong> &copy; <?= date('Y') ?> &mdash; Sistem Bel Sekolah Digital</span>
    </footer>

    <script src="<?= App::asset('js/app.js') ?>?v=<?= @filemtime(BASE_PATH . '/public/js/app.js') ?: time() ?>"></script>
</body>
</html>