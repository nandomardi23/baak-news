<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$svc = app(NeoFeederService::class);

echo "--- Testing GetProdi ---\n";
$resProdi = $svc->getProdi(10, 0);
echo "error_code: " . ($resProdi['error_code'] ?? 'N/A') . "\n";
echo "error_desc: " . ($resProdi['error_desc'] ?? 'N/A') . "\n";
echo "Count: " . (isset($resProdi['data']) ? count($resProdi['data']) : '0') . "\n\n";

echo "--- Testing GetListDosen ---\n";
$resDosen = $svc->getDosen(10, 0);
echo "error_code: " . ($resDosen['error_code'] ?? 'N/A') . "\n";
echo "error_desc: " . ($resDosen['error_desc'] ?? 'N/A') . "\n";
echo "Count: " . (isset($resDosen['data']) ? count($resDosen['data']) : '0') . "\n";
if (isset($resDosen['error_code']) && $resDosen['error_code'] != 0) {
    echo "Trying alternative act 'GetDosen'...\n";
    $altRes = $svc->request('GetDosen', ['limit' => 10, 'offset' => 0]);
    echo "GetDosen error_code: " . ($altRes['error_code'] ?? 'N/A') . "\n";
    echo "GetDosen Count: " . (isset($altRes['data']) ? count($altRes['data']) : '0') . "\n";
}
