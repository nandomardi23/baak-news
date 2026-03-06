<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    protected $table = 'kurikulum';
    protected $guarded = ['id'];

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi', 'id_prodi');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'id_semester', 'id_semester');
    }

    public function matkulKurikulum()
    {
        return $this->hasMany(MatkulKurikulum::class, 'id_kurikulum', 'id_kurikulum');
    }
}
