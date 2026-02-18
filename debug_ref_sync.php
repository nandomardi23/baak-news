<?php
use App\Services\NeoFeederService;
use App\Services\Sync\ReferenceSyncService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$log = fopen("debug_ref_result.txt", "w");
fwrite($log, "--- Debug Ref Sync ---\n");

try {
    $neo = new NeoFeederService();
    $token = $neo->getToken();
    fwrite($log, "Token: " . ($token ? "OK" : "FAIL") . "\n");

    if ($token) {
        $refService = new ReferenceSyncService($neo);
        
        // 1. Test Agama (Simple Reference)
        fwrite($log, "1. Testing SyncAgama...\n");
        $resAgama = $refService->syncAgama();
        fwrite($log, "Result: " . json_encode($resAgama) . "\n");

        // 2. Test Wilayah (Paginated Reference)
        fwrite($log, "\n2. Testing SyncWilayah (Limit 100)...\n");
        $resWilayah = $refService->syncWilayah(0, 100);
        fwrite($log, "Result: " . json_encode($resWilayah) . "\n");
        
        // 3. Test Wilayah Count
        fwrite($log, "\n3. Testing GetCountWilayah...\n");
        $count = $refService->getCountWilayah();
        fwrite($log, "Count: " . $count . "\n");
    }
} catch (Exception $e) {
    fwrite($log, "FATAL ERROR: " . $e->getMessage() . "\n");
    echo $e->getMessage();
}
fclose($log);
echo "Done.";
