<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;
use App\Models\Mahasiswa;

$neoFeeder = app(NeoFeederService::class);

echo "--- DATA COUNTS ---\n";

try {
    $countMhs = $neoFeeder->getCountMahasiswa();
    $totalNeo = 0;
    if ($countMhs && isset($countMhs['data'])) {
        // extractCount logic from BaseSyncService: return is_array($data) ? ($data[0]['count'] ?? 0) : $data;
        $data = $countMhs['data'];
        $totalNeo = is_array($data) ? ($data[0]['count'] ?? (isset($data['count']) ? $data['count'] : 0)) : $data;
    }
    echo "Total Mahasiswa (Neo Feeder API): " . $totalNeo . "\n";
} catch (\Exception $e) {
    echo "Error GetCountMahasiswa: " . $e->getMessage() . "\n";
}

$localCount = Mahasiswa::count();
echo "Total Mahasiswa (Local DB): " . $localCount . "\n";

echo "Difference: " . ($totalNeo - $localCount) . "\n";
echo "--- END ---\n";
