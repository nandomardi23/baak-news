<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            // Shrink existing columns to free up space
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
    }

    public function down(): void
    {
        // ...
    }
};
