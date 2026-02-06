<?php

use App\Services\NeoFeederService;
use App\Models\Mahasiswa;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new NeoFeederService();

// Student: ADRAN (141413001) 
$nim = '141413001';
$mhs = Mahasiswa::where('nim', $nim)->first();

if (!$mhs) {
    echo "Mahasiswa $nim not found locally.\n";
    exit;
}

echo "Testing Aktivitas Kuliah for: {$mhs->nama} ($nim)\n";
echo "ID Registrasi: {$mhs->id_registrasi_mahasiswa}\n";


echo "\n--- PROBING ENDPOINTS ---\n";

$endpoints = [
    'GetListAktivitasKuliahMahasiswa',
    'GetAktivitasKuliahMahasiswa',
    'GetPerkuliahanMahasiswa',
    'GetDetailAktivitasKuliahMahasiswa'
];

foreach ($endpoints as $act) {
    echo "\nTesting: $act\n";
    try {
        $tStart = microtime(true);
        $response = $service->request($act, [
            'filter' => "id_registrasi_mahasiswa = '{$mhs->id_registrasi_mahasiswa}'",
            'limit' => 1
        ]);
        $duration = microtime(true) - $tStart;
        
        if ($response && !isset($response['error_code'])) {
            echo "✅ Success ($duration s). Count: " . count($response['data'] ?? []) . "\n";
            if (!empty($response['data'])) {
                print_r($response['data'][0]);
            }
        } else {
            echo "❌ Failed ($duration s). Code: " . ($response['error_code'] ?? 'Unknown') . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
}

