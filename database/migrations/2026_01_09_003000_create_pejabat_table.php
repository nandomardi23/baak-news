<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pejabat', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nip')->nullable();
            $table->string('nidn')->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('jabatan'); // Ketua, Wakil Ketua, Kaprodi, dll
            $table->string('pangkat_golongan')->nullable();
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->date('periode_awal')->nullable();
            $table->date('periode_akhir')->nullable();
            $table->string('tandatangan_path')->nullable();
            $table->string('foto_path')->nullable();
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add the foreign key constraint for program_studi -> pejabat to avoid circular dependency
        Schema::table('program_studi', function (Blueprint $table) {
            $table->foreign('pejabat_id')->references('id')->on('pejabat')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            $table->dropForeign(['pejabat_id']);
        });

        Schema::dropIfExists('pejabat');
    }
};
