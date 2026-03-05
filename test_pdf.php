<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nim = '182411003';
$mahasiswa = \App\Models\Mahasiswa::where('nim', $nim)->first();

if (!$mahasiswa) {
    die("Mahasiswa not found.\n");
}

echo "NIM: {$mahasiswa->nim}\n";
echo "Nama: {$mahasiswa->nama}\n";
echo "IPK from Mahasiswa: {$mahasiswa->ipk}\n";

$ta = \App\Models\TahunAkademik::where('id_semester', '20252')->first();

if (!$ta) {
    die("TahunAkademik 20252 not found.\n");
}

$aktivitas = $mahasiswa->aktivitasKuliah()
    ->where('id_semester', '<=', $ta->id_semester)
    ->orderBy('id_semester', 'desc')
    ->first();

echo "aktivitasKuliah IPK: " . ($aktivitas ? $aktivitas->ipk : 'NOT FOUND') . "\n";

$krs = $mahasiswa->krs()->where('tahun_akademik_id', $ta->id)->with(['details.kelasKuliah.dosenPengajar'])->first();

if (!$krs) {
    die("KRS not found.\n");
}

echo "KRS Details:\n";
foreach ($krs->details as $d) {
    echo "- MK: {$d->mataKuliah->nama_matkul}\n";
    echo "  nama_dosen: {$d->nama_dosen}\n";
    $list = [];
    if ($d->kelasKuliah && $d->kelasKuliah->dosenPengajar->count() > 0) {
        foreach ($d->kelasKuliah->dosenPengajar as $dp) {
            $list[] = $dp->nama_dosen;
        }
    }
    echo "  dosenPengajar (from KelasKuliah): " . implode(", ", $list) . "\n";
    echo "  id_kelas_kuliah in KRS detail: {$d->id_kelas_kuliah}\n";
    echo "  KelasKuliah relation found: " . ($d->kelasKuliah ? 'Yes' : 'No') . "\n";
}
