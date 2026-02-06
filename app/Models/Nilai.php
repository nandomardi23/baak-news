<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    protected $table = 'nilai';

    protected $fillable = [
        'mahasiswa_id',
        'mata_kuliah_id',
        'tahun_akademik_id',
        'id_semester',
        'id_kelas_kuliah',
        'nilai_angka',
        'nilai_huruf',
        'nilai_indeks',
    ];

    protected $casts = [
        'nilai_angka' => 'decimal:2',
        'nilai_indeks' => 'decimal:2',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get bobot (SKS x Nilai Indeks)
     */
    public function getBobotAttribute(): float
    {
        $sks = $this->mataKuliah->sks_mata_kuliah ?? 0;
        return $sks * ($this->nilai_indeks ?? 0);
    }
}
