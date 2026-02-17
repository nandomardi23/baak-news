<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\NeoFeederService::class);
$idSemester = '20241';

echo "Testing Ajar Dosen with id_periode filter...\n";
$resP = $svc->requestQuick('GetAktivitasMengajarDosen', ['filter' => "id_periode = '$idSemester'", 'limit' => 1]);
if ($resP && isset($resP['data'][0])) {
    echo "id_periode SUCCESS\n";
    echo "Keys: " . implode(', ', array_keys($resP['data'][0])) . "\n";
} else {
    echo "id_periode FAILED: " . json_encode($resP) . "\n";
}

echo "\nTesting Ajar Dosen with id_semester filter...\n";
$resS = $svc->requestQuick('GetAktivitasMengajarDosen', ['filter' => "id_semester = '$idSemester'", 'limit' => 1]);
if ($resS && isset($resS['data'][0])) {
    echo "id_semester SUCCESS\n";
    echo "Keys: " . implode(', ', array_keys($resS['data'][0])) . "\n";
} else {
    echo "id_semester FAILED: " . json_encode($resS) . "\n";
}
