<?php
// Diagnostic script for failing syncs
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function log_diag($msg) {
    echo "[" . date('H:i:s') . "] $msg\n";
    file_put_contents(__DIR__ . '/diag_results.txt', "[" . date('H:i:s') . "] $msg\n", FILE_APPEND);
}

file_put_contents(__DIR__ . '/diag_results.txt', "=== Diagnostic Results ===\n");

$svc = app(\App\Services\NeoFeederService::class);
$studentSvc = app(\App\Services\Sync\StudentSyncService::class);
$academicSvc = app(\App\Services\Sync\AcademicSyncService::class);
$lecturerSvc = app(\App\Services\Sync\LecturerSyncService::class);

// 1. Diagnostics Mahasiswa Batch 2
log_diag("Testing Mahasiswa offset 2000 with limit 10...");
try {
    $res = $svc->getMahasiswa(10, 2000);
    if ($res && isset($res['data'])) {
        log_diag("Mahasiswa batch 2 fetch success. Count: " . count($res['data']));
    } else {
        log_diag("Mahasiswa batch 2 fetch FAILED. Result keys: " . implode(', ', array_keys($res ?? [])));
    }
} catch(\Exception $e) {
    log_diag("Mahasiswa batch 2 ERROR: " . $e->getMessage());
}

// 2. Diagnostics Biodata
$sampleStudent = \App\Models\Mahasiswa::first();
if ($sampleStudent) {
    log_diag("Testing Biodata for student: " . $sampleStudent->nim . " (ID: " . $sampleStudent->id_mahasiswa . ")");
    try {
        // Calling NeoFeeder directly to see the raw response
        $response = $svc->getBiodataMahasiswa($sampleStudent->id_mahasiswa);
        if ($response && isset($response['data'])) {
            log_diag("Biodata raw response received. Data count: " . count($response['data']));
            if (count($response['data']) > 0) {
                log_diag("Sample biodata keys: " . implode(', ', array_keys($response['data'][0])));
            }
        } else {
            log_diag("Biodata raw request FAILED: " . json_encode($response));
        }

        // Now test the service method
        $res = $studentSvc->syncBiodata($sampleStudent);
        log_diag("Biodata sync result: " . ($res ?? 'NULL (Exception occurred)'));
    } catch(\Exception $e) {
        log_diag("Biodata sync EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

// 3. Diagnostics KRS/Nilai (Focus on a valid semester)
$testSemesters = ['20241', '20242'];
foreach ($testSemesters as $idSemester) {
    log_diag("--- Checking for Semester: $idSemester ---");

    log_diag("--- Checking for Semester: $idSemester ---");
    
    try {
        log_diag("Checking KRS for $idSemester...");
        $r = $svc->requestQuick('GetCountKRSMahasiswa', ['filter' => "id_periode = '$idSemester'"]);
        $count = extractCountLocal($r['data'] ?? []);
        log_diag("KRS count for $idSemester: $count");
        
        log_diag("Checking Nilai for $idSemester...");
        $rn = $svc->requestQuick('GetCountNilaiPerkuliahanKelas', ['filter' => "id_semester = '$idSemester'"]);
        $countN = extractCountLocal($rn['data'] ?? []);
        log_diag("Nilai count for $idSemester: $countN");

        log_diag("Checking Aktivitas Kuliah for $idSemester...");
        $ra = $svc->requestQuick('GetCountAktivitasKuliahMahasiswa', ['filter' => "id_semester = '$idSemester'"]);
        $countA = extractCountLocal($ra['data'] ?? []);
        log_diag("Aktivitas Kuliah count for $idSemester: $countA");

        if ($count > 0 || $countN > 0) {
            log_diag("Found semester with data: $idSemester.");
            break; 
        }
    } catch (\Exception $e) {
        log_diag("Check ERROR for $idSemester: " . $e->getMessage());
    }
}

// Add local helper to the script
function extractCountLocal($data) {
    if (is_array($data) && isset($data[0])) {
        return $data[0]['count'] ?? (isset($data['count']) ? $data['count'] : 0);
    } elseif (is_numeric($data)) {
        return (int)$data;
    }
    return 0;
}




// 5. Diagnostics Ajar Dosen
$idSemester = '20241';
log_diag("Testing Ajar Dosen for semester: $idSemester ...");
try {
    $res = $svc->getAktivitasMengajarDosen(5, 0, "id_semester = '$idSemester'");
    if ($res && isset($res['data'])) {
        log_diag("Ajar Dosen API raw response: " . count($res['data']) . " records.");
        if (count($res['data']) > 0) {
            log_diag("Ajar Dosen keys: " . implode(', ', array_keys($res['data'][0])));
        }
    } else {
        log_diag("Ajar Dosen raw request FAILED: " . json_encode($res));
    }
} catch(\Exception $e) {
    log_diag("Ajar Dosen raw ERROR: " . $e->getMessage());
}

// 6. Diagnostics Nilai Sample
try {
    log_diag("Fetching Nilai sample for $idSemester...");
    $res = $svc->getNilaiBySemester($idSemester, 5, 0);
    if ($res && isset($res['data'][0])) {
        log_diag("Nilai Sample Keys: " . implode(', ', array_keys($res['data'][0])));
    }
} catch (\Exception $e) {
    log_diag("Nilai sample ERROR: " . $e->getMessage());
}


// 7. Testing KRS with alternative filter
try {
    log_diag("Checking KRS for $idSemester with id_semester filter...");
    $r = $svc->requestQuick('GetCountKRSMahasiswa', ['filter' => "id_semester = '$idSemester'"]);
    $count = extractCountLocal($r['data'] ?? []);
    log_diag("KRS (id_semester filter) count for $idSemester: $count");
} catch (\Exception $e) {
    log_diag("KRS alt filter ERROR: " . $e->getMessage());
}



log_diag("Diagnostics complete.");
