<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$service = new NeoFeederService();

// Try to fetch data for a specific semester (e.g., 20231 or 20241)
$semesterId = '20231';
echo "Testing GetListAktivitasKuliahMahasiswa with filter id_semester = '$semesterId'...\n";

$response = $service->request('GetListAktivitasKuliahMahasiswa', [
    'filter' => "id_semester = '$semesterId'",
    'limit' => 1, // Just get 1 to see if it works fast
]);

if (isset($response['error_code']) && $response['error_code'] == 0) {
    echo "SUCCESS: Found " . count($response['data']) . " records.\n";
    if (count($response['data']) > 0) {
        print_r($response['data'][0]);
    }
} else {
    echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
    print_r($response);
}
