<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

try {
    $neoFeeder = app(NeoFeederService::class);
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 20]);
    
    if ($response && isset($response['data'])) {
        foreach ($response['data'] as $index => $item) {
            echo "Item $index: " . json_encode($item) . "\n";
        }
    } else {
        echo "Failed to get data\n";
    }
    
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
