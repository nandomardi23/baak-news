<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$neo = app(App\Services\NeoFeederService::class);
$res = $neo->getKonversiKampusMerdeka(1, 0);
if($res && isset($res['data'][0])) {
    echo "KEYS:" . json_encode(array_keys($res['data'][0]));
} else {
    echo "FAIL";
}
