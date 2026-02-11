<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$acts = [
    'GetCountKebutuhanKhusus',
    'GetCountJenisKebutuhan',
    'GetCountRefKebutuhanKhusus',
];

foreach ($acts as $act) {
    echo "Testing $act: ";
    try {
        $response = $neoFeeder->request($act, []);
        if ($response && isset($response['data'])) {
            echo "SUCCESS! Count: " . json_encode($response['data']) . "\n";
        } else {
            echo "Failed: " . ($response['error_desc'] ?? 'Null') . "\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
