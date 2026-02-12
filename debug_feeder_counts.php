<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$service = new NeoFeederService();

function printCount($service, $action) {
    try {
        $res = $service->request($action, []);
        $count = $res['data'] ?? 'Error';
        echo "$action: " . json_encode($count) . PHP_EOL;
    } catch (\Exception $e) {
        echo "$action failed: " . $e->getMessage() . PHP_EOL;
    }
}

printCount($service, 'GetCountMahasiswa');
printCount($service, 'GetCountRiwayatPendidikanMahasiswa');
printCount($service, 'GetCountMahasiswaLulusDO');
