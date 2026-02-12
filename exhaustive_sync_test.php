<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mahasiswa;
use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);
$offset = 0;
$limit = 500;
$errors = [];
$totalProcessed = 0;

echo "Starting Exhaustive Sync Test...\n";

while (true) {
    $res = $svc->getMahasiswa($limit, $offset);
    if (!$res || empty($res['data'])) break;
    
    foreach ($res['data'] as $item) {
        $totalProcessed++;
        try {
            $angkatan = substr((string)$item['id_periode'], 0, 4);
            Mahasiswa::updateOrCreate(
                ['id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa']],
                [
                    'id_mahasiswa' => $item['id_mahasiswa'],
                    'nim' => $item['nim'],
                    'nama' => $item['nama_mahasiswa'], 
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'tanggal_lahir' => $item['tanggal_lahir'],
                    'angkatan' => $angkatan,
                    'id_prodi' => $item['id_prodi'],
                    'status_mahasiswa' => $item['nama_status_mahasiswa'],
                    'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                ]
            );
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (!isset($errors[$msg])) {
                $errors[$msg] = 0;
            }
            $errors[$msg]++;
            
            if (count($errors) < 5 && $errors[$msg] == 1) {
                 echo "Sample Error: $msg\n";
            }
        }
    }
    
    echo "Processed $totalProcessed so far...\n";
    if (count($res['data']) < $limit) break;
    $offset += $limit;
}

echo "\nSummary of Errors:\n";
foreach ($errors as $msg => $count) {
    echo "- [$count] $msg\n";
}
echo "Final DB Count: " . Mahasiswa::count() . "\n";
