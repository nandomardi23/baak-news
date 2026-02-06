<?php

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(NeoFeederService::class);
$idRegistrasi = '69af84eb-113f-4a9c-8306-cf833593ce2c';

echo "Fetching Aktivitas for: $idRegistrasi\n";

try {
    $response = $service->getAktivitasKuliahMahasiswa($idRegistrasi);
    
    if (!$response) {
        echo "Response is null (Connection or Token error)\n";
        exit;
    }

    if (!isset($response['data'])) {
        echo "Response has no data key. Full response:\n";
        print_r($response);
        exit;
    }

    $data = $response['data'];
    $count = count($data);
    echo "Found $count records.\n";

    if ($count > 0) {
        echo "Sample Data Keys:\n";
        print_r(array_keys($data[0]));
        
        echo "\nSample Data Values:\n";
        print_r($data[0]);
    } else {
        echo "Data is empty.\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
