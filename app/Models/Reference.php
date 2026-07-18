<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    protected $guarded = ['id'];

    // Type constants
    const TYPE_AGAMA = 'agama';
    const TYPE_JENIS_TINGGAL = 'jenis_tinggal';
    const TYPE_ALAT_TRANSPORTASI = 'alat_transportasi';
    const TYPE_PEKERJAAN = 'pekerjaan';
    const TYPE_PENGHASILAN = 'penghasilan';
    const TYPE_KEBUTUHAN_KHUSUS = 'kebutuhan_khusus';
    const TYPE_PEMBIAYAAN = 'pembiayaan';

    /**
     * Scope to filter by reference type.
     *
     * Usage: Reference::ofType('agama')->get()
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
