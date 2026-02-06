<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pejabat extends Model
{
    protected $table = 'pejabat';

    protected $fillable = [
        'nama',
        'nip',
        'nidn',
        'nik',
        'jabatan',
        'pangkat_golongan',
        'gelar_depan',
        'gelar_belakang',
        'periode_awal',
        'periode_akhir',
        'tandatangan_path',
        'is_active',
    ];

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get full name with titles
     */
    public function getNamaLengkapAttribute(): string
    {
        $parts = [];
        if ($this->gelar_depan) {
            $parts[] = $this->gelar_depan;
        }
        $parts[] = $this->nama;
        if ($this->gelar_belakang) {
            $parts[] = $this->gelar_belakang;
        }
        return implode(' ', $parts);
    }

    /**
     * Scope for active pejabat
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get pejabat by jabatan
     */
    public function scopeByJabatan($query, string $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }
}
