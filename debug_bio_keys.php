<?php

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$neoFeeder = new NeoFeederService();
try {
    $response = $neoFeeder->getBiodataMahasiswa(null, 1, 0);
    if (!empty($response['data'])) {
        print_r(array_keys($response['data'][0]));
    } else {
        echo "No data returned\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
