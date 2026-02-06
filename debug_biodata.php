<?php

require __DIR__.'/vendor/autoload.php';

use App\Models\Mahasiswa;
use App\Services\NeoFeederService;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '141413001';
$mhs = Mahasiswa::where('nim', $nim)->first();

if (!$mhs) {
    echo "Mahasiswa NIM $nim not found locally.\n";
    exit;
}

echo "Testing Biodata Sync for: {$mhs->nama} ({$mhs->nim})\n";
echo "ID Mahasiswa: {$mhs->id_mahasiswa}\n";

$service = app(NeoFeederService::class);
echo "Calling GetBiodataMahasiswa...\n";

$response = $service->getBiodataMahasiswa($mhs->id_mahasiswa);

if (!$response || empty($response['data'])) {
    echo "❌ API Response Empty or Error.\n";
    print_r($response);
    exit;
}

echo "✅ API returned data.\n";
$data = $response['data'][0];
print_r($data);

// Also check GetListMahasiswa for this student
echo "\nChecking GetListMahasiswa (for status comparison)...\n";
$listResponse = $service->request('GetListMahasiswa', [
    'filter' => "id_mahasiswa = '{$mhs->id_mahasiswa}'"
]);

if ($listResponse && !empty($listResponse['data'])) {
    echo "✅ GetListMahasiswa Data:\n";
    print_r($listResponse['data'][0]);
}

echo "\nChecking GetDetailMahasiswa...\n";
$detailResponse = $service->getDetailMahasiswa($mhs->id_mahasiswa);
if ($detailResponse && !empty($detailResponse['data'])) {
    echo "✅ GetDetailMahasiswa Data:\n";
    print_r($detailResponse['data']); // Usually not array of array? Or is it?
} else {
    echo "❌ GetDetailMahasiswa failed/empty.\n";
    print_r($detailResponse);
}

