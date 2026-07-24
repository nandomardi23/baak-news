<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YudisiumRequirement extends Model
{
    use HasFactory;

    protected $table = 'yudisium_requirements';

    protected $fillable = [
        'nama_syarat',
        'deskripsi',
        'is_upload_required',
        'is_active',
    ];

    protected $casts = [
        'is_upload_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
