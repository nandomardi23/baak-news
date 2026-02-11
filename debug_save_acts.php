<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NeoFeederService;

$neoFeeder = app(NeoFeederService::class);

echo "Running ListAction and saving to act_list.txt...\n";

try {
    $response = $neoFeeder->request('ListAction', []);
    if ($response && isset($response['data'])) {
        $acts = array_column($response['data'], 'act');
        sort($acts);
        file_put_contents('act_list.txt', implode("\n", $acts));
        echo "SUCCESS! " . count($acts) . " actions saved.\n";
    } else {
        echo "FAILED: " . ($response['error_desc'] ?? 'Unknown error') . "\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
