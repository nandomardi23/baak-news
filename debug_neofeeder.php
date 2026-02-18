<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $service = new \App\Services\NeoFeederService();
    // $dict = $service->getDictionary(); // Failed
    $data = $service->getMahasiswa(1, 0); // Limit 1, Offset 0
    file_put_contents('debug_output.json', json_encode($data, JSON_PRETTY_PRINT));
    echo "Done writing to debug_output.json";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
