<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '182411003';
$mahasiswa = \App\Models\Mahasiswa::where('nim', $nim)->first();

echo "All AktivitasKuliah for $nim:\n";
foreach ($mahasiswa->aktivitasKuliah as $ak) {
    echo "- Semester: {$ak->id_semester}, IPK: {$ak->ipk}, IPS: {$ak->ips}\n";
}

$ta = \App\Models\TahunAkademik::where('id_semester', '20252')->first();
echo "TA 20252 ID: {$ta->id}, id_semester: {$ta->id_semester}\n";

$aktivitas = $mahasiswa->aktivitasKuliah()
    ->where('id_semester', '<=', $ta->id_semester)
    ->orderBy('id_semester', 'desc')
    ->first();

if ($aktivitas) {
    echo "Found Aktivitas <= 20252: {$aktivitas->id_semester}, IPK: {$aktivitas->ipk}\n";
} else {
    echo "No Aktivitas <= 20252 found!\n";
}

// Check Dosen field name
$krs = $mahasiswa->krs()->where('id_semester', '20252')->with(['details.kelasKuliah.dosenPengajar'])->first();
if ($krs) {
    echo "Dosen for first detail:\n";
    $detail = $krs->details->first();
    foreach ($detail->kelasKuliah->dosenPengajar as $dp) {
        echo " - " . ($dp->nama ?? 'No nama') . " (id_dosen: " . $dp->id_dosen . ")\n";
    }
}
