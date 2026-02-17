<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\NeoFeederService::class);
$idSemester = '20241';

echo "Fetching Ajar Dosen Sample...\n";
$resA = $svc->getAktivitasMengajarDosen(1, 0, "id_semester = '$idSemester'");
if ($resA && isset($resA['data'][0])) {
    echo "SUCCESS\n";
    echo "Keys: " . implode(', ', array_keys($resA['data'][0])) . "\n";
    echo "Data: " . json_encode($resA['data'][0], JSON_PRETTY_PRINT) . "\n";
} else {
    echo "FAILED: " . json_encode($resA) . "\n";
}
