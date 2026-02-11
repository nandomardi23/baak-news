<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Final key inspection for GetKebutuhanKhusus:\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 1]);
    if ($response && isset($response['data']) && count($response['data']) > 0) {
        $item = $response['data'][0];
        echo "KEYS: " . implode(', ', array_keys($item)) . "\n";
        print_r($item);
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
