<?php
/**
 * Layout Installer
 */
use Core\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(App::csrfToken()) ?>">
    <title>Instalasi | Bel Sekolah Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= App::asset('css/app.css') ?>?v=<?= @filemtime(BASE_PATH . '/public/css/app.css') ?: time() ?>">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔔</text></svg>">
</head>
<body class="auth-body">
    <div id="toastContainer"></div>
    <div style="width:100%;display:flex;justify-content:center;">
        <?= $content ?>
    </div>
    <script src="<?= App::asset('js/app.js') ?>"></script>
</body>
</html>