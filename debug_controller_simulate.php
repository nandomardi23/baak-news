<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\SyncController;
use App\Services\Sync\ReferenceSyncService;
use App\Services\NeoFeederService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$log = fopen("debug_controller_result.txt", "w");
fwrite($log, "--- Debug Controller Simulation ---\n");

// Mock Request
$request = Request::create('/admin/sync/referensi', 'POST', [
    'sub_type' => 'Agama', // Try PascalCase just in case, though logic handles it
    'only_count' => false
]);

// Instantiate Services
try {
    $neo = new NeoFeederService();
    $refService = new ReferenceSyncService($neo);
    $controller = new SyncController();

    fwrite($log, "Calling syncReferensi...\n");
    $response = $controller->syncReferensi($request, $refService);
    
    fwrite($log, "Status: " . $response->getStatusCode() . "\n");
    $content = $response->getContent();
    fwrite($log, "Content: " . $content . "\n");
    
} catch (Exception $e) {
    fwrite($log, "FATAL: " . $e->getMessage() . "\n");
    fwrite($log, $e->getTraceAsString());
}
fclose($log);
echo "Done.";
