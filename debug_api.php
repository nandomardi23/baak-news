<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

echo "Testing Neo Feeder API: GetKebutuhanKhusus\n";

try {
    $neoFeeder = app(NeoFeederService::class);
    $response = $neoFeeder->request('GetKebutuhanKhusus', []);
    
    if ($response === null) {
        echo "Response is NULL (Check NeoFeeder logs)\n";
    } else {
        echo "Response received:\n";
        print_r($response);
        
        if (isset($response['error_code']) && $response['error_code'] != 0) {
            echo "API Error: " . $response['error_desc'] . "\n";
        }
    }
    
} catch (\Throwable $e) {
    echo "FATAL ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
