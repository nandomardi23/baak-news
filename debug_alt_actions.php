<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$alternatives = [
    'GetJenisKebutuhanKhusus',
    'GetJenisKebutuhan',
    'GetKebutuhan',
    'GetListKebutuhanKhusus',
    'GetRefKebutuhanKhusus',
    'GetKategoriKebutuhanKhusus',
    'GetKategoriKebutuhan',
    'GetListJenisKebutuhanKhusus'
];

foreach ($alternatives as $alt) {
    echo "Testing: $alt... ";
    try {
        // Set a shorter timeout for this test
        $response = $neoFeeder->request($alt, ['limit' => 1]);
        if ($response && isset($response['data'])) {
            echo "MATCH! Count: " . count($response['data']) . "\n";
            print_r($response['data'][0]);
        } else {
            echo "Failed: " . ($response['error_desc'] ?? 'Null') . "\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
