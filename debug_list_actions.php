<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Attempting to list all API actions...\n";

try {
    $response = $neoFeeder->request('ListAction', []);
    if ($response && isset($response['data'])) {
        foreach ($response['data'] as $item) {
            echo $item['act'] . "\n";
        }
    } else {
        echo "ListAction not supported or empty response.\n";
        print_r($response);
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
