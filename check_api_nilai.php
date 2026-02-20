<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$neoFeeder = app(\App\Services\NeoFeederService::class);
echo "Testing getNilaiBySemester...\n";
$timeStart = microtime(true);
$response = $neoFeeder->getNilaiBySemester('20231', 1000, 0);
$timeEnd = microtime(true);
echo "Time taken: " . ($timeEnd - $timeStart) . " seconds\n";
if (!$response) {
    echo "NO RESPONSE, TIMEOUT OR ERROR\n";
} else {
    echo "Got data: " . count($response['data'] ?? []) . " records.\n";
    if (isset($response['error_desc']) && $response['error_desc']) {
        echo "API Error: " . $response['error_desc'] . "\n";
    }
}
