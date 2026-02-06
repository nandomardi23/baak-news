<?php

use App\Services\NeoFeederService;
use App\Models\Setting;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Neo Feeder URL: " . Setting::getValue('neo_feeder_url') . "\n";

$service = new NeoFeederService();
try {
    $token = $service->getToken();
    echo "Token: " . substr($token, 0, 10) . "...\n";
} catch (\Exception $e) {
    echo "Error getting token: " . $e->getMessage() . "\n";
}
