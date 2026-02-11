<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$prefixes = ['Get', 'GetList', 'GetRef', 'GetJenis', 'GetKategori'];
$names = ['KebutuhanKhusus', 'Kebutuhan', 'JenisKebutuhan', 'KebutuhanKhususMahasiswa'];

foreach ($prefixes as $prefix) {
    foreach ($names as $name) {
        $act = $prefix . $name;
        echo "Testing: $act... ";
        try {
            // Short timeout test
            $response = $neoFeeder->request($act, ['limit' => 1]);
            if ($response && isset($response['data']) && count($response['data']) > 0) {
                echo "MATCH! Count: " . count($response['data']) . "\n";
                if (count($response['data']) < 500) { // Reference tables are usually small
                    echo "  - PROBABLE REFERENCE TABLE\n";
                }
            } else {
                echo "No match\n";
            }
        } catch (\Throwable $e) {
            echo "Error\n";
        }
    }
}
