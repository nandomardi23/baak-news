<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Testing GetJenisKebutuhanKhusus with 60s timeout...\n";

try {
    $response = $neoFeeder->request('GetJenisKebutuhanKhusus', ['limit' => 5]);
    if ($response && isset($response['data'])) {
        echo "MATCH! Count: " . count($response['data']) . "\n";
        print_r($response['data']);
    } else {
        echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
