<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KalenderAkademik extends Model
{
    protected $table = 'kalender_akademik';

    protected $fillable = [
        'judul',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'tahun_akademik_id',
        'warna',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public const JENIS_OPTIONS = [
        'pendaftaran' => ['label' => 'Pendaftaran', 'color' => '#10B981'],
        'perkuliahan' => ['label' => 'Perkuliahan', 'color' => '#3B82F6'],
        'ujian' => ['label' => 'Ujian', 'color' => '#EF4444'],
        'libur' => ['label' => 'Libur', 'color' => '#F59E0B'],
        'lainnya' => ['label' => 'Lainnya', 'color' => '#6B7280'],
    ];

    // ==================== RELATIONSHIPS ====================

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    // ==================== SCOPES ====================

    public function scopeTahun($query, $tahunAkademikId)
    {
        if ($tahunAkademikId) {
            return $query->where('tahun_akademik_id', $tahunAkademikId);
        }
        return $query;
    }

    public function scopeJenis($query, $jenis)
    {
        if ($jenis) {
            return $query->where('jenis', $jenis);
        }
        return $query;
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_mulai', '>=', now()->startOfDay())
            ->orderBy('tanggal_mulai', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->where('tanggal_mulai', '<=', now())
            ->where(function ($q) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', now());
            });
    }

    // ==================== ACCESSORS ====================

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS_OPTIONS[$this->jenis]['label'] ?? $this->jenis;
    }

    public function getDefaultColorAttribute(): string
    {
        return self::JENIS_OPTIONS[$this->jenis]['color'] ?? '#6B7280';
    }

    public function getTanggalFormatAttribute(): string
    {
        $start = $this->tanggal_mulai->translatedFormat('d M Y');
        
        if ($this->tanggal_selesai && !$this->tanggal_mulai->eq($this->tanggal_selesai)) {
            return $start . ' - ' . $this->tanggal_selesai->translatedFormat('d M Y');
        }
        
        return $start;
    }

    public function getDurationDaysAttribute(): int
    {
        if (!$this->tanggal_selesai) {
            return 1;
        }
        
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }
}
