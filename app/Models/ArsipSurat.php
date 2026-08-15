<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ArsipSurat extends Model
{
    use HasFactory;

    protected $table = 'arsip_surat';

    protected $fillable = [
        'jenis',
        'nomor_surat',
        'tanggal_surat',
        'tanggal_diterima',
        'asal_surat',
        'tujuan_surat',
        'perihal',
        'keterangan',
        'file_path',
        'created_by',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_diterima' => 'date',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeMasuk($query)
    {
        return $query->where('jenis', 'masuk');
    }

    public function scopeKeluar($query)
    {
        return $query->where('jenis', 'keluar');
    }

    // ─── Accessors ───────────────────────────────────────────

    /**
     * Get the public URL for the uploaded file.
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human-readable label for jenis surat.
     */
    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'masuk' => 'Surat Masuk',
            'keluar' => 'Surat Keluar',
            default => $this->jenis,
        };
    }

    /**
     * Get badge variant for jenis surat.
     */
    public function getJenisBadgeAttribute(): string
    {
        return match ($this->jenis) {
            'masuk' => 'info',
            'keluar' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get the file extension.
     */
    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Check if the file is a PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->file_extension === 'pdf';
    }

    /**
     * Check if the file is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return in_array($this->file_extension, ['jpg', 'jpeg', 'png', 'webp']);
    }
}
