<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '182411003';
$akList = App\Models\AktivitasKuliah::where('nim', $nim)->get();
echo "Found " . $akList->count() . " aktivitas records by NIM\n";

if ($akList->count() > 0) {
    echo "AK id_registrasi_mahasiswa: " . $akList->first()->id_registrasi_mahasiswa . "\n";
}

$mhs = App\Models\Mahasiswa::where('nim', $nim)->first();
echo "MHS id_registrasi_mahasiswa: " . $mhs->id_registrasi_mahasiswa . "\n";

$byRelation = $mhs->aktivitasKuliah;
echo "Found " . $byRelation->count() . " aktivitas records by Relation\n";

if ($byRelation->count() == 0) {
    echo "Wait, is AktivitasKuliah using id_mahasiswa instead? let's check.\n";
    $akByMhsId = App\Models\AktivitasKuliah::where('id_registrasi_mahasiswa', $mhs->id_mahasiswa)->get();
    echo "Found " . $akByMhsId->count() . " by id_mahasiswa\n";
}
