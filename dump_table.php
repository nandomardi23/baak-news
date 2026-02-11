<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select('DESCRIBE mahasiswa');
file_put_contents('table_structure.json', json_encode($cols, JSON_PRETTY_PRINT));
echo "Dumped " . count($cols) . " columns to table_structure.json\n";
