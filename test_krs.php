<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$service = new NeoFeederService();

// Let's directly query Neo Feeder for the "D3F-1" Kimia Farmasi class participants
$idKelas = 'a59f9d25-c693-4bd1-8c38-79cdf83f89b2';

echo "Querying NeoFeeder for KRS details for id_kelas_kuliah = $idKelas\n";

$filter = "id_kelas = '$idKelas'";

$response = $service->request('GetKRSMahasiswa', [
    'filter' => $filter,
    'limit' => 10,
    'offset' => 0
]);

if ($response && isset($response['data'])) {
    echo "Found " . count($response['data']) . " records in NeoFeeder.\n";
    if (count($response['data']) > 0) {
        $first = $response['data'][0];
        echo "Example Student NIM: " . ($first['nim'] ?? 'N/A') . "\n";
        echo "Example Student Nama: " . ($first['nama_mahasiswa'] ?? 'N/A') . "\n";
    }
} else {
    echo "Failed to get response from NeoFeeder or no data key.\n";
    print_r($response);
}

