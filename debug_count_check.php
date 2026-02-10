<?php

use App\Services\NeoFeederService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$neo = app(NeoFeederService::class);

echo "=== CHECKING GET COUNT FOR GENERAL REFERENCES (RETRY) ===\n";

// List of actions to test
$tests = [
    'GetAgama' => ['limit' => 1, 'offset' => 0],
    'GetCountAgama' => [],
    'GetPekerjaan' => ['limit' => 1, 'offset' => 0],
    'GetCountPekerjaan' => [],
    'GetWilayah' => ['limit' => 1, 'offset' => 0], // Known to work
    'GetCountWilayah' => [], // Known to work
];

foreach ($tests as $action => $params) {
    echo "Testing {$action}... ";
    try {
        $start = microtime(true);
        $res = $neo->request($action, $params);
        $duration = round(microtime(true) - $start, 2);
        
        if (isset($res['error_code']) && $res['error_code'] !== 0) {
             echo "API ERROR ({$duration}s): " . $res['error_desc'] . "\n";
        } elseif (($res['error_code'] ?? -1) === 0) {
             $count = 0;
             if(isset($res['data'])) {
                 if(is_array($res['data'])) $count = count($res['data']);
                 else $count = 1; // It might be a single object or count value
             }
             echo "OK ({$duration}s). Data count/value: " . json_encode($res['data']) . "\n";
        } else {
             echo "UNKNOWN ({$duration}s): " . json_encode($res) . "\n";
        }
    } catch (\Exception $e) {
        echo "EXCEPTION: " . $e->getMessage() . "\n";
    }
}
