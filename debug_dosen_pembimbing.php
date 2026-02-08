<?php

use App\Services\NeoFeederService;
use App\Models\Mahasiswa;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(NeoFeederService::class);

echo "\n--- Testing GetDosenPembimbing (No Filter) ---\n";
$response = $service->request('GetDosenPembimbing', [
    'limit' => 5
]);

if ($response) {
    echo "Response received:\n";
    if (empty($response['data'])) {
        echo "Data is empty.\n";
        print_r($response);
    } else {
        print_r($response['data']);
    }
} else {
    echo "Request failed or returned null.\n";
}
