<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\AcademicSyncService;

$sync = app(AcademicSyncService::class);
echo "Starting Konversi sync (limit 10)...\n";
$result = $sync->syncKonversiKampusMerdeka(0, 10);

echo "SYNCED: " . $result['synced'] . "\n";
if(!empty($result['errors'])) {
    echo "ERRORS:\n";
    print_r($result['errors']);
} else {
    echo "No errors reported by service.\n";
}
