<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

$neoFeeder = new NeoFeederService();
try {
    // Try to get data for a recent semester
    $response = $neoFeeder->getKrsBySemester('20241', 1, 0); 
    if (!empty($response['data'])) {
        $keys = array_keys($response['data'][0]);
        sort($keys);
        print_r($keys);
        
        echo "\nHas id_kelas_kuliah? " . (array_key_exists('id_kelas_kuliah', $response['data'][0]) ? 'YES' : 'NO') . "\n";
    } else {
        echo "No data\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
