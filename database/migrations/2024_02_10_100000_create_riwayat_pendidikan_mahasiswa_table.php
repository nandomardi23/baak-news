<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('riwayat_pendidikan_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_registrasi_mahasiswa')->unique();
            $table->string('id_mahasiswa');
            $table->string('nim');
            $table->string('nama_mahasiswa');
            $table->string('id_jenis_daftar');
            $table->string('nama_jenis_daftar');
            $table->string('id_jalur_daftar')->nullable();
            $table->string('nama_jalur_daftar')->nullable();
            $table->string('id_periode_masuk');
            $table->date('tanggal_daftar')->nullable();
            $table->string('id_perguruan_tinggi_asal')->nullable();
            $table->string('nama_perguruan_tinggi_asal')->nullable();
            $table->string('id_prodi_asal')->nullable();
            $table->string('nama_prodi_asal')->nullable();
            $table->integer('sks_diakui')->default(0);
            $table->decimal('biaya_masuk', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikan_mahasiswa');
    }
};
