<?php

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new NeoFeederService();

// Try common actions for Roles/Peran
$actions = ['GetListPeran', 'GetPeran', 'GetLevelPengguna', 'GetListLevelPengguna', 'GetJenisPegawai', 'GetListJenisPegawai'];

foreach ($actions as $action) {
    echo "Testing Action: $action\n";
    $result = $service->request($action, ['limit' => 5]);
    
    if ($result && $result['error_code'] === 0) {
        echo "SUCCESS: $action\n";
        print_r($result['data']);
        break; // Found one!
    } else {
        echo "FAILED: $action (" . ($result['error_desc'] ?? 'Unknown error') . ")\n";
    }
    echo "-------------------\n";
}
