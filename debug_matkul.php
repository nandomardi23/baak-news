<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$endpoints = [
    'GetCountMataKuliah',
    'GetMataKuliah',
    'GetCountMatkulKurikulum',
    'GetMatkulKurikulum',
];

foreach ($endpoints as $endpoint) {
    echo "Testing $endpoint...\n";
    $params = (strpos($endpoint, 'Count') === false) ? ['limit' => 5] : [];
    try {
        $response = $neoFeeder->request($endpoint, $params);
        if ($response && isset($response['data'])) {
            echo "SUCCESS: " . (is_array($response['data']) ? "Items: " . count($response['data']) : "Value: " . $response['data']) . "\n";
            if (is_array($response['data']) && count($response['data']) > 0) {
                echo "Keys: " . implode(', ', array_keys($response['data'][0])) . "\n";
            }
        } else {
            echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
            print_r($response);
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }
    echo "-------------------\n";
}
