<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function calcIpk($nim)
{
    $mahasiswa = \App\Models\Mahasiswa::where('nim', $nim)->first();
    $aktivitas = $mahasiswa->aktivitasKuliah()
        ->orderBy('id_semester', 'desc')
        ->where('ipk', '>', 0)
        ->first();

    if ($aktivitas) {
        return $aktivitas->ipk;
    } else {
        $nilais = $mahasiswa->nilai()->with('mataKuliah')->get();
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
        return $totalSks > 0 ? ($totalBobot / $totalSks) : 0;
    }
}

echo "Dynamic IPK: " . number_format((float) calcIpk('182411003'), 2) . "\n";
