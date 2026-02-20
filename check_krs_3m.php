<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulating offset 3M
$offset = 3000000;
$limit = 1000;

$service = app(\App\Services\Sync\AcademicSyncService::class);
try {
    $result = $service->syncKrsAllSemesters($offset, $limit);
    echo "Success for 3M offset. HasMore: " . ($result['has_more'] ? 'yes' : 'no') . "\n";
    echo "Next offset: " . $result['next_offset'] . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
