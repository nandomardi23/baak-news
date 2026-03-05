<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '182411003';
$mahasiswa = \App\Models\Mahasiswa::where('nim', $nim)->first();
$ta_id = '20252';

// Calculate IPK up to this semester
$nilais = $mahasiswa->nilai()
    ->where('id_semester', '<=', $ta_id)
    ->with('mataKuliah')
    ->get();

$mkGrades = [];
foreach ($nilais as $n) {
    if (!$n->mata_kuliah_id || $n->nilai_indeks === null)
        continue;

    // Keep highest grade for each course
    if (!isset($mkGrades[$n->mata_kuliah_id]) || $mkGrades[$n->mata_kuliah_id]['indeks'] < $n->nilai_indeks) {
        $mkGrades[$n->mata_kuliah_id] = [
            'sks' => $n->mataKuliah->sks_mata_kuliah ?? $n->sks_mata_kuliah ?? 0,
            'indeks' => $n->nilai_indeks
        ];
    }
}

$totalSks = 0;
$totalBobot = 0;

foreach ($mkGrades as $grade) {
    $totalSks += $grade['sks'];
    $totalBobot += ($grade['sks'] * $grade['indeks']);
}

$calculatedIpk = $totalSks > 0 ? ($totalBobot / $totalSks) : 0;

echo "Total SKS: $totalSks\n";
echo "Total Bobot: $totalBobot\n";
echo "Calculated IPK: " . number_format($calculatedIpk, 2) . "\n";
