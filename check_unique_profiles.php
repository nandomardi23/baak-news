<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);

$offset = 0;
$limit = 1000;
$allMhsIds = [];
$allNims = [];

echo "Checking unique profiles...\n";

while (true) {
    $res = $svc->getMahasiswa($limit, $offset);
    if (!$res || empty($res['data'])) break;
    
    foreach ($res['data'] as $item) {
        if (isset($item['id_mahasiswa'])) {
            $allMhsIds[] = $item['id_mahasiswa'];
        }
        if (isset($item['nim'])) {
            $allNims[] = $item['nim'];
        }
    }
    
    if (count($res['data']) < $limit) break;
    $offset += $limit;
}

echo "Total Records: " . count($allMhsIds) . "\n";
echo "Unique id_mahasiswa: " . count(array_unique($allMhsIds)) . "\n";
echo "Unique nim: " . count(array_unique($allNims)) . "\n";
echo "DB Mahasiswa Count: " . \App\Models\Mahasiswa::count() . "\n";
