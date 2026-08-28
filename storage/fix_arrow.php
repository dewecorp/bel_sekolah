<?php
$root = dirname(__DIR__);

// admin.php: "Dashboard Publik →" -> icon arrow-left
$f = $root . '/views/layouts/admin.php';
$c = file_get_contents($f);
if (preg_match('/(<a href="<\?= App::url\(\'\/\'\) \?>" class="btn btn-sm btn-ghost">)\s*Dashboard Publik\s*(.*?)(<\/a>)/u', $c, $m)) {
    $replace = $m[1] . 'Dashboard Publik ' . iconLeft() . $m[3];
    $c = preg_replace('/(<a href="<\?= App::url\(\'\/\'\) \?>" class="btn btn-sm btn-ghost">)\s*Dashboard Publik\s*(.*?)(<\/a>)/u', $m[1] . 'Dashboard Publik ' . iconLeft() . $m[3], $c, 1);
    file_put_contents($f, $c);
    echo "admin.php arrow fixed\n";
} else {
    echo "admin.php pattern not matched\n";
}

// login.php: "← Kembali ke Dashboard"
$f = $root . '/views/auth/login.php';
$c = file_get_contents($f);
if (preg_match('/(<a href="<\?= App::url\(\'\/\'\) \?>" class="btn btn-ghost btn-sm">\s*)\S+(\s*Kembali ke Dashboard<\/a>)/u', $c, $m)) {
    $c = preg_replace('/(<a href="<\?= App::url\(\'\/\'\) \?>" class="btn btn-ghost btn-sm">\s*)\S+(\s*Kembali ke Dashboard<\/a>)/u', $m[1] . iconLeft() . $m[2], $c, 1);
    file_put_contents($f, $c);
    echo "login.php arrow fixed\n";
} else {
    echo "login.php pattern not matched\n";
}

function iconLeft() {
    return '<?= App::icon(\'arrow-left\', \'w-4 h-4\') ?> ';
}