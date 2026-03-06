<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatkulKurikulum extends Model
{
    protected $table = 'matkul_kurikulum';
    protected $guarded = ['id'];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'id_kurikulum', 'id_kurikulum');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'id_matkul', 'id_matkul');
    }
}
