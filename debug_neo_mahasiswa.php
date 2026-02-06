<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $neoFeeder = app(\App\Services\NeoFeederService::class);
    
    // Ambil 1 data mahasiswa dari list dulu untuk dapet ID
    $listResponse = $neoFeeder->getMahasiswa(null, 1, 0); 
    
    if (isset($listResponse['data']) && count($listResponse['data']) > 0) {
        $mhsList = $listResponse['data'][0];
        $idMahasiswa = $mhsList['id_mahasiswa'];
        echo "Testing GetBiodataMahasiswa for ID: $idMahasiswa\n";
        
        $response = $neoFeeder->getBiodataMahasiswa($idMahasiswa);
        
        if (isset($response['data']) && count($response['data']) > 0) {
            $mhs = $response['data'][0];
        echo "Data Mahasiswa Sample:\n";
        echo "=======================\n";
        echo "Nama: " . ($mhs['nama_mahasiswa'] ?? 'N/A') . "\n";
        echo "NIM: " . ($mhs['nim'] ?? 'N/A') . "\n";
        echo "\nField Data Orang Tua (Raw):\n";
        echo "---------------------------\n";
        
        // Print key related to ayah/ibu/orangtua
        $parentKeys = array_filter(array_keys($mhs), function($k) {
            return stripos($k, 'ayah') !== false || 
                   stripos($k, 'ibu') !== false || 
                   stripos($k, 'ortu') !== false ||
                   stripos($k, 'orang_tua') !== false ||
                   stripos($k, 'wali') !== false;
        });
        
        foreach ($parentKeys as $key) {
            echo "$key: " . ($mhs[$key] ?? 'NULL') . "\n";
        }
        
        echo "\nFull Keys Available:\n";
        print_r(array_keys($mhs));
        } else {
            echo "Biodata kosong.\n";
            print_r($response);
        }
    } else {
        echo "Tidak ada data mahasiswa ditemukan.\n";
        print_r($listResponse);
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
