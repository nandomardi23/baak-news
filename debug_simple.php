<?php
use App\Services\NeoFeederService;
use App\Services\Sync\ReferenceSyncService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$log = fopen("debug_result.txt", "w");
fwrite($log, "--- Debug Start ---\n");

try {
    $neo = new NeoFeederService();
    $token = $neo->getToken();
    fwrite($log, "Token: " . ($token ? "OK" : "FAIL") . "\n");

    if ($token) {
        $ref = new ReferenceSyncService($neo);
        $count = $ref->getCountProdi();
        fwrite($log, "Prodi Count: " . $count . "\n");
        
        // Mock Sync
        $data = $neo->getProdi(10, 0);
        fwrite($log, "Prodi Data Count: " . count($data['data'] ?? []) . "\n");
    }
} catch (Exception $e) {
    fwrite($log, "Error: " . $e->getMessage() . "\n");
}
fclose($log);
echo "Done.";
