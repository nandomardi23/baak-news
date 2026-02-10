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
        Schema::create('mahasiswa_lulus_do', function (Blueprint $table) {
            $table->id();
            $table->string('id_registrasi_mahasiswa')->unique(); // ID Reg from NeoFeeder
            $table->string('id_mahasiswa')->index();
            $table->string('id_prodi')->nullable();
            $table->string('id_semester_keluar')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->string('id_jenis_keluar')->nullable(); // Lulus, Mutasi, DO, dll
            $table->string('id_jalur_skripsi')->nullable();
            $table->string('judul_skripsi')->nullable();
            $table->string('bulan_awal_bimbingan')->nullable();
            $table->string('bulan_akhir_bimbingan')->nullable();
            $table->string('sk_yudisium')->nullable();
            $table->date('tanggal_sk_yudisium')->nullable();
            $table->decimal('ipk', 4, 2)->nullable();
            $table->string('nomor_ijazah')->nullable();
            $table->string('keterangan')->nullable();
            
            // Relasi optional ke tabel lokal (jika data sudah ada)
            // $table->foreign('id_mahasiswa')->references('id_mahasiswa')->on('mahasiswa')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_lulus_do');
    }
};
