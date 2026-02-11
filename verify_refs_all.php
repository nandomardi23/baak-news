<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\ReferenceSyncService;

$syncService = app(ReferenceSyncService::class);

$methods = [
    'syncAgama',
    'syncJenisTinggal',
    'syncAlatTransportasi',
    'syncPekerjaan',
    'syncPenghasilan',
    'syncPembiayaan'
];

echo "Starting comprehensive reference sync verification...\n";

foreach ($methods as $method) {
    echo "Testing $method... ";
    try {
        $result = $syncService->$method();
        if ($result['synced'] > 0) {
            echo "SUCCESS (Synced: " . $result['synced'] . ")\n";
        } else {
            echo "WARNING (Synced 0 records)\n";
        }
    } catch (\Throwable $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "\nVerification complete.\n";
