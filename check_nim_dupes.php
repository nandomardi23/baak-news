<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);

$offset = 0;
$limit = 1000;
$allNims = [];

echo "Checking NIMs in batches...\n";

while (true) {
    $res = $svc->getMahasiswa($limit, $offset);
    if (!$res || empty($res['data'])) break;
    
    foreach ($res['data'] as $item) {
        if (isset($item['nim'])) {
            $allNims[] = $item['nim'];
        }
    }
    
    if (count($res['data']) < $limit) break;
    $offset += $limit;
}

$counts = array_count_values($allNims);
$duplicates = array_filter($counts, function($v) { return $v > 1; });

echo "Total NIMs: " . count($allNims) . "\n";
echo "Unique NIMs: " . count(array_unique($allNims)) . "\n";
echo "NIMs with multiple entries: " . count($duplicates) . "\n";

// Show some samples of duplicates
echo "\nSample duplicates:\n";
$i = 0;
foreach ($duplicates as $nim => $count) {
    echo "NIM: $nim, Count: $count\n";
    if (++$i > 5) break;
}
