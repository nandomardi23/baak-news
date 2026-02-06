<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('id_matkul')->unique()->comment('ID dari Neo Feeder');
            $table->string('kode_matkul');
            $table->string('nama_matkul');
            $table->integer('sks_mata_kuliah')->default(0);
            $table->integer('sks_tatap_muka')->default(0);
            $table->integer('sks_praktek')->default(0);
            $table->integer('sks_praktek_lapangan')->default(0);
            $table->integer('sks_simulasi')->default(0);
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('id_prodi')->nullable();
            $table->timestamps();
            
            $table->index('kode_matkul');
            $table->index('program_studi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
