<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AktivitasMahasiswa extends Model
{
    protected $table = 'aktivitas_mahasiswa';
    protected $guarded = ['id'];

    public function anggota()
    {
        return $this->hasMany(AnggotaAktivitasMahasiswa::class, 'id_aktivitas', 'id_aktivitas');
    }
}
