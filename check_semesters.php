<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$maxSemester = (date('Y') + 1) . '3';
$semesters = \App\Models\TahunAkademik::where('id_semester', '<=', $maxSemester)
    ->orderBy('id_semester', 'desc')
    ->pluck('id_semester');

echo "Tahun Akademik Semesters count: " . $semesters->count() . "\n";
