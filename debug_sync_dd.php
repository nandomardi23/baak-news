<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\ReferenceSyncService;

$syncService = app(ReferenceSyncService::class);

echo "Dumping syncKebutuhanKhusus response (die dumb):\n";

try {
    // We expect the user wants to see what the API returns for the mapping
    $res = $syncService->syncKebutuhanKhusus();
    var_dump($res);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
