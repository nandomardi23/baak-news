<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Listing actions filtered by 'Kebutuhan':\n";

try {
    // Some Feeder versions support filtering in ListAction
    $response = $neoFeeder->request('ListAction', ['filter' => "action LIKE '%Kebutuhan%'"]);
    if ($response && isset($response['data'])) {
        foreach ($response['data'] as $item) {
            echo "ACTION: " . ($item['action'] ?? $item['table'] ?? json_encode($item)) . "\n";
        }
    } else {
        echo "No filtered actions found or ListAction failed. Trying unfiltered with small limit...\n";
        $response = $neoFeeder->request('ListAction', ['limit' => 10]);
        if ($response && isset($response['data'])) {
             print_r($response['data']);
        } else {
            echo "Direct ListAction failed: " . ($response['error_desc'] ?? 'Null') . "\n";
        }
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
