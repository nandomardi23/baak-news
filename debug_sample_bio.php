<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Fetching sample student ID...\n";

try {
    $mhsResponse = $neoFeeder->request('GetListMahasiswa', ['limit' => 1]);
    if ($mhsResponse && isset($mhsResponse['data']) && count($mhsResponse['data']) > 0) {
        $idMahasiswa = $mhsResponse['data'][0]['id_mahasiswa'];
        echo "Found ID: $idMahasiswa\n";
        
        echo "Fetching biodata...\n";
        $bioResponse = $neoFeeder->request('GetBiodataMahasiswa', ['filter' => "id_mahasiswa = '$idMahasiswa'"]);
        if ($bioResponse && isset($bioResponse['data']) && count($bioResponse['data']) > 0) {
            $bio = $bioResponse['data'][0];
            echo "SPECIAL NEEDS KEYS:\n";
            echo "id_kebutuhan_khusus_mahasiswa: " . ($bio['id_kebutuhan_khusus_mahasiswa'] ?? 'N/A') . "\n";
            echo "nama_kebutuhan_khusus_mahasiswa: " . ($bio['nama_kebutuhan_khusus_mahasiswa'] ?? 'N/A') . "\n";
            echo "FULL BIODATA keys: " . implode(', ', array_keys($bio)) . "\n";
        } else {
            echo "Bio failed\n";
        }
    } else {
        echo "No student found\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
