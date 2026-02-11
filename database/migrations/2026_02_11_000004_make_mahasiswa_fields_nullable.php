<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('dusun', 100)->nullable()->change();
            $table->string('rt', 10)->nullable()->change();
            $table->string('rw', 10)->nullable()->change();
            $table->string('kelurahan', 100)->nullable()->change();
            $table->string('kecamatan', 100)->nullable()->change();
            $table->string('kota_kabupaten', 100)->nullable()->change();
            $table->string('provinsi', 100)->nullable()->change();
            $table->string('kode_pos', 10)->nullable()->change();
            $table->string('no_hp', 50)->nullable()->change();
            $table->string('email', 100)->nullable()->change();
            $table->string('nama_ayah', 150)->nullable()->change();
            $table->string('pekerjaan_ayah', 100)->nullable()->change();
            $table->string('nama_ibu', 150)->nullable()->change();
            $table->string('pekerjaan_ibu', 100)->nullable()->change();
            $table->string('nik', 50)->nullable()->change();
            $table->string('nisn', 50)->nullable()->change();
            $table->string('npwp', 50)->nullable()->change();
            $table->string('telepon', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        // ...
    }
};
