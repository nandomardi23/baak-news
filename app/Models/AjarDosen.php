<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AjarDosen extends Model
{
    protected $table = 'ajar_dosen';
    protected $fillable = [
        'id_aktivitas_mengajar',
        'id_registrasi_dosen',
        'id_dosen',
        'id_kelas_kuliah',
        'id_substansi',
        'sks_substansi_total',
        'rencana_tatap_muka',
        'realisasi_tatap_muka',
        'id_jenis_evaluasi',
    ];
}
