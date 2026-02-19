<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsDetail extends Model
{
    protected $table = 'krs_detail';

    protected $fillable = [
        'krs_id',
        'mata_kuliah_id',
        'id_kelas_kuliah',
        'nama_kelas',
        'dosen_id',
        'nama_dosen',
        // Sync Columns
        'id_matkul',
        'kode_mata_kuliah',
        'nama_mata_kuliah',
        'sks_mata_kuliah',
        'nama_kelas_kuliah',
        'angkatan',
    ];

    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class, 'krs_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }
}
