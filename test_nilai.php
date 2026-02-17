<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\NeoFeederService::class);
$idSemester = '20241';

echo "Fetching Nilai Sample...\n";
$resN = $svc->getNilaiBySemester($idSemester, 1, 0);
if ($resN && isset($resN['data'][0])) {
    echo "SUCCESS\n";
    echo "Keys: " . implode(', ', array_keys($resN['data'][0])) . "\n";
    echo "Data: " . json_encode($resN['data'][0], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "FAILED: " . json_encode($resN) . "\n";
}
