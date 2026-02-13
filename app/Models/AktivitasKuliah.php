<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasKuliah extends Model
{
    protected $table = 'aktivitas_kuliah';

    protected $fillable = [
        'id_registrasi_mahasiswa',
        'id_semester',
        'nim',
        'nama_mahasiswa',
        'id_status_mahasiswa',
        'ips',
        'ipk',
        'sks_semester',
        'sks_total',
        'biaya_kuliah_smt',
    ];

    /**
     * Relasi ke Mahasiswa
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_registrasi_mahasiswa', 'id_registrasi_mahasiswa');
    }

    /**
     * Relasi ke Semester
     */
    public function semester()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_semester', 'id_semester');
    }
}
