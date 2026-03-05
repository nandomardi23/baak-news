<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '182411003';
$mahasiswa = \App\Models\Mahasiswa::where('nim', $nim)->first();

$n = $mahasiswa->nilai()->first();
echo "First Nilai:\n";
echo "ID Semester: " . $n->id_semester . "\n";
echo "Mata Kuliah ID: " . $n->mata_kuliah_id . "\n";
echo "SKS from Nilai: " . $n->sks_mata_kuliah . "\n";
if ($n->mataKuliah) {
    echo "SKS from MataKuliah relation: " . $n->mataKuliah->sks_mata_kuliah . "\n";
} else {
    echo "No MataKuliah relation found!\n";
}
echo "Nilai Indeks: " . $n->nilai_indeks . "\n";
