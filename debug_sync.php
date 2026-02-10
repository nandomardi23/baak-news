<?php

use App\Services\Sync\ReferenceSyncService;
use App\Services\NeoFeederService;

$service = app(ReferenceSyncService::class);
$neo = app(NeoFeederService::class);

echo "=== DEBUGGING REFERENCE SYNC ===\n";

// 1. Check GetCountWilayah
try {
    echo "1. Checking GetCountWilayah raw response...\n";
    $raw = $neo->request('GetCountWilayah', []);
    echo "Raw Response: " . json_encode($raw) . "\n";
} catch (\Exception $e) {
    echo "ERROR GetCountWilayah: " . $e->getMessage() . "\n";
}

// 2. Test Sync Wilayah
try {
    echo "\n2. Testing Sync Wilayah (Limit 5)...\n";
    $res = $service->syncWilayah(0, 5);
    echo "Result: " . json_encode($res) . "\n";
} catch (\Exception $e) {
    echo "ERROR SyncWilayah: " . $e->getMessage() . "\n";
}

// 3. Test All Simple References
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
        echo "\n3. Testing {$method}...\n";
        $res = $service->$method();
        echo "Result: " . json_encode($res) . "\n";
    } catch (\Exception $e) {
        echo "ERROR {$method}: " . $e->getMessage() . "\n";
    }
}
