<?php

use App\Services\NeoFeederService;
use App\Models\Mahasiswa;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new NeoFeederService();

// Student: ADRAN (141413001) - Known Lulus
$nim = '141413001';
$mhs = Mahasiswa::where('nim', $nim)->first();

if (!$mhs) {
    echo "Mahasiswa $nim not found locally.\n";
    exit;
}

echo "Testing Aktivitas Kuliah for: {$mhs->nama} ($nim)\n";
echo "ID Registrasi: {$mhs->id_registrasi_mahasiswa}\n";

try {
    // Check GetListAktivitasKuliahMahasiswa using Helper
    echo "\nCalling getAktivitasKuliahMahasiswa (Helper)...\n";
    $response = $service->getAktivitasKuliahMahasiswa($mhs->id_registrasi_mahasiswa);

    if ($response && !empty($response['data'])) {
        echo "✅ Data Found: " . count($response['data']) . " records.\n";
        
        // Show first and last
        echo "\n--- First Record (Earliest) ---\n";
        print_r($response['data'][0]);

        echo "\n--- Last Record (Latest) ---\n";
        $last = end($response['data']);
        print_r($last);

        // Check key fields
        echo "\n--- Verification ---\n";
        echo "IPK Key Exists: " . (array_key_exists('ipk', $last) ? 'YES' : 'NO') . "\n";
        echo "SKS Total Key Exists: " . (array_key_exists('sks_total', $last) ? 'YES' : 'NO') . "\n";
        echo "Status Key Exists: " . (array_key_exists('id_status_mahasiswa', $last) ? 'YES' : 'NO') . "\n";
        
    } else {
        echo "❌ Response Empty or Error.\n";
        print_r($response);
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
