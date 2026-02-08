<?php

use App\Services\NeoFeederService;
use App\Models\KrsDetail;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get a class that has id_kelas_kuliah but NO nama_dosen (prioritize these to debug the issue)
$detail = KrsDetail::whereNotNull('id_kelas_kuliah')
    ->whereNull('nama_dosen')
    ->inRandomOrder()
    ->first();

if (!$detail) {
    // Fallback to any class
    $detail = KrsDetail::whereNotNull('id_kelas_kuliah')->inRandomOrder()->first();
}

if (!$detail) {
    echo "No KrsDetail with id_kelas_kuliah found.\n";
    exit;
}

echo "Checking Class: {$detail->nama_kelas} (ID: {$detail->id_kelas_kuliah})\n";
echo "Current Nama Dosen: " . ($detail->nama_dosen ?? 'NULL') . "\n";

$service = app(NeoFeederService::class);
$response = $service->request('GetDosenPengajarKelasKuliah', [
    'filter' => "id_kelas_kuliah = '{$detail->id_kelas_kuliah}'",
    'limit' => 5
]);

if ($response && !empty($response['data'])) {
    echo "--- DATA FOUND ---\n";
    foreach ($response['data'] as $item) {
        echo "id_dosen: " . ($item['id_dosen'] ?? 'NULL') . "\n";
        echo "nama_dosen: " . ($item['nama_dosen'] ?? 'NULL') . "\n";
        echo "sks_substansi_total: " . ($item['sks_substansi_total'] ?? 'NULL') . "\n";
        echo "rencana_tatap_muka: " . ($item['rencana_tatap_muka'] ?? 'NULL') . "\n";
        echo "tatap_muka_realisasi: " . ($item['tatap_muka_realisasi'] ?? 'NULL') . "\n";
        echo "------------------\n";
    }
} else {
    echo "--- NO DATA FOUND ---\n";
    print_r($response);
}
