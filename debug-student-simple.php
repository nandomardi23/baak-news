<?php

use App\Models\Mahasiswa;
use App\Models\Krs;
use App\Models\Nilai;

$nim = '242413016';
$mhs = Mahasiswa::where('nim', $nim)->first();

if ($mhs) {
    echo "Student: " . $mhs->nama . "\n";
    echo "IPK from DB: " . $mhs->ipk . "\n";
    echo "SKS from DB: " . $mhs->sks_tempuh . "\n";
    
    $krs = Krs::where('mahasiswa_id', $mhs->id)->count();
    $nilai = Nilai::where('mahasiswa_id', $mhs->id)->count();
    
    echo "KRS Records: " . $krs . "\n";
    echo "Nilai Records: " . $nilai . "\n";
} else {
    echo "Student not found.\n";
}
