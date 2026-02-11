<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$endpoints = [
    'GetKebutuhanKhusus',
    'GetJenisKebutuhanKhusus',
    'KebutuhanKhusus',
    'GetRefKebutuhanKhusus'
];

$neoFeeder = app(NeoFeederService::class);

foreach ($endpoints as $endpoint) {
    echo "Testing endpoint: $endpoint\n";
    try {
        $response = $neoFeeder->request($endpoint, ['limit' => 10]);
        if ($response && isset($response['data'])) {
            echo "  - SUCCESS! Items found: " . count($response['data']) . "\n";
            if (count($response['data']) > 0) {
                echo "  - First item: " . json_encode($response['data'][0]) . "\n";
            }
        } else {
            echo "  - FAILED or No Data: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
        }
    } catch (\Throwable $e) {
        echo "  - EXCEPTION: " . $e->getMessage() . "\n";
    }
    echo "-------------------\n";
}
