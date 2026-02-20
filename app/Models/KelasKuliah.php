<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @property string $id_kelas_kuliah
 * @property string $nama_kelas_kuliah
 */
class KelasKuliah extends Model
{
    protected $table = 'kelas_kuliah';

    protected $fillable = [
        'id_kelas_kuliah',
        'id_matkul',
        'mata_kuliah_id',
        'id_prodi',
        'program_studi_id',
        'id_semester',
        'tahun_akademik_id',
        'nama_kelas_kuliah',
        'kode_mata_kuliah',
        'nama_mata_kuliah',
        'sks',
        'kapasitas',
        'dosen_id',
        'id_dosen',
        'nama_dosen',
    ];

    /**
     * Get the Mata Kuliah for this class
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Get the Program Studi for this class
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }

    /**
     * Get the Tahun Akademik/Semester for this class
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(TahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get KRS Details for this class
     */
    public function krsDetails(): HasMany
    {
        return $this->hasMany(KrsDetail::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
    /**
     * Get related Dosen Pengajar (Many-to-Many via dosen_pengajar_kelas)
     */
    public function dosenPengajar(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Dosen::class, 'dosen_pengajar_kelas', 'kelas_kuliah_id', 'dosen_id')
            ->withPivot(['sks_substansi_total', 'rencana_tatap_muka', 'realisasi_tatap_muka', 'nama_jenis_evaluasi'])
            ->withTimestamps();
    }
}
