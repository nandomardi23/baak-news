<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Testing getCountKonversiKampusMerdeka()...\n";
$countResponse = $neoFeeder->getCountKonversiKampusMerdeka();
print_r($countResponse);

echo "\nTesting getKonversiKampusMerdeka(10, 0)...\n";
$response = $neoFeeder->getKonversiKampusMerdeka(10, 0);

if ($response && isset($response['data'])) {
    echo "SUCCESS! Count: " . count($response['data']) . "\n";
    if (count($response['data']) > 0) {
        print_r($response['data'][0]);
    } else {
        echo "No data returned (possibly filtered or empty in Neo Feeder).\n";
    }
} else {
    echo "FAILED! Response:\n";
    print_r($response);
}
