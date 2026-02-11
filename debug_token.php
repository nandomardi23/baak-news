<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use App\Models\Setting;

echo "Neo Feeder URL: " . Setting::getValue('neo_feeder_url', 'NOT SET') . "\n";
echo "Neo Feeder Username: " . Setting::getValue('neo_feeder_username', 'NOT SET') . "\n";

try {
    $service = new NeoFeederService();
    echo "Attempting to get token...\n";
    $token = $service->getToken();
    
    if ($token) {
        echo "SUCCESS! Token received: " . substr($token, 0, 10) . "...\n";
    } else {
        echo "FAILED to get token.\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
