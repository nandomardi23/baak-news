<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $table = 'program_studi';

    protected $fillable = [
        'id_prodi',
        'kode_prodi',
        'nama_prodi',
        'jenjang',
        'akreditasi',
        'tanggal_akreditasi',
        'tanggal_berakhir_akreditasi',
        'sk_akreditasi',
        'jenis_program',
        'is_active',
        'nama_alias',
    ];

    protected $casts = [
        'tanggal_akreditasi' => 'date',
        'tanggal_berakhir_akreditasi' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['nama_cetak'];

    public function getNamaCetakAttribute()
    {
        return $this->nama_alias ?: $this->nama_prodi;
    }

    public function mahasiswa(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'program_studi_id');
    }

    public function mataKuliah(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'program_studi_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeReguler($query)
    {
        return $query->where('jenis_program', 'reguler');
    }

    public function scopeRpl($query)
    {
        return $query->where('jenis_program', 'rpl');
    }
}
