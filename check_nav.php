<?php
$css = file_get_contents(__DIR__ . '/public/css/app.css');
$i = strpos($css, '.nav-item {');
echo substr($css, $i, 250) . "\n";