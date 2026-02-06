<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mahasiswa;
use App\Services\NeoFeederService;

try {
    $mhs = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->first();
    if (!$mhs) {
        echo "No student with id_registrasi_mahasiswa found.\n";
        exit;
    }

    echo "Testing with student: " . $mhs->nim . " (ID Reg: " . $mhs->id_registrasi_mahasiswa . ")\n";

    $service = new NeoFeederService();
    // We check getAktivitasKuliahMahasiswa
    $response = $service->getAktivitasKuliahMahasiswa($mhs->id_registrasi_mahasiswa);

    if (isset($response['data'])) {
        echo "Found " . count($response['data']) . " records.\n";
        if (count($response['data']) > 0) {
            echo "First record keys:\n";
            print_r(array_keys($response['data'][0]));
            
            echo "Last record (raw):\n";
            print_r(end($response['data']));
        }
    } else {
        echo "No data in response or error.\n";
        print_r($response);
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
