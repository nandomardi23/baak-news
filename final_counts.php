<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);

$mhs = $svc->request('GetCountMahasiswa', []);
$rp = $svc->request('GetCountRiwayatPendidikanMahasiswa', []);

echo "Mhs: " . ($mhs['data'] ?? 'ERROR') . "\n";
echo "RP: " . ($rp['data'] ?? 'ERROR') . "\n";
echo "Current DB: " . \App\Models\Mahasiswa::count() . "\n";
