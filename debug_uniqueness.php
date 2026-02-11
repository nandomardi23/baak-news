<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

try {
    $neoFeeder = app(NeoFeederService::class);
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 2000]);
    
    if ($response && isset($response['data'])) {
        $data = $response['data'];
        $count = count($data);
        $uniqueIds = [];
        $uniqueNames = [];
        
        foreach ($data as $item) {
            $uniqueIds[$item['id_kebutuhan_khusus']] = true;
            $uniqueNames[$item['nama_kebutuhan_khusus']] = true;
        }
        
        echo "Fetched: $count items\n";
        echo "Unique IDs: " . count($uniqueIds) . "\n";
        echo "Unique Names: " . count($uniqueNames) . "\n";
        
        if (count($uniqueIds) < 100) {
            echo "Top Unique Names:\n";
            print_r(array_keys($uniqueNames));
        }
    } else {
        echo "Failed to get data\n";
    }
    
} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
