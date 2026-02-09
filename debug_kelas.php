<?php

use App\Models\KelasKuliah;
use App\Models\KrsDetail;

// Check specific class by kode
$kelas = KelasKuliah::where('kode_mata_kuliah', 'DKP130')->first();

if ($kelas) {
    echo "Kelas ID: " . $kelas->id . "\n";
    echo "id_kelas_kuliah: " . $kelas->id_kelas_kuliah . "\n";
    
    // Direct query on KrsDetail
    $count = KrsDetail::where('id_kelas_kuliah', $kelas->id_kelas_kuliah)->count();
    echo "KrsDetail Count (direct): " . $count . "\n";
    
    // Via relation
    echo "KrsDetail Count (relation): " . $kelas->krsDetails()->count() . "\n";
    
    // Sample 3 KrsDetails for this class
    $samples = $kelas->krsDetails()->take(3)->get();
    echo "\nSample KrsDetails:\n";
    foreach ($samples as $s) {
        echo "  - KrsDetail ID: " . $s->id . ", krs_id: " . $s->krs_id . "\n";
    }
} else {
    echo "Kelas DKP130 not found.\n";
}
