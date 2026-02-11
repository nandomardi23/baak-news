<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$endpoints = [
    'GetJenisKebutuhanKhusus',
    'GetKebutuhanKhusus',
    'GetJenisKebutuhan',
    'GetKebutuhan',
    'GetKategoriKebutuhanKhusus',
    'GetKategoriKebutuhan'
];

$neoFeeder = app(NeoFeederService::class);

foreach ($endpoints as $endpoint) {
    echo "Testing endpoint: $endpoint\n";
    try {
        $response = $neoFeeder->request($endpoint, ['limit' => 5]);
        if ($response && isset($response['data']) && count($response['data']) > 0) {
            echo "  - SUCCESS! Items: " . count($response['data']) . "\n";
            echo "  - First item: " . json_encode($response['data'][0]) . "\n";
        } elseif ($response && isset($response['error_desc'])) {
            echo "  - API ERROR: " . $response['error_desc'] . "\n";
        } else {
            echo "  - EMPTY or NULL response\n";
        }
    } catch (\Throwable $e) {
        echo "  - EXCEPTION: " . $e->getMessage() . "\n";
    }
    echo "-------------------\n";
}
