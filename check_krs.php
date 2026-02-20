<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$semesters = \App\Models\AktivitasKuliah::select('id_semester')
    ->distinct()
    ->orderBy('id_semester', 'desc')
    ->pluck('id_semester');

$semesterIndex = 3;
if (!isset($semesters[$semesterIndex])) {
    echo "Semester index $semesterIndex not found.\n";
    exit;
}

$currentSemester = $semesters[$semesterIndex];
echo "Semester at index 3: $currentSemester\n";

$neoFeeder = app(\App\Services\NeoFeederService::class);

$filter = "id_periode = '{$currentSemester}'";
echo "Fetching count...\n";
$countResponse = $neoFeeder->requestQuick('GetCountKRSMahasiswa', ['filter' => $filter]);
$totalAll = $countResponse['data']['total'] ?? $countResponse['data'] ?? 0;
echo "Total KRS for $currentSemester: " . json_encode($totalAll) . "\n";

echo "Fetching data...\n";
$response = $neoFeeder->getKrsBySemester($currentSemester, 1000, 0);
$data = $response['data'] ?? [];
echo "Fetched count: " . count($data) . "\n";

$batchCount = count($data);
$nextOffset = 0 + $batchCount;
$hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === 1000)) && ($batchCount > 0);
echo "Has more: " . ($hasMore ? 'true' : 'false') . "\n";
echo "Next offset: " . $nextOffset . "\n";
