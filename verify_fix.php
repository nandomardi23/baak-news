<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mahasiswa;
use App\Services\NeoFeederService;
use App\Services\NeoFeederSyncService;

// Target specific student for verification
$nim = '101013070'; 
$mhs = Mahasiswa::where('nim', $nim)->first();

if (!$mhs) {
    echo "Student $nim not found.\n";
    $mhs = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->first();
    echo "Falling back to " . $mhs->nim . "\n";
}

echo "BEFORE Sync:\n";
echo "IPK: " . $mhs->ipk . "\n";
echo "SKS: " . $mhs->sks_tempuh . "\n";
echo "Status: " . $mhs->status_mahasiswa . "\n";

echo "\nRunning Sync...\n";

try {
    $neoService = new NeoFeederService();
    $syncService = new NeoFeederSyncService($neoService);
    
    // We can't easily call syncAktivitasKuliah for just ONE student because it loops.
    // So we'll replicate the logic here for this single student to prove the mapping works.
    
    $response = $neoService->getAktivitasKuliahMahasiswa($mhs->id_registrasi_mahasiswa);
            
    if (!$response || !isset($response['data']) || empty($response['data'])) {
        echo "No data from API.\n";
    } else {
        $data = $response['data'];
        
        // LOGIC FIX REPLICATION:
        // Sort by id_semester descending
        usort($data, function($a, $b) {
            return strcmp($b['id_semester'], $a['id_semester']);
        });

        $latestData = $data[0];
        echo "Latest Semester Found: " . $latestData['id_semester'] . "\n";
        
        $updateData = [
            'status_mahasiswa' => $latestData['id_status_mahasiswa'] ?? $mhs->status_mahasiswa,
        ];
        
        // Strict mapping check
        if (isset($latestData['ipk'])) {
            $updateData['ipk'] = $latestData['ipk'];
        } else {
            echo "WARNING: 'ipk' not found in API response. Keeping existing.\n";
        }
        
        if (isset($latestData['sks_total'])) {
                $updateData['sks_tempuh'] = $latestData['sks_total'];
        } else {
            echo "WARNING: 'sks_total' not found in API response. Keeping existing.\n";
        }

        $mhs->update($updateData);
        
        echo "\nAFTER Sync:\n";
        echo "IPK: " . $mhs->ipk . "\n";
        echo "SKS: " . $mhs->sks_tempuh . "\n";
        echo "Status: " . $mhs->status_mahasiswa . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
