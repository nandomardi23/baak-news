<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Dumping first item of GetKebutuhanKhusus API response:\n";

try {
    $response = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 1]);
    var_dump($response);
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
