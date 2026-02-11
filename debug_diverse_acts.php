<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$acts = [
    'GetJenisKebutuhanKhusus',
    'GetJenisKebutuhan',
    'GetKebutuhanKhususRef',
    'GetKebutuhanKhususJenis',
    'GetRefKebutuhanKhusus',
    'GetKategoriKebutuhanKhusus',
    'GetKebutuhanKhususKategori'
];

foreach ($acts as $act) {
    echo "Testing $act: ";
    try {
        $response = $neoFeeder->request($act, ['limit' => 5]);
        if ($response && isset($response['data']) && count($response['data']) > 0) {
            echo "SUCCESS! Items: " . count($response['data']) . "\n";
            print_r($response['data'][0]);
        } else {
            echo "Failed: " . ($response['error_desc'] ?? 'Null') . "\n";
        }
    } catch (\Throwable $e) {
        echo "Error\n";
    }
}
