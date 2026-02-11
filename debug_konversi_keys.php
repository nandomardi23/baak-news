<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Testing getKonversiKampusMerdeka(1, 0)...\n";
$response = $neoFeeder->getKonversiKampusMerdeka(1, 0);

if ($response && isset($response['data']) && count($response['data']) > 0) {
    echo "KEYS FOUND:\n";
    print_r(array_keys($response['data'][0]));
    echo "\nSAMPLE VALUES:\n";
    print_r($response['data'][0]);
} else {
    echo "FAILED! Response empty or invalid.\n";
    print_r($response);
}
