<?php

use App\Services\NeoFeederService;
use App\Models\Mahasiswa;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mahasiswa = Mahasiswa::inRandomOrder()->first();

if (!$mahasiswa) {
    echo "No mahasiswa found.\n";
    exit;
}

echo "Checking Mahasiswa: {$mahasiswa->nama} ({$mahasiswa->nim})\n";
$service = app(NeoFeederService::class);

// 1. GetListMahasiswa
echo "1. GetListMahasiswa:\n";
$response = $service->request('GetListMahasiswa', [
    'filter' => "nim = '{$mahasiswa->nim}'",
    'limit' => 1
]);
if ($response && isset($response['data'][0])) {
    $data = $response['data'][0];
    echo "   id_dosen_wali: " . ($data['id_dosen_wali'] ?? 'NULL') . "\n";
    echo "   nama_dosen_wali: " . ($data['nama_dosen_wali'] ?? 'NULL') . "\n";
} else {
    echo "   NO DATA\n";
}

// 2. GetDetailMahasiswa
echo "2. GetDetailMahasiswa:\n";
if ($mahasiswa->id_mahasiswa) {
    $response = $service->request('GetDetailMahasiswa', [
        'filter' => "id_mahasiswa = '{$mahasiswa->id_mahasiswa}'",
        'limit' => 1
    ]);
    if ($response && isset($response['data'][0])) {
        $data = $response['data'][0];
        echo "   id_dosen_wali: " . ($data['id_dosen_wali'] ?? 'NULL') . "\n";
        echo "   nama_dosen_wali: " . ($data['nama_dosen_wali'] ?? 'NULL') . "\n";
    } else {
        echo "   NO DATA (or empty data)\n";
    }
} else {
    echo "   Skipped (No id_mahasiswa)\n";
}

// 3. GetBiodataMahasiswa
echo "3. GetBiodataMahasiswa:\n";
if ($mahasiswa->id_mahasiswa) {
    $response = $service->request('GetBiodataMahasiswa', [
        'filter' => "id_mahasiswa = '{$mahasiswa->id_mahasiswa}'",
        'limit' => 1
    ]);
    if ($response && isset($response['data'][0])) {
        $data = $response['data'][0];
        echo "   id_dosen_wali: " . ($data['id_dosen_wali'] ?? 'NULL') . "\n";
        echo "   nama_dosen_wali: " . ($data['nama_dosen_wali'] ?? 'NULL') . "\n";
    } else {
        echo "   NO DATA\n";
    }
}
