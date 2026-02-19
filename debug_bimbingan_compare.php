<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

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
            echo "No data or failed. Response:\n";
            // print_r($response); // Too verbose if large
            echo isset($response['error_desc']) ? $response['error_desc'] : "Unknown error/empty";
            echo "\n";
        }
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
    echo "------------------------------------------------\n";
}

// Test the one used in code
test($neoFeeder, 'GetListBimbingMahasiswa');

// Test the one that might be correct?
test($neoFeeder, 'GetListBimbinganMahasiswa');
