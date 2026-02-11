<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Searching all 36k items in GetKebutuhanKhusus...\n";

$keywords = ['Netra', 'Rungu', 'Wicara', 'Grahita', 'Daksa', 'Laras', 'Hiperaktif', 'Bakat'];
$found = [];

$batchSize = 5000;
$total = 36865;

for ($offset = 0; $offset < $total; $offset += $batchSize) {
    echo "Processing offset $offset...\n";
    try {
        $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => $batchSize, 'offset' => $offset]);
        if ($response && isset($response['data'])) {
            foreach ($response['data'] as $item) {
                foreach ($keywords as $kw) {
                    if (str_contains($item['nama_kebutuhan_khusus'], $kw)) {
                        $found[] = $item;
                        // echo "FOUND: " . $item['id_kebutuhan_khusus'] . " - " . $item['nama_kebutuhan_khusus'] . "\n";
                        break; 
                    }
                }
            }
        }
    } catch (\Throwable $e) {
        echo "Error at offset $offset: " . $e->getMessage() . "\n";
    }
}

echo "TOTAL MATCHES: " . count($found) . "\n";
if (count($found) > 0) {
    echo "SAMPLE MATCHES:\n";
    foreach (array_slice($found, 0, 20) as $item) {
        echo "ID: " . $item['id_kebutuhan_khusus'] . " | NAME: " . $item['nama_kebutuhan_khusus'] . "\n";
    }
    
    // Check for single names in matches
    $singles = array_filter($found, function($item) {
        return !str_contains($item['nama_kebutuhan_khusus'], ',');
    });
    echo "SINGLE NAME MATCHES: " . count($singles) . "\n";
    foreach ($singles as $item) {
        echo "ID: " . $item['id_kebutuhan_khusus'] . " | NAME: " . $item['nama_kebutuhan_khusus'] . "\n";
    }
}
