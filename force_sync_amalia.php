<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '182411003';
$mhs = \App\Models\Mahasiswa::where('nim', $nim)->first();

echo "Mahasiswa: {$mhs->nama} (id_registrasi: {$mhs->id_registrasi_mahasiswa})\n";

$neoFeeder = app(\App\Services\NeoFeederService::class);
$response = $neoFeeder->getAktivitasKuliahMahasiswa($mhs->id_registrasi_mahasiswa);

if (!$response || empty($response['data'])) {
    echo "No data from Neo Feeder!\n";
} else {
    echo "Found " . count($response['data']) . " records in Neo Feeder.\n";
    foreach ($response['data'] as $item) {
        echo " - Semester: {$item['id_semester']}, IPK: " . ($item['ipk'] ?? 'null') . "\n";

        \App\Models\AktivitasKuliah::updateOrCreate(
            [
                'id_registrasi_mahasiswa' => $mhs->id_registrasi_mahasiswa,
                'id_semester' => $item['id_semester']
            ],
            [
                'nim' => $item['nim'] ?? $mhs->nim,
                'nama_mahasiswa' => $item['nama_mahasiswa'] ?? $mhs->nama,
                'id_status_mahasiswa' => $item['id_status_mahasiswa'] ?? $item['status_mahasiswa'] ?? 'A',
                'ips' => $item['ips'] ?? 0,
                'ipk' => $item['ipk'] ?? 0,
                'sks_semester' => $item['sks_semester'] ?? $item['sks_smt'] ?? 0,
                'sks_total' => $item['sks_total'] ?? $item['sks_tot'] ?? $item['total_sks'] ?? 0,
                'biaya_kuliah_smt' => $item['biaya_kuliah_smt'] ?? 0,
            ]
        );
    }
}

echo "Records in local DB now: " . \App\Models\AktivitasKuliah::where('nim', $nim)->count() . "\n";
