<?php
$idReg = \App\Models\Mahasiswa::where('nim', '242413016')->value('id_registrasi_mahasiswa');
if ($idReg) {
    echo "ID Reg: $idReg\n";
    $service = app(\App\Services\NeoFeederService::class);
    $res = $service->getAktivitasKuliahMahasiswa($idReg);
    print_r($res);
} else {
    echo "Student not found or no ID Reg.\n";
}
