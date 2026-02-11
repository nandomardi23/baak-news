<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\ReferenceSyncService;
use App\Models\RefWilayah;

$syncService = app(ReferenceSyncService::class);

echo "Starting verified sync for Wilayah (Batch 1)...\n";

try {
    // We don't truncate as there are 8k records, just test one batch
    $result = $syncService->syncWilayah(0, 50);
    echo "SYNC RESULT:\n";
    print_r($result);
    
    echo "\nSAMPLE STORED ITEMS IN DATABASE (First 5):\n";
    $items = RefWilayah::take(5)->get();
    foreach ($items as $item) {
        echo "ID: " . $item->id_wilayah . " | NAME: " . $item->nama_wilayah . " | INDUK: " . $item->id_induk_wilayah . "\n";
    }
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
