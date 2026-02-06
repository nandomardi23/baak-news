<?php

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Services\NeoFeederService;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mahasiswaId = 1663; // From screenshot URL
$mhs = Mahasiswa::find($mahasiswaId);

if (!$mhs) {
    echo "Mahasiswa ID $mahasiswaId not found.\n";
    exit;
}

echo "Testing KRS Sync for: {$mhs->nama} ({$mhs->nim})\n";
echo "ID Registrasi: {$mhs->id_registrasi_mahasiswa}\n\n";

// 1. Get Semesters first
$service = app(NeoFeederService::class);
echo "Calling GetListPerkuliahanMahasiswa (to get semesters)...\n";
$semResponse = $service->request('GetListPerkuliahanMahasiswa', [
    'filter' => "id_registrasi_mahasiswa = '{$mhs->id_registrasi_mahasiswa}'",
    'limit' => 5,
]);

if ($semResponse && !empty($semResponse['data'])) {
    $firstSem = $semResponse['data'][0];
    $idSemester = $firstSem['id_semester'];
    echo "Found Semester: $idSemester\n";

    echo "Calling GetKRSMahasiswa for Sem 20231 (Bulk Check, Limit 5)...\n";
    try {
        $krsResponse = $service->request('GetKRSMahasiswa', [
            'filter' => "id_periode = '20231'",
            'limit' => 5,
            'offset' => 0
        ]);
        
        if ($krsResponse && !empty($krsResponse['data'])) {
            echo "✅ Bulk KRS Found: " . count($krsResponse['data']) . " items.\n";
            print_r($krsResponse['data'][0]);
        } else {
            echo "❌ Bulk KRS Response Empty or Error.\n";
            print_r($krsResponse);
        }
    } catch (\Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Failed to get semesters.\n";
}



echo "\nChecking Mata Kuliah Mapping:\n";
$idMatkul = $firstItem['id_matkul'] ?? null;
$kodeMk = $firstItem['kode_mata_kuliah'] ?? $firstItem['kode_mk'] ?? null;

echo "Searching for ID Matkul: $idMatkul ... ";
if ($idMatkul) {
    $mk = MataKuliah::where('id_matkul', $idMatkul)->first();
    echo $mk ? "FOUND (ID: {$mk->id})\n" : "NOT FOUND\n";
} else {
    echo "No ID Matkul in response.\n";
}

echo "Searching for Kode MK: $kodeMk ... ";
if ($kodeMk) {
    $mk = MataKuliah::where('kode_matkul', $kodeMk)->first();
    echo $mk ? "FOUND (ID: {$mk->id})\n" : "NOT FOUND\n";
} else {
    echo "No Kode MK in response.\n";
}
