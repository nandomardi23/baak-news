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
    'GetKebutuhan',
    'GetJenisKebutuhan',
    'GetListKebutuhanKhusus',
    'GetRefKebutuhanKhusus',
    'GetKategoriKebutuhanKhusus',
    'GetPendidikan',
    'GetAgama',
    'GetPekerjaan'
];

$neoFeeder = app(NeoFeederService::class);

foreach ($endpoints as $endpoint) {
    echo "Testing Action: $endpoint\n";
    try {
        $response = $neoFeeder->request($endpoint, ['limit' => 1]);
        if ($response === null) {
            echo "  - RESPONSE IS NULL\n";
        } elseif (isset($response['error_code']) && $response['error_code'] != 0) {
            echo "  - API ERROR ({$response['error_code']}): " . $response['error_desc'] . "\n";
        } elseif (isset($response['data'])) {
            echo "  - SUCCESS! Count: " . count($response['data']) . "\n";
            if (count($response['data']) > 0) {
                echo "  - Sample: " . json_encode($response['data'][0]) . "\n";
            }
        } else {
            echo "  - UNKNOWN RESPONSE FORMAT\n";
            print_r($response);
        }
    } catch (\Throwable $e) {
        echo "  - EXCEPTION: " . $e->getMessage() . "\n";
    }
    echo "-------------------\n";
}
