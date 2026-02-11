<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

try {
    $neoFeeder = app(NeoFeederService::class);
    $response = $neoFeeder->request('GetKebutuhanKhusus', []);
    
    if ($response && isset($response['data'])) {
        echo "Total items: " . count($response['data']) . "\n";
        echo "First item:\n";
        print_r($response['data'][0]);
    } else {
        echo "No data found or response is NULL\n";
        print_r($response);
    }
    
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
