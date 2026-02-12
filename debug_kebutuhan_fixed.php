<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Testing GetKebutuhanKhusus WITHOUT limit...\n";
try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', []);
    if ($response && isset($response['data'])) {
        echo "SUCCESS! Count: " . count($response['data']) . "\n";
        if (count($response['data']) > 0) {
            echo "First item: " . json_encode($response['data'][0]) . "\n";
        }
    } else {
        echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
        print_r($response);
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
