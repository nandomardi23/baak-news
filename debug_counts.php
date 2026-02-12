<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

$endpoints = [
    'GetCountKebutuhanKhusus',
    'GetCountAktivitasMengajarDosen',
    'GetCountKonversiKampusMerdeka',
    'GetCountAktivitasMahasiswa',
    'GetCountAnggotaAktivitasMahasiswa',
    'GetCountBimbingMahasiswa',
    'GetCountUjiMahasiswa',
];

foreach ($endpoints as $endpoint) {
    echo "Testing $endpoint...\n";
    try {
        $response = $neoFeeder->request($endpoint, []);
        if ($response && isset($response['data'])) {
            echo "SUCCESS: " . json_encode($response['data']) . "\n";
        } else {
            echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }
    echo "-------------------\n";
}
