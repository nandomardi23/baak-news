<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$r = DB::select('SHOW CREATE TABLE mahasiswa');
$sql = $r[0]->{'Create Table'};
file_put_contents('create_table.sql', $sql);
echo "SQL saved to create_table.sql\n";
echo "First 500 chars:\n" . substr($sql, 0, 500) . "\n";
