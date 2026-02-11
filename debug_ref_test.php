<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$alternatives = [
    'GetRefKebutuhanKhusus',
    'GetRefKebutuhan',
    'GetDictionary',
    'Dictionary',
    'GetListKebutuhanKhusus',
    'GetJenisKebutuhanKhusus'
];

foreach ($alternatives as $alt) {
    echo "Testing: $alt... ";
    try {
        $response = $neoFeeder->request($alt, ['limit' => 1]);
        if ($response && isset($response['data'])) {
            echo "MATCH! Count: " . count($response['data']) . "\n";
            print_r($response['data']);
        } else {
            echo "Failed: " . ($response['error_desc'] ?? 'Null response') . "\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
