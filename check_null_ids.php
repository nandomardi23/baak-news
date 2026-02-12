<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);

$offset = 0;
$limit = 1000;
$nullRegIds = 0;
$nullMhsIds = 0;
$total = 0;

echo "Checking for NULL IDs...\n";

while (true) {
    $res = $svc->getMahasiswa($limit, $offset);
    if (!$res || empty($res['data'])) break;
    
    foreach ($res['data'] as $item) {
        $total++;
        if (empty($item['id_registrasi_mahasiswa'])) $nullRegIds++;
        if (empty($item['id_mahasiswa'])) $nullMhsIds++;
    }
    
    if (count($res['data']) < $limit) break;
    $offset += $limit;
}

echo "Total Records: $total\n";
echo "Null Registration IDs: $nullRegIds\n";
echo "Null Mahasiswa IDs: $nullMhsIds\n";
