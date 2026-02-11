<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

try {
    $neoFeeder = app(NeoFeederService::class);
    $response = $neoFeeder->request('GetJenisKebutuhanKhusus', []);
    
    if ($response && isset($response['data'])) {
        echo "Endpoint: GetJenisKebutuhanKhusus\n";
        echo "Total items: " . count($response['data']) . "\n";
        if (count($response['data']) > 0) {
            echo "First item keys: " . implode(', ', array_keys($response['data'][0])) . "\n";
            echo "First item: " . json_encode($response['data'][0]) . "\n";
        }
    } else {
        echo "Failed to get data from GetJenisKebutuhanKhusus\n";
        print_r($response);
    }
    
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
