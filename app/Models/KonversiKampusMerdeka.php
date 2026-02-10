<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KonversiKampusMerdeka extends Model
{
    protected $table = 'konversi_kampus_merdeka';
    protected $guarded = ['id'];

    public function anggota()
    {
        return $this->belongsTo(AnggotaAktivitasMahasiswa::class, 'id_anggota', 'id_anggota');
    }

    public function aktivitas()
    {
        return $this->belongsTo(AktivitasMahasiswa::class, 'id_aktivitas', 'id_aktivitas');
    }
}
