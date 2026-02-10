<?php
use App\Services\NeoFeederService;
$neo = app(NeoFeederService::class);
echo "Testing GetCountWilayah...\n";
$start = microtime(true);
try {
    $res = $neo->request('GetCountWilayah', []);
    $duration = round(microtime(true) - $start, 2);
    echo "Result ({$duration}s): " . json_encode($res) . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
