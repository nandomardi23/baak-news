<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Dosen;
use App\Models\ProgramStudi;

echo "--- DOSEN DIAGNOSTICS ---\n";
$names = ['CIAN IBNU SINA', 'ENDANG ABDULLAH', 'MASYITAH NOVIA YANTI', 'NUR MEITY SULISTIA AYU'];
foreach ($names as $name) {
    $d = Dosen::where('nama', 'like', "%$name%")->first();
    if ($d) {
        echo "Nama: {$d->nama} | Depan: [{$d->gelar_depan}] | Belakang: [{$d->gelar_belakang}] | Full: {$d->nama_lengkap}\n";
    } else {
        echo "Dosen not found: $name\n";
    }
}

echo "\n--- PRODI DIAGNOSTICS ---\n";
$prodi = ProgramStudi::where('nama_prodi', 'like', '%Farmasi%')->get();
foreach ($prodi as $p) {
    echo "ID: {$p->id_prodi} | Nama: {$p->nama_prodi} | Cetak: {$p->nama_cetak} | Alias: {$p->alias}\n";
}
