<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

$neoFeeder = new NeoFeederService();
try {
    // Try to get data 
    $response = $neoFeeder->getBimbinganMahasiswa(1, 0); 
    if (!empty($response['data'])) {
        $keys = array_keys($response['data'][0]);
        sort($keys);
        print_r($keys);
        print_r($response['data'][0]);
    } else {
        echo "No data returned.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
