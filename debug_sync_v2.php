<?php

use App\Services\NeoFeederService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Debug Sync V2 ---\n";

$neo = new NeoFeederService();

// 1. Check Token
echo "[1] Getting Token...\n";
$token = $neo->getToken();
echo "Token: " . ($token ? "OK" : "FAIL") . "\n";

if (!$token) exit;

// 2. Check Count Wilayah (Simulate Controller 'count' check)
echo "\n[2] RequestQuick: GetCountWilayah\n";
try {
    $tStart = microtime(true);
    // Use reflection to access requestQuick since it might be protected/public? It is public.
    $countRaw = $neo->requestQuick('GetCountWilayah', []);
    $tEnd = microtime(true);
    echo "Time: " . round($tEnd - $tStart, 2) . "s\n";
    
    if ($countRaw === null) {
        echo "Result: NULL (Timeout or Error)\n";
    } else {
        echo "Result: Possible JSON\n";
        echo "Error Code: " . ($countRaw['error_code'] ?? 'N/A') . "\n";
        echo "Data: " . json_encode($countRaw['data'] ?? 'No Data') . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// 3. Check GetAgama (Dictionary)
echo "\n[3] Request: GetAgama\n";
$agama = $neo->getAgama();
if ($agama === null) {
    echo "Result: NULL (Failure)\n";
} else {
    echo "Result: OK. Count: " . count($agama['data'] ?? []) . "\n";
}

// 4. Check GetWilayah (Data)
echo "\n[4] Request: GetWilayah (Limit 5)\n";
$wilayah = $neo->getWilayah(5);
if ($wilayah === null) {
    echo "Result: NULL (Failure)\n";
} else {
    echo "Result: OK. Count: " . count($wilayah['data'] ?? []) . "\n";
}
