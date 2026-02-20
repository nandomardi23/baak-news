<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(\App\Services\Sync\AcademicSyncService::class);
echo "Testing sync KRS with new fix...\n";
try {
    $result = $service->syncKrsAllSemesters(3000, 1000);
    echo "Success: " . json_encode($result) . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
