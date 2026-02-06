<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Krs extends Model
{
    protected $table = 'krs';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'id_semester',
        'id_registrasi_mahasiswa',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'krs_id');
    }

    /**
     * Get total SKS
     */
    public function getTotalSksAttribute(): int
    {
        return $this->details()->with('mataKuliah')->get()
            ->sum(fn($detail) => $detail->mataKuliah->sks_mata_kuliah ?? 0);
    }
}
