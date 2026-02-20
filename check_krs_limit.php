<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$neoFeeder = app(\App\Services\NeoFeederService::class);
echo "Testing getKrsBySemester with limit 1000 for semester 20231...\n";
try {
    $timeStart = microtime(true);
    $response = $neoFeeder->getKrsBySemester('20231', 1000, 0);
    $timeEnd = microtime(true);
    echo "Time taken: " . ($timeEnd - $timeStart) . " seconds\n";
    $data = $response['data'] ?? [];
    echo "Fetched items: " . count($data) . "\n";
} catch (\Exception $e) {
    echo "Failed with limit 1000: " . $e->getMessage() . "\n";
}
