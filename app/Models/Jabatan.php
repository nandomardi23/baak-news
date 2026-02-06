<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_jabatan',
        'kode_jabatan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
