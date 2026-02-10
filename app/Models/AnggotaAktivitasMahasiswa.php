<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaAktivitasMahasiswa extends Model
{
    protected $table = 'anggota_aktivitas_mahasiswa';
    protected $guarded = ['id'];

    public function aktivitas()
    {
        return $this->belongsTo(AktivitasMahasiswa::class, 'id_aktivitas', 'id_aktivitas');
    }
}
