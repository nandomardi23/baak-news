<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$s = app()->make(App\Services\NeoFeederSyncService::class);
echo "Syncing dosen...\n";
$res = $s->syncDosen(0, 500);
print_r($res);
echo "\nDone!\n";
