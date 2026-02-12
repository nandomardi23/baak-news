<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Sync\ReferenceSyncService;
use App\Services\Sync\LecturerSyncService;
use App\Services\Sync\StudentSyncService;

$refSvc = app(ReferenceSyncService::class);
$lecSvc = app(LecturerSyncService::class);
$stdSvc = app(StudentSyncService::class);

echo "--- Verifying Prodi Sync ---\n";
$prodiResult = $refSvc->syncProdi(0, 10);
echo "Synced: " . $prodiResult['synced'] . "\n";
echo "Errors: " . count($prodiResult['errors']) . "\n";
if (!empty($prodiResult['errors'])) print_r($prodiResult['errors']);

echo "\n--- Verifying Dosen Sync ---\n";
$dosenResult = $lecSvc->syncDosen(0, 10);
echo "Synced: " . $dosenResult['synced'] . "\n";
echo "Errors: " . count($dosenResult['errors']) . "\n";
if (!empty($dosenResult['errors'])) print_r($dosenResult['errors']);

echo "\n--- Verifying Mahasiswa Sync (Sample) ---\n";
$mhsResult = $stdSvc->syncMahasiswa(0, 5);
echo "Synced: " . $mhsResult['synced'] . "\n";
echo "Errors: " . count($mhsResult['errors']) . "\n";
if (!empty($mhsResult['errors'])) print_r($mhsResult['errors']);

echo "\n--- Final Database Counts ---\n";
echo "Prodi: " . \App\Models\ProgramStudi::count() . "\n";
echo "Dosen: " . \App\Models\Dosen::count() . "\n";
echo "Mahasiswa: " . \App\Models\Mahasiswa::count() . "\n";
