<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Pulling 2000 items from GetKebutuhanKhusus to find categories...\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 2000]);
    if ($response && isset($response['data'])) {
        $count = count($response['data']);
        echo "Successfully pulled $count items.\n";
        
        // Find standard categories (usually have short IDs like 'A', '1', etc)
        $shortIds = array_filter($response['data'], function($item) {
            return strlen($item['id_kebutuhan_khusus']) <= 2;
        });
        
        echo "Found " . count($shortIds) . " items with short IDs.\n";
        print_r(array_values($shortIds));
        
        // Also check if some have single names
        $singleNames = array_filter($response['data'], function($item) {
            return !str_contains($item['nama_kebutuhan_khusus'], ',');
        });
        echo "Found " . count($singleNames) . " items with single names.\n";
    } else {
        echo "Failed: " . ($response['error_desc'] ?? 'Null') . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
