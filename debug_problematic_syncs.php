<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\LecturerSyncService;
use App\Services\Sync\AcademicSyncService;

$lecSvc = app(LecturerSyncService::class);
$acadSvc = app(AcademicSyncService::class);

echo "--- Testing syncAjarDosen (limit 5) ---\n";
try {
    $result = $lecSvc->syncAjarDosen(0, 5);
    echo "Synced: " . $result['synced'] . "\n";
    if (!empty($result['errors'])) print_r($result['errors']);
} catch (\Exception $e) {
    echo "ERROR syncAjarDosen: " . $e->getMessage() . "\n";
}

echo "\n--- Testing syncKonversiKampusMerdeka (limit 5) ---\n";
try {
    $result = $acadSvc->syncKonversiKampusMerdeka(0, 5);
    echo "Synced: " . $result['synced'] . "\n";
    if (!empty($result['errors'])) print_r($result['errors']);
} catch (\Exception $e) {
    echo "ERROR syncKonversiKampusMerdeka: " . $e->getMessage() . "\n";
}

echo "\n--- Testing syncKebutuhanKhusus (ReferenceSyncService) ---\n";
try {
    $refSvc = app(\App\Services\Sync\ReferenceSyncService::class);
    $result = $refSvc->syncKebutuhanKhusus();
    echo "Synced: " . $result['synced'] . "\n";
    if (!empty($result['errors'] ?? [])) print_r($result['errors']);
} catch (\Exception $e) {
    echo "ERROR syncKebutuhanKhusus: " . $e->getMessage() . "\n";
}
