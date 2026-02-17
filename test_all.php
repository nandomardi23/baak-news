<?php
// Test just the API endpoints (not the full sync) to quickly identify what works
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$output = "";
$svc = app(\App\Services\NeoFeederService::class);

// 1. Test Mahasiswa batch 2 (API only)
$output .= "--- GetListMahasiswa(limit=10, offset=2000) ---\n";
$start = microtime(true);
try {
    $r = $svc->getMahasiswa(10, 2000);
    $cnt = isset($r['data']) ? count($r['data']) : 'N/A';
    $output .= "OK: count=$cnt\n";
    if (isset($r['data'][0])) {
        $output .= "First item tanggal_lahir: " . ($r['data'][0]['tanggal_lahir'] ?? 'NULL') . "\n";
    }
} catch(\Exception $e) { $output .= "ERROR: " . substr($e->getMessage(), 0, 300) . "\n"; }
$output .= "Time: " . round(microtime(true) - $start, 2) . "s\n\n";

// 2. Test KRS API (picking first semester from DB)
$firstSemester = \App\Models\TahunAkademik::orderBy('id_semester', 'desc')->value('id_semester');
$output .= "--- First semester in DB: $firstSemester ---\n";
$output .= "--- GetCountKRSMahasiswa(filter=$firstSemester) ---\n";
$start = microtime(true);
try {
    $r = $svc->request('GetCountKRSMahasiswa', ['filter' => "id_periode = '$firstSemester'"]);
    $output .= "Result: " . json_encode($r) . "\n";
} catch(\Exception $e) { $output .= "ERROR: " . substr($e->getMessage(), 0, 300) . "\n"; }
$output .= "Time: " . round(microtime(true) - $start, 2) . "s\n\n";

// 3. Test KRS data fetch
$output .= "--- GetKRSMahasiswa(semester=$firstSemester, limit=5) ---\n";
$start = microtime(true);
try {
    $r = $svc->getKrsBySemester($firstSemester, 5, 0);
    $cnt = isset($r['data']) ? count($r['data']) : 'N/A';
    $err = $r['error_code'] ?? 'N/A';
    $desc = $r['error_desc'] ?? 'N/A';
    $output .= "data count: $cnt, error_code: $err, error_desc: $desc\n";
    if (isset($r['data'][0])) {
        $output .= "Keys: " . implode(', ', array_keys($r['data'][0])) . "\n";
    }
} catch(\Exception $e) { $output .= "ERROR: " . substr($e->getMessage(), 0, 300) . "\n"; }
$output .= "Time: " . round(microtime(true) - $start, 2) . "s\n\n";

// 4. Test Nilai API
$output .= "--- GetCountNilaiPerkuliahanKelas(semester=$firstSemester) ---\n";
$start = microtime(true);
try {
    $r = $svc->request('GetCountNilaiPerkuliahanKelas', ['filter' => "id_semester = '$firstSemester'"]);
    $output .= "Result: " . json_encode($r) . "\n";
} catch(\Exception $e) { $output .= "ERROR: " . substr($e->getMessage(), 0, 300) . "\n"; }
$output .= "Time: " . round(microtime(true) - $start, 2) . "s\n\n";

// 5. Test Nilai data fetch
$output .= "--- GetNilaiBySemester(semester=$firstSemester, limit=5) ---\n";
$start = microtime(true);
try {
    $r = $svc->getNilaiBySemester($firstSemester, 5, 0);
    $cnt = isset($r['data']) ? count($r['data']) : 'N/A';
    $err = $r['error_code'] ?? 'N/A';
    $desc = $r['error_desc'] ?? 'N/A';
    $output .= "data count: $cnt, error_code: $err, error_desc: $desc\n";
} catch(\Exception $e) { $output .= "ERROR: " . substr($e->getMessage(), 0, 300) . "\n"; }
$output .= "Time: " . round(microtime(true) - $start, 2) . "s\n\n";

// 6. Test Ajar Dosen API
$output .= "--- GetCountAktivitasMengajarDosen ---\n";
$start = microtime(true);
try {
    $r = $svc->getCountAktivitasMengajarDosen('');
    $output .= "Result: " . json_encode($r) . "\n";
} catch(\Exception $e) { $output .= "ERROR: " . substr($e->getMessage(), 0, 300) . "\n"; }
$output .= "Time: " . round(microtime(true) - $start, 2) . "s\n\n";

$output .= "--- GetAktivitasMengajarDosen(limit=5) ---\n";
$start = microtime(true);
try {
    $r = $svc->getAktivitasMengajarDosen(5, 0, '');
    $cnt = isset($r['data']) ? count($r['data']) : 'N/A';
    $err = $r['error_code'] ?? 'N/A';
    $desc = $r['error_desc'] ?? 'N/A';
    $output .= "data count: $cnt, error_code: $err, error_desc: $desc\n";
} catch(\Exception $e) { $output .= "ERROR: " . substr($e->getMessage(), 0, 300) . "\n"; }
$output .= "Time: " . round(microtime(true) - $start, 2) . "s\n\n";

// 7. Check how many semesters are in DB 
$semCount = \App\Models\TahunAkademik::count();
$output .= "Total semesters in DB: $semCount\n";

file_put_contents(__DIR__ . '/test_all_results.txt', $output);
echo "Done!\n";
