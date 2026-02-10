<?php

use App\Services\Sync\ReferenceSyncService;
use App\Services\NeoFeederService;

$service = app(ReferenceSyncService::class);
$neo = app(NeoFeederService::class);

echo "=== DEBUG REF SYNC ===\n";

// 1. Test Sync Agama
try {
    echo "1. Sync Agama... ";
    $res = $service->syncAgama();
    echo "OK. Result: " . json_encode($res) . "\n";
} catch (\Exception $e) {
    echo "ERROR Agama: " . $e->getMessage() . "\n";
    // Dump raw data to see what's wrong
    try {
        $raw = $neo->getAgama();
        echo "RAW Agama Data (First Item): " . json_encode($raw['data'][0] ?? 'EMPTY') . "\n";
    } catch (\Exception $ex) {
        echo "RAW Error: " . $ex->getMessage() . "\n";
    }
}

// 2. Test Sync Wilayah
try {
    echo "2. Sync Wilayah (Limit 10)... ";
    $res = $service->syncWilayah(0, 10);
    echo "OK. Result: " . json_encode($res) . "\n";
} catch (\Exception $e) {
    echo "ERROR Wilayah: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
