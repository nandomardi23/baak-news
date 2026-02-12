<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$service = new NeoFeederService();

$res = $service->request('GetListMahasiswa', ['limit' => 5]);
echo "GetListMahasiswa Sample:" . PHP_EOL;
print_r($res['data'] ?? 'No data');

$res = $service->request('GetRiwayatPendidikanMahasiswa', ['limit' => 5]);
echo "GetRiwayatPendidikanMahasiswa Sample:" . PHP_EOL;
print_r($res['data'] ?? 'No data');
