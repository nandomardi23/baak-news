<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Fetching first 100 items from GetKebutuhanKhusus:\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 100]);
    if ($response && isset($response['data'])) {
        foreach ($response['data'] as $index => $item) {
            echo sprintf("[%03d] ID: %s | NAME: %s\n", $index, $item['id_kebutuhan_khusus'], $item['nama_kebutuhan_khusus']);
        }
    } else {
        echo "Failed or empty response\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
