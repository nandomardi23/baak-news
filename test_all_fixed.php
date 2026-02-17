<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$idSemester = '20241';
$academicSvc = app(\App\Services\Sync\AcademicSyncService::class);
$lecturerSvc = app(\App\Services\Sync\LecturerSyncService::class);

echo "=== Verifying Sync 20241 ===\n";

echo "Testing syncNilai (Limit 5)...\n";
$resN = $academicSvc->syncNilai(0, 5, $idSemester);
echo "Nilai Result: " . json_encode($resN) . "\n";
$dbNilai = \App\Models\Nilai::where('id_periode', $idSemester)->count();
echo "Nilai in DB: $dbNilai\n\n";

echo "Testing syncKrs (Limit 5)...\n";
$resK = $academicSvc->syncKrs(0, 5, $idSemester);
echo "KRS Result: " . json_encode($resK) . "\n";
$dbKrs = \App\Models\Krs::where('id_semester', $idSemester)->count();
echo "KRS Headers in DB: $dbKrs\n\n";

echo "Testing syncAjarDosen (Limit 5)...\n";
$resA = $lecturerSvc->syncAjarDosen(0, 5, $idSemester);
echo "Ajar Dosen Result: " . json_encode($resA) . "\n";
$dbAjar = \App\Models\AjarDosen::where('id_semester', $idSemester)->count();
echo "Ajar Dosen in DB: $dbAjar\n";
