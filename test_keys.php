<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\NeoFeederService::class);
$idSemester = '20241';

$results = "=== Fetching Nilai Sample ===\n";
$resN = $svc->getNilaiBySemester($idSemester, 1, 0);
if ($resN && isset($resN['data'][0])) {
    $results .= "Nilai Keys: " . implode(', ', array_keys($resN['data'][0])) . "\n";
    $results .= "Sample data: " . json_encode($resN['data'][0], JSON_PRETTY_PRINT) . "\n";
} else {
    $results .= "Nilai failed or empty: " . json_encode($resN) . "\n";
}

$results .= "\n=== Fetching Ajar Dosen Sample ===\n";
$resA = $svc->getAktivitasMengajarDosen(1, 0, "id_semester = '$idSemester'");
if ($resA && isset($resA['data'][0])) {
    $results .= "Ajar Dosen Keys: " . implode(', ', array_keys($resA['data'][0])) . "\n";
    $results .= "Sample data: " . json_encode($resA['data'][0], JSON_PRETTY_PRINT) . "\n";
} else {
    $results .= "Ajar Dosen failed or empty: " . json_encode($resA) . "\n";
}

$results .= "\n=== Fetching KRS Sample (id_semester filter) ===\n";
$resK = $svc->requestQuick('GetListKRSMahasiswa', ['filter' => "id_semester = '$idSemester'", 'limit' => 1]);
if ($resK && isset($resK['data'][0])) {
    $results .= "KRS Keys: " . implode(', ', array_keys($resK['data'][0])) . "\n";
    $results .= "Sample data: " . json_encode($resK['data'][0], JSON_PRETTY_PRINT) . "\n";
} else {
    $results .= "KRS failed or empty: " . json_encode($resK) . "\n";
}

file_put_contents(__DIR__ . '/keys_results.txt', $results);
echo "Results written to keys_results.txt\n";
