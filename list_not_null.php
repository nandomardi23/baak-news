<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "NOT NULL Columns in mahasiswa:\n";
$columns = DB::select("SHOW COLUMNS FROM mahasiswa WHERE `Null` = 'NO'");
foreach ($columns as $c) {
    echo "{$c->Field} | {$c->Type} | Default: {$c->Default}\n";
}
