<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Setting;
use App\Models\Mahasiswa;
use App\Services\NeoFeederService;

echo "=== Test IPK Field Inspection ===\n\n";

// Get specific mahasiswa from screenshot
$mhs = Mahasiswa::where('nim', '172311001')->first();

if (!$mhs) {
    echo "Mahasiswa 172311001 not found locally.\n";
    $mhs = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->first();
}

if (!$mhs) {
    echo "No mahasiswa found with id_registrasi.\n";
    exit;
}

echo "Testing for: {$mhs->nama} ({$mhs->nim})\n";
echo "ID Registrasi: {$mhs->id_registrasi_mahasiswa}\n\n";

// Test GetListAktivitasKuliahMahasiswa (Correct endpoint for Status/IPK?)
echo "Testing GetListAktivitasKuliahMahasiswa...\n";

$service = app(NeoFeederService::class);
// $client = $service->getClient(); // Method not available, use request directly
// We can't access client directly easily, let's reflect or just create new client
// Or simpler: modify NeoFeederService temporarily or just use raw curl/guzzle here.

// Let's use raw request via service helper if possible, or just re-instantiate
$token = \App\Models\Setting::getValue('neo_feeder_token');
if (!$token) {
    // Force login
    try {
        $service->getToken();
        $token = \App\Models\Setting::getValue('neo_feeder_token');
    } catch (\Exception $e) {
        echo "Login failed: " . $e->getMessage();
        exit;
    }
}

$endpoints = [
    'GetListAktivitasKuliahMahasiswa',
    'GetAktivitasKuliahMahasiswa',
    'GetDetailAktivitasKuliahMahasiswa'
];

foreach ($endpoints as $act) {
    echo "\nTrying {$act}...\n";
    try {
        $res = $service->request($act, [
            'filter' => "id_registrasi_mahasiswa = '{$mhs->id_registrasi_mahasiswa}'"
        ]);
        
        if ($res && !empty($res['data'])) {
            echo "✅ SUCCESS! Found " . count($res['data']) . " records.\n";
            $last = end($res['data']);
            print_r($last);
            echo "\nIPK: " . ($last['ipk'] ?? 'NULL') . "\n";
            echo "Status: " . ($last['id_status_mahasiswa'] ?? 'NULL') . "\n";
            break; 
        } else {
             echo "❌ Empty or Failed\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

