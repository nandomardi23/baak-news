<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

function testEndpoint($neoFeeder, $action, $params = []) {
    echo "Testing $action with params: " . json_encode($params) . "...\n";
    try {
        $response = $neoFeeder->request($action, $params);
        if ($response && isset($response['data'])) {
            echo "SUCCESS! Count: " . count($response['data']) . "\n";
            if (count($response['data']) > 0) {
                echo "First item: " . json_encode($response['data'][0]) . "\n";
            }
        } else {
            echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
            echo "Response: " . json_encode($response) . "\n";
        }
    } catch (\Throwable $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }
    echo "-----------------------------------\n";
}

// Test Kebutuhan Khusus with smaller limit
testEndpoint($neoFeeder, 'GetListKebutuhanKhusus', ['limit' => 10]);
testEndpoint($neoFeeder, 'GetKebutuhanKhusus', ['limit' => 10]);

// Test Ajar Dosen
testEndpoint($neoFeeder, 'GetListAktivitasMengajarDosen', ['limit' => 10]);
testEndpoint($neoFeeder, 'GetAktivitasMengajarDosen', ['limit' => 10]);

// Test Konversi
testEndpoint($neoFeeder, 'GetListKonversiKampusMerdeka', ['limit' => 10]);
testEndpoint($neoFeeder, 'GetKonversiKampusMerdeka', ['limit' => 10]);
