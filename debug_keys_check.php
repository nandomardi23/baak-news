<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Dumping items to check for student mapping keys:\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 20]);
    if ($response && isset($response['data'])) {
        foreach ($response['data'] as $index => $item) {
            echo "Item $index keys: " . implode(', ', array_keys($item)) . "\n";
            echo "Item $index: " . json_encode($item) . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
