<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use App\Services\NeoFeederSyncService;
use App\Models\Mahasiswa;

// 1. Setup Service
$neoService = new NeoFeederService();
$syncService = new NeoFeederSyncService($neoService);

// 2. Define Semester to Sync (e.g., 20231 or 20241 - pick one that has data)
// We'll try 20231 (Ganjil 2023) or 20241. 
// Ideally user selects this in UI, but for test we hardcode.
$semesterId = '20231'; 

echo "TEST: Running Bulk Sync for Semester $semesterId\n";
echo "This will fetch ALL students active in $semesterId at once.\n";

$startTime = microtime(true);

try {
    // Only fetch 50 records for safety in test
    $result = $syncService->syncAktivitasKuliah($semesterId, 0, 50);

    $duration = round(microtime(true) - $startTime, 2);

    echo "\n=== RESULT ({$duration}s) ===\n";
    echo "Total Data from API: " . ($result['total'] ?? 0) . "\n";
    echo "Synced to DB: " . ($result['synced'] ?? 0) . "\n";
    echo "Skipped (No Match Local): " . ($result['skipped'] ?? 0) . "\n";
    
    if (!empty($result['errors'])) {
        echo "Errors (" . count($result['errors']) . "):\n";
        print_r(array_slice($result['errors'], 0, 3)); 
    }
    
    echo "Has More: " . ($result['has_more'] ? 'YES' : 'NO') . "\n";

} catch (\Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
