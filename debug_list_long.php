<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Attempting ListAction with 120s timeout...\n";

try {
    // NeoFeederService has 120s timeout in its constructor Client
    $response = $neoFeeder->request('ListAction', []);
    if ($response && isset($response['data'])) {
        foreach ($response['data'] as $item) {
            echo $item['act'] . "\n";
        }
    } else {
        echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
