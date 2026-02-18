<?php

use App\Services\NeoFeederService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Debugging Neo Feeder API ---\n";

try {
    $neo = new NeoFeederService();
    
    // Test 1: Token
    echo "\n1. Testing Token...\n";
    $token = $neo->getToken();
    echo "Token: " . ($token ? substr($token, 0, 10) . "..." : "FAILED") . "\n";

    if (!$token) {
        die("Stopping: No Token.\n");
    }

    // Test 2: Agama (Simple Dictionary)
    echo "\n2. Testing GetAgama (Simple)...\n";
    $agama = $neo->getAgama();
    echo "Agama Response: " . json_encode($agama) . "\n";

    // Test 3: Wilayah (Large Data)
    echo "\n3. Testing GetWilayah (Limit 5)...\n";
    $wilayah = $neo->getWilayah(5); // Request only 5
    if ($wilayah) {
        echo "Wilayah Count: " . count($wilayah['data'] ?? []) . "\n";
        echo "First Item: " . json_encode($wilayah['data'][0] ?? []) . "\n";
        echo "Error Code: " . ($wilayah['error_code'] ?? 'None') . "\n";
        echo "Error Desc: " . ($wilayah['error_desc'] ?? 'None') . "\n";
    } else {
        echo "Wilayah Response is NULL (Failed).\n";
    }

} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
