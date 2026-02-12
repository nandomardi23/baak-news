<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\StudentSyncService;
use App\Models\Mahasiswa;

$syncService = app(StudentSyncService::class);

echo "Finding a student to test biodata sync...\n";
$mahasiswa = Mahasiswa::first();

if (!$mahasiswa) {
    echo "No student found. Running syncMahasiswa batch first...\n";
    $mhsResult = $syncService->syncMahasiswa(0, 10);
    echo "Sync Mahasiswa Result: " . $mhsResult['synced'] . " items synced.\n";
    if (!empty($mhsResult['errors'])) {
        echo "ERRORS:\n";
        print_r($mhsResult['errors']);
    }
    $mahasiswa = Mahasiswa::first();
}

if ($mahasiswa) {
    echo "Syncing biodata for: " . $mahasiswa->nama . " (NIM: " . $mahasiswa->nim . ")...\n";
    $result = $syncService->syncBiodata($mahasiswa);
    echo "RESULT: " . ($result ?? 'FAILED/NULL') . "\n";

    if ($result === null) {
        // Additional debugging: request raw biodata response and print
        $neo = app(\App\Services\NeoFeederService::class);
        $filter = "id_mahasiswa='{$mahasiswa->id_mahasiswa}'";
        echo "Attempting raw API call: GetBiodataMahasiswa with filter={$filter}\n";
        $raw = $neo->request('GetBiodataMahasiswa', ['filter' => $filter]);
        echo "Raw response: \n";
        var_export($raw);
        echo "\n";
    }

    $mahasiswa->refresh();
    echo "\nVERIFYING FIELDS:\n";
    echo "ID Agama: " . $mahasiswa->id_agama . "\n";
    echo "Kebutuhan Khusus: " . $mahasiswa->nama_kebutuhan_khusus_mahasiswa . "\n";
    echo "Ayah: " . $mahasiswa->nama_ayah . " | NIK: " . $mahasiswa->nik_ayah . "\n";
    echo "Ibu: " . $mahasiswa->nama_ibu . " | NIK: " . $mahasiswa->nik_ibu . "\n";
    
} else {
    echo "ERROR: Still no student found.\n";
}
