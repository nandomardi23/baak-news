<?php
use App\Services\NeoFeederService;
use App\Services\Sync\ReferenceSyncService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$log = fopen("debug_full_ref_result.txt", "w");
fwrite($log, "--- Full Reference Sync Check ---\n");

try {
    $neo = new NeoFeederService();
    $token = $neo->getToken();
    fwrite($log, "Token: " . ($token ? "OK" : "FAIL") . "\n");

    if ($token) {
        $refService = new ReferenceSyncService($neo);
        
        $subTypes = [
            'Agama', 
            'JenisTinggal', 
            'AlatTransportasi', 
            'Pekerjaan', 
            'Penghasilan', 
            'KebutuhanKhusus', 
            'Pembiayaan'
        ];

        foreach ($subTypes as $sub) {
            fwrite($log, "\nTesting Sync{$sub}...\n");
            $method = 'sync' . $sub;
            try {
                if (method_exists($refService, $method)) {
                    $res = $refService->$method();
                    fwrite($log, "Result: " . json_encode($res) . "\n");
                } else {
                    fwrite($log, "ERROR: Method {$method} does not exist!\n");
                }
            } catch (Exception $e) {
                fwrite($log, "ERROR in {$sub}: " . $e->getMessage() . "\n");
            }
        }

        // Test Wilayah specifically
        fwrite($log, "\nTesting SyncWilayah (Limit 10)...\n");
        try {
            $resWilayah = $refService->syncWilayah(0, 10);
            fwrite($log, "Result: " . json_encode($resWilayah) . "\n");
        } catch (Exception $e) {
            fwrite($log, "ERROR in Wilayah: " . $e->getMessage() . "\n");
        }
    }
} catch (Exception $e) {
    fwrite($log, "FATAL ERROR: " . $e->getMessage() . "\n");
}
fclose($log);
echo "Done.";
