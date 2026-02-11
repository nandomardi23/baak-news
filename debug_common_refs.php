<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$acts = [
    'GetJenisKebutuhan',
    'GetRefJenisKebutuhan',
    'GetKebutuhan',
    'GetDetailKebutuhanKhusus',
];

foreach ($acts as $act) {
    echo "Testing $act: ";
    try {
        // We use request directly to control the feeling
        $response = $neoFeeder->request($act, ['limit' => 1]);
        if ($response && isset($response['data']) && count($response['data']) > 0) {
            echo "SUCCESS! Item: " . json_encode($response['data'][0]) . "\n";
        } else {
            echo "Failed: " . ($response['error_desc'] ?? 'Null') . "\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
