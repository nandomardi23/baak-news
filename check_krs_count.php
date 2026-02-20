<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$neoFeeder = app(\App\Services\NeoFeederService::class);
echo "Testing GetCountKRSMahasiswa for semester 20231...\n";
try {
    $timeStart = microtime(true);
    $countResponse = $neoFeeder->requestQuick('GetCountKRSMahasiswa', ['filter' => "id_periode = '20231'"]);
    $timeEnd = microtime(true);
    echo "Time taken: " . ($timeEnd - $timeStart) . " seconds\n";
    $totalAll = $countResponse['data']['total'] ?? $countResponse['data'] ?? 0;
    echo "Count: " . json_encode($totalAll) . "\n";
} catch (\Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
