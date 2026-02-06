<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    protected $table = 'mata_kuliah';

    protected $fillable = [
        'id_matkul',
        'kode_matkul',
        'nama_matkul',
        'sks_mata_kuliah',
        'sks_tatap_muka',
        'sks_praktek',
        'sks_praktek_lapangan',
        'sks_simulasi',
        'program_studi_id',
        'id_prodi',
    ];

    protected $casts = [
        'sks_mata_kuliah' => 'integer',
        'sks_tatap_muka' => 'integer',
        'sks_praktek' => 'integer',
        'sks_praktek_lapangan' => 'integer',
        'sks_simulasi' => 'integer',
    ];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function krsDetail(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'mata_kuliah_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'mata_kuliah_id');
    }
}
