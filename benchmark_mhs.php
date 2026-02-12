<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);

$offset = 0;
$limit = 500;
$totalFetched = 0;
$allIds = [];

echo "Fetching Mahasiswa in batches of $limit...\n";

while (true) {
    $res = $svc->getMahasiswa($limit, $offset);
    if (!$res || empty($res['data'])) break;
    
    $batch = count($res['data']);
    $totalFetched += $batch;
    
    foreach ($res['data'] as $item) {
        $allIds[] = $item['id_registrasi_mahasiswa'] ?? 'NO_REG_ID';
    }
    
    echo "Offset: $offset, Batch: $batch, Total so far: $totalFetched\n";
    
    if ($batch < $limit) break;
    $offset += $limit;
    
    // Safety break
    if ($totalFetched > 10000) break;
}

echo "\nFinal Total Fetched: $totalFetched\n";
$uniqueIds = array_unique($allIds);
echo "Unique Reg IDs: " . count($uniqueIds) . "\n";
echo "Duplicate Reg IDs: " . ($totalFetched - count($uniqueIds)) . "\n";
