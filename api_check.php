<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$neo = app(\App\Services\NeoFeederService::class);
$response = $neo->getDosen(10, 0, "nama_dosen like '%MASYITAH NOVIA YANTI%'");
echo json_encode($response, JSON_PRETTY_PRINT);
echo "\n";
