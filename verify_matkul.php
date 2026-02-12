<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\CurriculumSyncService;

$syncService = app(CurriculumSyncService::class);

echo "Testing syncMataKuliah (limit 10)...\n";
try {
    $result = $syncService->syncMataKuliah(0, 10);
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
