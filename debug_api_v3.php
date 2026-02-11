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
        echo "First 10 items:\n";
        for ($i = 0; $i < min(10, count($response['data'])); $i++) {
            echo "Item $i: ";
            echo $response['data'][$i]['id_kebutuhan_khusus'] . " - " . $response['data'][$i]['nama_kebutuhan_khusus'] . "\n";
        }
    } else {
        echo "No data found\n";
    }
    
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
