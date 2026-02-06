<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPengajuan extends Model
{
    protected $table = 'surat_pengajuan';

    protected $fillable = [
        'nomor_surat',
        'mahasiswa_id',
        'pejabat_id',
        'jenis_surat',
        'keperluan',
        'data_tambahan',
        'status',
        'catatan',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'data_tambahan' => 'array',
        'processed_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function pejabat(): BelongsTo
    {
        return $this->belongsTo(Pejabat::class, 'pejabat_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get jenis surat label
     */
    public function getJenisSuratLabelAttribute(): string
    {
        return match($this->jenis_surat) {
            'aktif_kuliah' => 'Surat Aktif Kuliah',
            'krs' => 'Kartu Rencana Studi',
            'khs' => 'Kartu Hasil Studi',
            'transkrip' => 'Transkrip Nilai',
            default => $this->jenis_surat,
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'printed' => 'Sudah Dicetak',
            default => $this->status,
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'printed' => 'info',
            default => 'secondary',
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Approve the surat
     */
    public function approve(int $userId): void
    {
        $this->update([
            'status' => 'approved',
            'nomor_surat' => $this->generateNomorSurat(),
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);
    }

    /**
     * Reject the surat
     */
    public function reject(int $userId, ?string $catatan = null): void
    {
        $this->update([
            'status' => 'rejected',
            'catatan' => $catatan,
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark as printed with optional pejabat
     */
    public function markAsPrinted(?int $pejabatId = null): void
    {
        $data = ['status' => 'printed'];
        if ($pejabatId && !$this->pejabat_id) {
            $data['pejabat_id'] = $pejabatId;
        }
        $this->update($data);
    }

    /**
     * Set pejabat for the surat
     */
    public function setPejabat(int $pejabatId): void
    {
        $this->update(['pejabat_id' => $pejabatId]);
    }

    /**
     * Generate automatic nomor surat
     */
    protected function generateNomorSurat(): string
    {
        $month = $this->toRoman((int) date('n'));
        $year = date('Y');
        
        // Count surat of the same type this year
        $count = self::whereYear('processed_at', $year)
            ->where('jenis_surat', $this->jenis_surat)
            ->whereNotNull('nomor_surat')
            ->count() + 1;
        
        // Format: /I/2026
        return '/' . $month . '/' . $year;
    }

    /**
     * Convert number to Roman numeral
     */
    protected function toRoman(int $number): string
    {
        $map = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        ];
        $result = '';
        foreach ($map as $roman => $int) {
            while ($number >= $int) {
                $result .= $roman;
                $number -= $int;
            }
        }
        return $result;
    }
}
