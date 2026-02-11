<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\StudentSyncService;

$syncService = app(StudentSyncService::class);
$result = $syncService->syncMahasiswa(0, 1);

if (!empty($result['errors'])) {
    echo "ERROR FOUND:\n";
    echo $result['errors'][0] . "\n";
} else {
    echo "SUCCESS! Items synced: " . $result['synced'] . "\n";
}
