<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_mahasiswa')->unique()->comment('ID dari Neo Feeder');
            $table->string('nim')->unique();
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota_kabupaten')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->text('alamat_ortu')->nullable();
            $table->string('rt_ortu')->nullable();
            $table->string('rw_ortu')->nullable();
            $table->string('kelurahan_ortu')->nullable();
            $table->string('kecamatan_ortu')->nullable();
            $table->string('kota_kabupaten_ortu')->nullable();
            $table->string('provinsi_ortu')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('id_prodi')->nullable()->comment('ID Prodi dari Neo Feeder');
            $table->string('angkatan')->nullable();
            $table->string('status_mahasiswa')->nullable(); // Aktif, Cuti, DO, Lulus
            $table->string('id_registrasi_mahasiswa')->nullable()->comment('ID Registrasi dari Neo Feeder');
            $table->decimal('ipk', 4, 2)->nullable();
            $table->integer('sks_tempuh')->nullable();
            
            $table->string('dusun')->nullable();
            $table->string('nik')->nullable();
            $table->string('nisn')->nullable();
            $table->string('npwp')->nullable();
            $table->string('kewarganegaraan')->nullable();
            $table->string('telepon')->nullable();

            $table->foreignId('dosen_wali_id')->nullable()->constrained('dosen')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index('nim');
            $table->index('nama');
            $table->index('program_studi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
