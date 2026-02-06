<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAkademik extends Model
{
    protected $table = 'tahun_akademik';

    protected $fillable = [
        'id_semester',
        'nama_semester',
        'tahun',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class, 'tahun_akademik_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'tahun_akademik_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get current active semester
     */
    public static function current(): ?self
    {
        return static::active()->first();
    }
}
