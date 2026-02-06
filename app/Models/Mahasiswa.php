<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = [
        'id_mahasiswa',
        'nim',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'dusun',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kota_kabupaten',
        'provinsi',
        'kode_pos',
        'telepon',
        'no_hp',
        'email',
        'nik',
        'nisn',
        'npwp',
        'kewarganegaraan',
        'nama_ayah',
        'pekerjaan_ayah',
        'nama_ibu',
        'pekerjaan_ibu',
        'alamat_ortu',
        'program_studi_id',
        'id_prodi',
        'angkatan',
        'status_mahasiswa',
        'id_registrasi_mahasiswa',
        'ipk',
        'sks_tempuh',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'ipk' => 'decimal:2',
        'sks_tempuh' => 'integer',
    ];

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    public function suratPengajuan(): HasMany
    {
        return $this->hasMany(SuratPengajuan::class, 'mahasiswa_id');
    }

    public function krs(): HasMany
    {
        return $this->hasMany(Krs::class, 'mahasiswa_id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'mahasiswa_id');
    }

    /**
     * Get formatted alamat lengkap
     */
    public function getAlamatLengkapAttribute(): string
    {
        $parts = [];
        if ($this->alamat) $parts[] = $this->alamat;
        if ($this->rt && $this->rw) $parts[] = "RT {$this->rt} RW {$this->rw}";
        if ($this->kelurahan) $parts[] = $this->kelurahan;
        if ($this->kecamatan) $parts[] = $this->kecamatan;
        if ($this->kota_kabupaten) $parts[] = $this->kota_kabupaten;
        if ($this->provinsi) $parts[] = $this->provinsi;
        if ($this->kode_pos) $parts[] = $this->kode_pos;
        return implode(', ', $parts);
    }

    /**
     * Get tempat tanggal lahir
     */
    public function getTtlAttribute(): string
    {
        if ($this->tempat_lahir && $this->tanggal_lahir) {
            return $this->tempat_lahir . ', ' . $this->tanggal_lahir->format('d F Y');
        }
        return '-';
    }

    public function scopeActive($query)
    {
        // Include all statuses that are allowed to request letters/documents
        // A=Aktif, L/1/3=Lulus, C/2=Cuti, N/6=Non-Aktif, U=Menunggu Ujian
        return $query->whereIn('status_mahasiswa', [
            'Aktif', 'A', 'aktif', 
            'L', '1', '3', 'lulus', 
            'C', '2', 'cuti', 
            'N', '6', 'non-aktif',
            'U', 'menunggu ujian'
        ]);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nim', 'like', "%{$search}%");
        });
    }

    /**
     * Get human readable status
     */
    public function getStatusTextAttribute(): string
    {
        $status = strtoupper((string) $this->status_mahasiswa);
        
        // Map of standard PDDIKTI codes (both letters and numbers)
        $map = [
            'A' => 'Aktif',
            'C' => 'Cuti',
            'D' => 'Drop Out',
            'K' => 'Keluar',
            'L' => 'Lulus',
            'N' => 'Non-Aktif',
            'G' => 'Sedang Double Degree',
            'U' => 'Menunggu Ujian',
            '1' => 'Lulus',
            '2' => 'Cuti',
            '3' => 'Lulus',
            '4' => 'Drop Out',
            '5' => 'Keluar',
            '6' => 'Non-Aktif',
            '7' => 'Sedang Double Degree',
            'M' => 'Meninggal Dunia',
        ];

        return $map[$status] ?? $status;
    }
}
