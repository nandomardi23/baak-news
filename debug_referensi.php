<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\ReferenceSyncService;
use Illuminate\Support\Facades\Log;

echo "Starting Manual Sync Kebutuhan Khusus...\n";

try {
    $service = app(ReferenceSyncService::class);
    $result = $service->syncKebutuhanKhusus();
    
    echo "Result:\n";
    print_r($result);
    
} catch (\Throwable $e) {
    echo "\nFATAL ERROR caught:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
