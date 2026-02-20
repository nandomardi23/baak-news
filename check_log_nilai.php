<?php
$lines = file('storage/logs/laravel.log');
$errors = array_filter($lines, function ($line) {
    return strpos($line, 'ERROR') !== false;
});
foreach (array_slice($errors, -10) as $error) {
    echo substr($error, 0, 500) . "\n"; // Show first 500 chars to avoid huge traces
}
