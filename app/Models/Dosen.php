<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dosen extends Model
{
    protected $table = 'dosen';

    protected $fillable = [
        'id_dosen',
        'nidn',
        'nip',
        'nama',
        'gelar_depan',
        'gelar_belakang',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'jabatan_fungsional',
        'id_status_aktif',
        'status_aktif',
        'program_studi_id',
        'id_prodi',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function getNamaLengkapAttribute(): string
    {
        $parts = [];
        if ($this->gelar_depan) $parts[] = $this->gelar_depan;
        $parts[] = $this->nama;
        if ($this->gelar_belakang) $parts[] = $this->gelar_belakang;
        return implode(' ', $parts);
    }

    public function scopeActive($query)
    {
        return $query->where('id_status_aktif', 1);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nidn', 'like', "%{$search}%")
              ->orWhere('nip', 'like', "%{$search}%");
        });
    }
}
