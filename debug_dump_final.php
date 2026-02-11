<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "START_DUMP\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 1]);
    if ($response && isset($response['data']) && count($response['data']) > 0) {
        $item = $response['data'][0];
        foreach ($item as $key => $value) {
            echo "KEY:[$key] VALUE:[$value]\n";
        }
    } else {
        echo "EMPTY_OR_FAILED\n";
        print_r($response);
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "END_DUMP\n";
