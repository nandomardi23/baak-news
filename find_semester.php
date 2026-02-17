<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function get_latest($table, $column = 'id_semester') {
    try {
        return \DB::table($table)->orderBy($column, 'desc')->value($column);
    } catch (\Exception $e) {
        return "TABLE ERROR on $table.$column: " . $e->getMessage();
    }
}

$res = "Latest Semester in krs (header): " . get_latest('krs') . "\n";
$res .= "Latest Semester in nilai: " . get_latest('nilai') . "\n";
$res .= "Latest created_at in ajar_dosen: " . get_latest('ajar_dosen', 'created_at') . "\n";

try {
    $semesters = \DB::table('krs')->distinct()->pluck('id_semester')->toArray();
    $res .= "All Distinct Semesters in KRS: " . implode(', ', $semesters) . "\n";
} catch (\Exception $e) {
    $res .= "Distinct Error: " . $e->getMessage() . "\n";
}

file_put_contents(__DIR__ . '/find_results.txt', $res);
echo "Results written to find_results.txt\n";
