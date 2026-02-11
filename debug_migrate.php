<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

try {
    echo "Current Columns in mahasiswa:\n";
    print_r(Schema::getColumnListing('mahasiswa'));
    
    echo "\nRunning migration...\n";
    $exitCode = Artisan::call('migrate', [
        '--path' => 'database/migrations/2026_02_11_000001_add_missing_columns_to_mahasiswa_table.php',
        '--force' => true
    ]);
    
    echo "Exit Code: $exitCode\n";
    echo "Output:\n" . Artisan::output() . "\n";
} catch (\Throwable $e) {
    echo "ERROR CLASS: " . get_class($e) . "\n";
    echo "ERROR MESSAGE: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getSql')) {
         echo "SQL: " . $e->getSql() . "\n";
    }
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}
