<?php
$lines = file('storage/logs/laravel.log');
$errors = array_filter($lines, function ($line) {
    return strpos($line, 'local.ERROR') !== false;
});
foreach (array_slice($errors, -5) as $error) {
    echo $error;
}
