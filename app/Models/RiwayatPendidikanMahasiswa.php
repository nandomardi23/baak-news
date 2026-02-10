<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikanMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pendidikan_mahasiswa';

    protected $fillable = [
        'id_registrasi_mahasiswa',
        'id_mahasiswa',
        'nim',
        'nama_mahasiswa',
        'id_jenis_daftar',
        'nama_jenis_daftar',
        'id_jalur_daftar',
        'nama_jalur_daftar',
        'id_periode_masuk',
        'tanggal_daftar',
        'id_perguruan_tinggi_asal',
        'nama_perguruan_tinggi_asal',
        'id_prodi_asal',
        'nama_prodi_asal',
        'sks_diakui',
        'biaya_masuk',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }
}
