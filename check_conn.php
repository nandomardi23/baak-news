<?php

use App\Services\NeoFeederService;
use Illuminate\Support\Facades\Log;

echo "=== QUICK CONNECTION CHECK ===\n";
$start = microtime(true);

try {
    $neo = app(NeoFeederService::class);
    echo "1. Requesting Token... ";

    $token = $neo->getToken();

    $duration = round(microtime(true) - $start, 2);

    if ($token) {
        echo "OK! (Duration: {$duration}s)\n";
        echo "Token: " . substr($token, 0, 10) . "...\n";
    } else {
        echo "FAILED! (Duration: {$duration}s)\n";
        echo "Check laravel.log for 'Neo Feeder GetToken' errors.\n";
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
