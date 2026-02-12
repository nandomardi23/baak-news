<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);

echo "--- Student Counts Diagnostics ---\n";

$countMhs = $svc->getCountMahasiswa();
echo "GetCountMahasiswa: " . json_encode($countMhs) . "\n";

// GetListMahasiswa usually returns records, but sometimes it returns a count if asked specifically
// However, our service calls it with limit/offset.
// Let's check a sample of GetListMahasiswa
$listMhs = $svc->getMahasiswa(1, 0);
echo "GetListMahasiswa (sample 1): " . (isset($listMhs['data']) ? "Found Data" : "No Data") . " Error Code: " . ($listMhs['error_code'] ?? 'N/A') . "\n";

// Check Riwayat Pendidikan (Enrollment) - this is what id_registrasi_mahasiswa refers to
$resRP = $svc->request('GetCountRiwayatPendidikanMahasiswa', []);
echo "GetCountRiwayatPendidikanMahasiswa: " . json_encode($resRP) . "\n";

$resBio = $svc->request('GetCountProfilMahasiswa', []);
echo "GetCountProfilMahasiswa: " . json_encode($resBio) . "\n";

echo "\n--- DB Current Counts ---\n";
echo "Mahasiswa DB Count: " . \App\Models\Mahasiswa::count() . "\n";
