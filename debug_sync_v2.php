<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\ReferenceSyncService;
use App\Services\NeoFeederService;

$service = app(ReferenceSyncService::class);
$neo = app(NeoFeederService::class);

echo "=== DEBUGGING REFERENCE SYNC V2 ===\n";

// 1. Test All Simple References (Referensi Umum)
$methods = [
    'syncAgama',
    'syncJenisTinggal',
    'syncAlatTransportasi',
    'syncPekerjaan',
    'syncPenghasilan',
    'syncKebutuhanKhusus',
    'syncPembiayaan'
];

foreach ($methods as $method) {
    try {
        echo "Testing {$method}... ";
        $start = microtime(true);
        $res = $service->$method();
        $duration = round(microtime(true) - $start, 2);
        echo "OK ({$duration}s). Result: " . json_encode($res) . "\n";
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// 2. Test Sync Wilayah (Data Wilayah) - skipping GetCount for now to see if Sync itself works
try {
    echo "\nTesting syncWilayah (limit 5)... ";
    $res = $service->syncWilayah(0, 5);
    echo "OK. Result: " . json_encode($res) . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

// 3. Check GetCountWilayah specifically
try {
    echo "\nChecking GetCountWilayah raw response... ";
    $raw = $neo->request('GetCountWilayah', []);
    echo "Raw: " . json_encode($raw) . "\n";
} catch (\Exception $e) {
    echo "ERROR GetCountWilayah: " . $e->getMessage() . "\n";
}
