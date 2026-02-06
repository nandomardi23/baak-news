<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$service = new NeoFeederService();

echo "1. Testing GetToken...\n";
$start = microtime(true);
$token = $service->getToken();
$end = microtime(true);
echo "Token: " . ($token ? "OK" : "FAILED") . " (" . round($end - $start, 2) . "s)\n";

if (!$token) exit;

echo "\n2. Testing GetProdi (Simple List)...\n";
$start = microtime(true);
$prodi = $service->getProdi();
$end = microtime(true);
echo "Prodi: " . (isset($prodi['error_code']) && $prodi['error_code'] == 0 ? "OK" : "FAILED") . " (" . round($end - $start, 2) . "s)\n";

echo "\n3. Testing GetListAktivitasKuliahMahasiswa (The problematic one)...\n";
$mhs = \App\Models\Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->first();
if ($mhs) {
    echo "Target: " . $mhs->nim . "\n";
    $start = microtime(true);
    // reduce timeout for this test if possible, but we can't easily injection it. 
    // We'll just wait.
    $akt = $service->getAktivitasKuliahMahasiswa($mhs->id_registrasi_mahasiswa);
    $end = microtime(true);
    echo "Aktivitas: " . (isset($akt['error_code']) && $akt['error_code'] == 0 ? "OK" : "FAILED") . " (" . round($end - $start, 2) . "s)\n";
}
