<?php
$lines = file('storage/logs/laravel.log');
$errors = array_filter($lines, function ($line) {
    return strpos($line, 'KRS') !== false;
});
foreach (array_slice($errors, -10) as $error) {
    echo trim($error) . "\n";
}
