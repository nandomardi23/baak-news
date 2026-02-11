<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Shrink existing columns to free up row space
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('id_mahasiswa', 50)->change();
            $table->string('nim', 50)->change();
            $table->string('nama', 150)->change();
            $table->string('id_prodi', 50)->change();
            $table->string('angkatan', 10)->change();
            $table->string('status_mahasiswa', 50)->change();
            $table->string('dusun', 100)->change();
            $table->string('nik', 50)->change();
            $table->string('nisn', 50)->change();
            $table->string('npwp', 50)->change();
            $table->string('telepon', 50)->change();
            $table->string('no_hp', 50)->change();
            $table->string('email', 100)->change();
            $table->string('nama_ayah', 150)->change();
            $table->string('pekerjaan_ayah', 100)->change();
            $table->string('nama_ibu', 150)->change();
            $table->string('pekerjaan_ibu', 100)->change();
            $table->string('rt', 10)->change();
            $table->string('rw', 10)->change();
            $table->string('kelurahan', 100)->change();
            $table->string('kecamatan', 100)->change();
            $table->string('kota_kabupaten', 100)->change();
            $table->string('provinsi', 100)->change();
            $table->string('kode_pos', 10)->change();
        });

        // 2. Add missing columns with optimized sizes
        Schema::table('mahasiswa', function (Blueprint $table) {
            $this->safeAddColumn($table, 'string', 'nama_mahasiswa', 150, 'nama');
            $this->safeAddColumn($table, 'string', 'id_agama', 50, 'tempat_lahir');
            $this->safeAddColumn($table, 'string', 'nama_agama', 100, 'id_agama');
            $this->safeAddColumn($table, 'string', 'jalan', 150, 'alamat');
            $this->safeAddColumn($table, 'string', 'id_wilayah', 50, 'kode_pos');
            $this->safeAddColumn($table, 'string', 'nama_wilayah', 150, 'id_wilayah');
            $this->safeAddColumn($table, 'string', 'id_jenis_tinggal', 50, 'nama_wilayah');
            $this->safeAddColumn($table, 'string', 'nama_jenis_tinggal', 100, 'id_jenis_tinggal');
            $this->safeAddColumn($table, 'string', 'id_alat_transportasi', 50, 'nama_jenis_tinggal');
            $this->safeAddColumn($table, 'string', 'nama_alat_transportasi', 100, 'id_alat_transportasi');
            $this->safeAddColumn($table, 'string', 'handphone', 50, 'telepon');

            // Parent detail refinements
            $this->safeAddColumn($table, 'string', 'nik_ayah', 50, 'nama_ayah');
            $this->safeAddColumn($table, 'date', 'tanggal_lahir_ayah', null, 'nik_ayah');
            $this->safeAddColumn($table, 'string', 'id_pendidikan_ayah', 50, 'tanggal_lahir_ayah');
            $this->safeAddColumn($table, 'string', 'nama_pendidikan_ayah', 100, 'id_pendidikan_ayah');
            $this->safeAddColumn($table, 'string', 'id_pekerjaan_ayah', 50, 'nama_pendidikan_ayah');
            $this->safeAddColumn($table, 'string', 'nama_pekerjaan_ayah', 100, 'id_pekerjaan_ayah');
            $this->safeAddColumn($table, 'string', 'id_penghasilan_ayah', 50, 'nama_pekerjaan_ayah');
            $this->safeAddColumn($table, 'string', 'nama_penghasilan_ayah', 100, 'id_penghasilan_ayah');

            $this->safeAddColumn($table, 'string', 'nik_ibu', 50, 'nama_ibu');
            $this->safeAddColumn($table, 'date', 'tanggal_lahir_ibu', null, 'nik_ibu');
            $this->safeAddColumn($table, 'string', 'id_pendidikan_ibu', 50, 'tanggal_lahir_ibu');
            $this->safeAddColumn($table, 'string', 'nama_pendidikan_ibu', 100, 'id_pendidikan_ibu');
            $this->safeAddColumn($table, 'string', 'id_pekerjaan_ibu', 50, 'nama_pendidikan_ibu');
            $this->safeAddColumn($table, 'string', 'nama_pekerjaan_ibu', 100, 'id_pekerjaan_ibu');
            $this->safeAddColumn($table, 'string', 'id_penghasilan_ibu', 50, 'nama_pekerjaan_ibu');
            $this->safeAddColumn($table, 'string', 'nama_penghasilan_ibu', 100, 'id_penghasilan_ibu');

            $this->safeAddColumn($table, 'string', 'nama_wali', 100);
            $this->safeAddColumn($table, 'date', 'tanggal_lahir_wali');
            $this->safeAddColumn($table, 'string', 'id_pendidikan_wali', 50);
            $this->safeAddColumn($table, 'string', 'nama_pendidikan_wali', 100);
            $this->safeAddColumn($table, 'string', 'id_pekerjaan_wali', 50);
            $this->safeAddColumn($table, 'string', 'nama_pekerjaan_wali', 100);
            $this->safeAddColumn($table, 'string', 'id_penghasilan_wali', 50);
            $this->safeAddColumn($table, 'string', 'nama_penghasilan_wali', 100);

            $this->safeAddColumn($table, 'string', 'id_kebutuhan_khusus_mahasiswa', 50);
            $this->safeAddColumn($table, 'string', 'nama_kebutuhan_khusus_mahasiswa', 100);
            $this->safeAddColumn($table, 'string', 'id_kebutuhan_khusus_ayah', 50);
            $this->safeAddColumn($table, 'string', 'nama_kebutuhan_khusus_ayah', 100);
            $this->safeAddColumn($table, 'string', 'id_kebutuhan_khusus_ibu', 50);
            $this->safeAddColumn($table, 'string', 'nama_kebutuhan_khusus_ibu', 100);

            $this->safeAddColumn($table, 'string', 'id_status_mahasiswa', 50, 'status_mahasiswa');
            $this->safeAddColumn($table, 'string', 'nama_status_mahasiswa', 100, 'id_status_mahasiswa');
            $this->safeAddColumn($table, 'string', 'id_periode', 20, 'angkatan');
            
            $this->safeAddColumn($table, 'text', 'keterangan_keluar');
            $this->safeAddColumn($table, 'date', 'tanggal_keluar');
            $this->safeAddColumn($table, 'string', 'id_periode_keluar', 20);
            $this->safeAddColumn($table, 'string', 'nomor_sk_yudisium', 100);
            $this->safeAddColumn($table, 'date', 'tanggal_sk_yudisium');
            $this->safeAddColumn($table, 'string', 'nomor_ijazah', 100);
        });
    }

    private function safeAddColumn(Blueprint $table, string $type, string $column, $length = null, $after = null)
    {
        if (!Schema::hasColumn('mahasiswa', $column)) {
            $col = $length ? $table->$type($column, $length) : $table->$type($column);
            if ($after) {
                try {
                    $col->after($after);
                } catch (\Exception $e) {}
            }
            $col->nullable();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ... omitted for brevity in debug
    }
};
