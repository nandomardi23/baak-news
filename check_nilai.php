<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '182411003';
$mahasiswa = \App\Models\Mahasiswa::where('nim', $nim)->first();

$nilais = $mahasiswa->nilai()->get();
echo "Total Nilai records for $nim: " . $nilais->count() . "\n";
foreach ($nilais as $n) {
    echo "Semester: {$n->id_semester}, MK: {$n->nama_mata_kuliah}, Nilai: {$n->nilai_indeks}\n";
}
