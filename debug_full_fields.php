<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Dumping ALL fields of the first item from GetKebutuhanKhusus:\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 5]);
    if ($response && isset($response['data']) && count($response['data']) > 0) {
        foreach ($response['data'] as $index => $item) {
            echo "Item $index:\n";
            print_r($item);
            echo "-------------------\n";
        }
    } else {
        echo "No data found\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
