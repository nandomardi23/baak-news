<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = App\Models\AktivitasKuliah::all();
$found = 0;
foreach ($all as $ak) {
    if (trim($ak->nim) === '182411003') {
        echo "- Found: {$ak->nim} (len: " . strlen($ak->nim) . "), Semester: {$ak->id_semester}, IPK: {$ak->ipk}, id_reg: {$ak->id_registrasi_mahasiswa}\n";
        $found++;
    }
}
echo "Total matched by trim: " . $found . "\n";

$byLike = App\Models\AktivitasKuliah::where('nim', 'like', '%182411003%')->get();
echo "Total matched by LIKE: " . $byLike->count() . "\n";
