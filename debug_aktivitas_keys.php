<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = new NeoFeederService();

function test($neoFeeder, $act) {
    try {
        echo "Testing $act...\n";
        $response = $neoFeeder->request($act, ['limit' => 1]);
        if (!empty($response['data'])) {
            $keys = array_keys($response['data'][0]);
            sort($keys);
            echo "Success! Keys:\n";
            print_r($keys);
        } else {
            echo "No data or failed.\n";
            if (isset($response['error_desc'])) echo $response['error_desc'] . "\n";
        }
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
    echo "------------------------------------------------\n";
}

test($neoFeeder, 'GetListAktivitasMahasiswa');
test($neoFeeder, 'GetListAnggotaAktivitasMahasiswa');
test($neoFeeder, 'GetListKonversiKampusMerdeka');
