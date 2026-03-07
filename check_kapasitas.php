<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = app(\App\Services\NeoFeederService::class);
$kelas = \App\Models\KelasKuliah::whereNotNull('id_kelas_kuliah')->first();

if ($kelas) {
    $detail = $service->getDetailKelasKuliah($kelas->id_kelas_kuliah);
    $list = $service->getKelasKuliah($kelas->id_semester ?? '20231', 1, 0, "id_kelas_kuliah='{$kelas->id_kelas_kuliah}'");

    file_put_contents('out_kapasitas.json', json_encode([
        'detail_keys' => array_keys($detail['data'][0] ?? []),
        'list_keys' => array_keys($list['data'][0] ?? []),
    ], JSON_PRETTY_PRINT));
}
