<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->string('id_dosen')->unique();
            $table->string('nidn', 20)->nullable();
            $table->string('nip', 30)->nullable();
            $table->string('nama');
            $table->string('gelar_depan', 50)->nullable();
            $table->string('gelar_belakang', 100)->nullable();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jabatan_fungsional')->nullable();
            $table->string('id_status_aktif')->nullable();
            $table->string('status_aktif')->nullable();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('id_prodi')->nullable();
            $table->timestamps();
            
            $table->index('nidn');
            $table->index('nama');
            $table->index('program_studi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};

