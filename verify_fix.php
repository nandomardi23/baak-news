<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\ReferenceSyncService;
use App\Models\RefKebutuhanKhusus;

$syncService = app(ReferenceSyncService::class);

echo "Starting verified sync for Kebutuhan Khusus...\n";

// Clear existing to ensure fresh test
RefKebutuhanKhusus::truncate();
echo "Cleared ref_kebutuhan_khusus table.\n";

try {
    $result = $syncService->syncKebutuhanKhusus();
    echo "SYNC RESULT:\n";
    print_r($result);
    
    echo "\nSTORED ITEMS IN DATABASE:\n";
    $items = RefKebutuhanKhusus::all();
    foreach ($items as $item) {
        echo "ID: " . $item->id_kebutuhan_khusus . " | NAME: " . $item->nama_kebutuhan_khusus . "\n";
    }
    echo "Total Stored: " . $items->count() . "\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
