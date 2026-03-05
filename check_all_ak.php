<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = App\Models\AktivitasKuliah::all();
echo "Total records in aktivitas_kuliah: " . $all->count() . "\n";
if ($all->count() > 0) {
    echo "First 5 records:\n";
    foreach ($all->take(5) as $ak) {
        echo " - NIM: {$ak->nim}, Nama: {$ak->nama_mahasiswa}, IPK: {$ak->ipk}\n";
    }
}
