<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Carbon|null $tanggal_lahir
 */
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
        'rt_ortu',
        'rw_ortu',
        'kelurahan_ortu',
        'kecamatan_ortu',
        'kota_kabupaten_ortu',
        'provinsi_ortu',
        'program_studi_id',
        'id_prodi',
        'angkatan',
        'status_mahasiswa',
        'id_registrasi_mahasiswa',
        'ipk',
        'sks_tempuh',
        'dosen_wali_id',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'ipk' => 'decimal:2',
        'sks_tempuh' => 'integer',
    ];

    public function dosenWali(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_wali_id');
    }

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
     * Get formatted alamat ortu lengkap
     */
    public function getAlamatOrtuLengkapAttribute(): string
    {
        $parts = [];
        if ($this->alamat_ortu) $parts[] = $this->alamat_ortu;
        if ($this->rt_ortu && $this->rw_ortu) $parts[] = "RT {$this->rt_ortu} RW {$this->rw_ortu}";
        if ($this->kelurahan_ortu) $parts[] = $this->kelurahan_ortu;
        if ($this->kecamatan_ortu) $parts[] = $this->kecamatan_ortu;
        if ($this->kota_kabupaten_ortu) $parts[] = $this->kota_kabupaten_ortu;
        if ($this->provinsi_ortu) $parts[] = $this->provinsi_ortu;
        return implode(', ', $parts);
    }

    /**
     * Get tempat tanggal lahir
     */
    public function getTtlAttribute(): string
    {
        if ($this->tempat_lahir && $this->tanggal_lahir) {
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $monthName = $months[$this->tanggal_lahir->format('n')] ?? $this->tanggal_lahir->format('F');
            return $this->tempat_lahir . ', ' . $this->tanggal_lahir->format('d') . ' ' . $monthName . ' ' . $this->tanggal_lahir->format('Y');
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
