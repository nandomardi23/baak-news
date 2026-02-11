<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Searching for single category items in GetKebutuhanKhusus (first 2000):\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 2000]);
    if ($response && isset($response['data'])) {
        $singleCategories = array_filter($response['data'], function($item) {
            return !str_contains($item['nama_kebutuhan_khusus'], ',');
        });
        
        foreach ($singleCategories as $item) {
            echo "ID: " . $item['id_kebutuhan_khusus'] . " | NAME: " . $item['nama_kebutuhan_khusus'] . "\n";
        }
        echo "Found " . count($singleCategories) . " single items.\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
