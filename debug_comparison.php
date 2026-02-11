<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "--- GetAgama ---\n";
$resAgama = $neoFeeder->request('GetAgama', ['limit' => 5]);
print_r($resAgama['data'] ?? 'No Data');

echo "\n--- GetKebutuhanKhusus ---\n";
$resKebutuhan = $neoFeeder->request('GetKebutuhanKhusus', ['limit' => 5]);
print_r($resKebutuhan['data'] ?? 'No Data');
