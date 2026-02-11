<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Testing getMahasiswa(10, 0)...\n";
$response = $neoFeeder->getMahasiswa(10, 0);

if ($response && isset($response['data'])) {
    echo "SUCCESS! Count: " . count($response['data']) . "\n";
    if (count($response['data']) > 0) {
        $first = $response['data'][0];
        echo "Sample NIM: " . $first['nim'] . " | Nama: " . $first['nama_mahasiswa'] . "\n";
    }
} else {
    echo "FAILED! Response:\n";
    print_r($response);
}
