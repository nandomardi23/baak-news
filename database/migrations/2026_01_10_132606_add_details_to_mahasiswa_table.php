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
        Schema::table('mahasiswa', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswa', 'dusun')) $table->string('dusun')->nullable();
            if (!Schema::hasColumn('mahasiswa', 'nik')) $table->string('nik')->nullable();
            if (!Schema::hasColumn('mahasiswa', 'nisn')) $table->string('nisn')->nullable();
            if (!Schema::hasColumn('mahasiswa', 'npwp')) $table->string('npwp')->nullable();
            if (!Schema::hasColumn('mahasiswa', 'kewarganegaraan')) $table->string('kewarganegaraan')->nullable();
            if (!Schema::hasColumn('mahasiswa', 'telepon')) $table->string('telepon')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['dusun', 'nik', 'nisn', 'npwp', 'kewarganegaraan', 'telepon']);
        });
    }
};
