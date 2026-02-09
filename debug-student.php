<?php

use App\Models\Mahasiswa;
use App\Models\Krs;
use App\Models\Nilai;

$nim = '242413016';
$mhs = Mahasiswa::where('nim', $nim)->first();

echo "Mahasiswa:\n";
if ($mhs) {
    echo "ID: " . $mhs->id . "\n";
    echo "NIM: " . $mhs->nim . "\n";
    echo "Nama: " . $mhs->nama . "\n";
    echo "IPK: " . $mhs->ipk . "\n";
    echo "SKS: " . $mhs->sks_tempuh . "\n";
    echo "ID Reg: " . $mhs->id_registrasi_mahasiswa . "\n";
    
    $krsCount = Krs::where('mahasiswa_id', $mhs->id)->count();
    echo "KRS Count (relation): " . $krsCount . "\n";
    
    $nilaiCount = Nilai::where('mahasiswa_id', $mhs->id)->count();
    echo "Nilai Count (relation): " . $nilaiCount . "\n";

    // Check if data exists but not linked?
    // Assuming KRS table has `id_registrasi_mahasiswa` or similar? 
    // Let's check one KRS record columns if count is 0
    if ($krsCount == 0) {
        echo "Checking generic KRS table structure...\n";
        $dummy = Krs::first();
        if ($dummy) {
            print_r($dummy->getAttributes());
        } else {
            echo "KRS Table is empty.\n";
        }
    }

} else {
    echo "Mahasiswa not found.\n";
}
