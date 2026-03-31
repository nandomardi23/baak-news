<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DokumenTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'kategori',
        'file_path',
        'file_type',
        'file_size'
    ];
    
    protected $appends = ['file_url', 'ukuran_format'];

    public function getFileUrlAttribute()
    {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getUkuranFormatAttribute()
    {
        $bytes = $this->file_size;
        if (!$bytes) return '0 B';
        
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
