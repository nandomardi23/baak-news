<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

$neoFeeder = new NeoFeederService();

// Try raw request to verify endpoint response
try {
    echo "Trying GetListBimbinganMahasiswa...\n";
    $response = $neoFeeder->request('GetListBimbinganMahasiswa', ['limit' => 1]);
    if (!empty($response['data'])) {
        $keys = array_keys($response['data'][0]);
        sort($keys);
        print_r($keys);
        // Check specific keys
        $hasId = in_array('id_bimbingan_mahasiswa', $keys);
        echo "Has id_bimbingan_mahasiswa: " . ($hasId ? "YES" : "NO") . "\n";
    } else {
        echo "No data or failed.\n";
        print_r($response);
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
