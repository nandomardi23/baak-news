<?php
use App\Services\NeoFeederService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Debug Wilayah Count ---\n";

try {
    $neo = new NeoFeederService();
    
    // 1. Try generic GetRecordCount (common in some Feeders)
    echo "1. Testing GetRecordCount for 'wilayah'...\n";
    $res1 = $neo->requestQuick('GetRecordCount', ['table' => 'wilayah']);
    print_r($res1);

    // 2. Try GetCountWilayah again with error catching
    echo "\n2. Testing GetCountWilayah...\n";
    $res2 = $neo->requestQuick('GetCountWilayah', []); 
    print_r($res2);

} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
echo "Done.\n";
