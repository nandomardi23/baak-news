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
        Schema::create('aktivitas_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_aktivitas')->unique(); // ID from NeoFeeder
            $table->string('judul');
            $table->string('id_jenis_aktivitas')->index(); // 1=Penelitian, 2=Pengabdian, 3=Tugas Akhir, etc.
            $table->string('nama_jenis_aktivitas')->nullable();
            $table->string('id_prodi')->nullable();
            $table->string('id_semester')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('sk_tugas')->nullable();
            $table->date('tanggal_sk_tugas')->nullable();
            $table->string('keterangan')->nullable();
            
            $table->timestamps();
        });

        Schema::create('anggota_aktivitas_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_anggota')->unique(); // ID from NeoFeeder
            $table->string('id_aktivitas')->index();
            $table->string('id_registrasi_mahasiswa')->index();
            $table->string('nim')->nullable();
            $table->string('nama_mahasiswa')->nullable();
            $table->string('peran')->nullable(); // 1=Ketua, 2=Anggota, 3=Personal

            // $table->foreign('id_aktivitas')->references('id_aktivitas')->on('aktivitas_mahasiswa')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_aktivitas_mahasiswa');
        Schema::dropIfExists('aktivitas_mahasiswa');
    }
};
