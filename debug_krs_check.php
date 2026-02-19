<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;

$neoFeeder = new NeoFeederService();

// 1. Find a semester that likely has data (e.g., 20232 or 20241)
$semester = '20241'; // Try 20241 (Ganjil 2024)
echo "Testing Semester: $semester\n";

// 2. Fetch KRS data for this semester
try {
    $response = $neoFeeder->getKrsBySemester($semester, 5); // Limit 5
    $data = $response['data'] ?? [];
    
    echo "Fetched " . count($data) . " records.\n";
    
    if (empty($data)) {
        echo "No data for $semester. Trying 20232...\n";
        $semester = '20232';
        $response = $neoFeeder->getKrsBySemester($semester, 5);
        $data = $response['data'] ?? [];
        echo "Fetched " . count($data) . " records for 20232.\n";
    }

    if (!empty($data)) {
        $sample = $data[0];
        echo "Sample Record:\n";
        print_r($sample);
        
        // 3. Check Mahasiswa Mapping
        $idReg = $sample['id_registrasi_mahasiswa'];
        echo "Checking ID Reg Mahasiswa: $idReg\n";
        
        $mhs = Mahasiswa::where('id_registrasi_mahasiswa', $idReg)->first();
        if ($mhs) {
            echo "FOUND Local Mahasiswa: " . $mhs->nama_mahasiswa . " (ID: $mhs->id)\n";
        } else {
            echo "NOT FOUND Local Mahasiswa for this ID!\n";
            // Check if any mahasiswa has this NIM
            $nim = $sample['nim'];
            $mhsNim = Mahasiswa::where('nim', $nim)->first();
            if ($mhsNim) {
                echo "But found Mahasiswa by NIM ($nim): " . $mhsNim->nama_mahasiswa . "\n";
                echo "Local ID Reg is: " . $mhsNim->id_registrasi_mahasiswa . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
