<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\NeoFeederService;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;

// Helper function untuk tracking memory
function showMemory($label = '') {
    $mem = memory_get_usage(true) / 1024 / 1024;
    $peak = memory_get_peak_usage(true) / 1024 / 1024;
    echo sprintf("[Memory] %s: %.2f MB (Peak: %.2f MB)\n", $label, $mem, $peak);
}

// Helper function untuk membersihkan memory
function cleanMemory() {
    gc_collect_cycles();
}

echo "=== Test Neo Feeder API Endpoints ===\n\n";
showMemory('Initial');

$service = app(NeoFeederService::class);

// Test 1: Get a sample mahasiswa
$mhs = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->first();
$semester = TahunAkademik::orderBy('id_semester', 'desc')->first();

if (!$mhs) {
    echo "ERROR: No mahasiswa with id_registrasi_mahasiswa found\n";
    exit(1);
}

echo "Testing with:\n";
echo "- Mahasiswa: {$mhs->nim} (id_reg: {$mhs->id_registrasi_mahasiswa})\n";
echo "- Semester: {$semester->nama_semester} (id: {$semester->id_semester})\n\n";

// Test: Get Aktivitas Kuliah (alternative endpoint)
echo "=== Test GetListPerkuliahanMahasiswa (Aktivitas) ===\n";
$startTime = microtime(true);
$response = $service->getAktivitasKuliahMahasiswa($mhs->id_registrasi_mahasiswa);
$endTime = microtime(true);
echo "Time: " . round($endTime - $startTime, 2) . "s\n";

// Output summary instead of full JSON to save memory
$dataCount = isset($response['data']) ? count($response['data']) : 0;
echo "Records found: {$dataCount}\n";
if ($dataCount > 0 && $dataCount <= 5) {
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} elseif ($dataCount > 5) {
    echo "First record sample:\n";
    echo json_encode($response['data'][0] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
showMemory('After Aktivitas');

// Clean up response to free memory
unset($response);
cleanMemory();
showMemory('After cleanup');

echo "\n";

// Test: Get KRS (with semester filter)
echo "=== Test GetKRSMahasiswa (with semester filter) ===\n";
$startTime = microtime(true);
$response = $service->getKrsMahasiswa($mhs->id_registrasi_mahasiswa, $semester->id_semester);
$endTime = microtime(true);
echo "Time: " . round($endTime - $startTime, 2) . "s\n";

// Output summary instead of full JSON to save memory
$dataCount = isset($response['data']) ? count($response['data']) : 0;
echo "Records found: {$dataCount}\n";
if ($dataCount > 0 && $dataCount <= 5) {
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} elseif ($dataCount > 5) {
    echo "First record sample:\n";
    echo json_encode($response['data'][0] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
showMemory('After KRS');

// Final cleanup
unset($response);
cleanMemory();

echo "\n=== Test Complete ===\n";
showMemory('Final');
