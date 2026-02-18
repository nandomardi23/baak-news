<?php

use App\Services\NeoFeederService;
use App\Services\Sync\ReferenceSyncService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Debug Sync Force (Prodi) ---\n";

// 1. Check NeoFeeder Connection
$neo = new NeoFeederService();
$token = $neo->getToken();
echo "Token: " . ($token ? "OK" : "FAIL") . "\n";
if (!$token) {
    echo "Connection Failed.\n";
    exit;
}

// 2. Try Count Prodi
echo "\n[2] Count Prodi (Limit 100)\n";
$refService = new ReferenceSyncService($neo);
try {
    $count = $refService->getCountProdi();
    echo "Count Result: " . $count . "\n";
} catch (Exception $e) {
    echo "Count Error: " . $e->getMessage() . "\n";
}

// 3. Try Sync Prodi (Limit 100)
echo "\n[3] Sync Prodi (Limit 100)\n";
try {
    $result = $refService->syncProdi(0, 100);
    echo "Sync Result:\n";
    print_r($result);
} catch (Exception $e) {
    echo "Sync Error: " . $e->getMessage() . "\n";
}

// 4. Check Last Log Entry
echo "\n[4] Last 5 Log Entries:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        echo $line;
    }
}
