<?php
$css = file_get_contents(__DIR__ . '/public/css/app.css');
$i = strpos($css, '.next-bell {');
echo substr($css, $i, 300) . "\n";