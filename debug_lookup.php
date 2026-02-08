<?php

use App\Services\NeoFeederService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(NeoFeederService::class);

echo "\n--- Testing GetJenisAktivitasMahasiswa ---\n";
$response = $service->request('GetJenisAktivitasMahasiswa', []);

if ($response && isset($response['data'])) {
    foreach ($response['data'] as $item) {
        echo $item['id_jenis_aktivitas_mahasiswa'] . ": " . $item['nama_jenis_aktivitas_mahasiswa'] . "\n";
    }
} else {
    echo "Failed to fetch Jenis Aktivitas.\n";
}
