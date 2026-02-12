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
        Schema::table('konversi_kampus_merdeka', function (Blueprint $table) {
            if (!Schema::hasColumn('konversi_kampus_merdeka', 'id_aktivitas_mahasiswa')) {
                $table->string('id_aktivitas_mahasiswa')->index()->nullable()->after('id_anggota');
            }
            if (!Schema::hasColumn('konversi_kampus_merdeka', 'judul_aktivitas_mahasiswa')) {
                $table->string('judul_aktivitas_mahasiswa')->nullable()->after('id_aktivitas_mahasiswa');
            }
            if (!Schema::hasColumn('konversi_kampus_merdeka', 'id_semester')) {
                $table->string('id_semester')->index()->nullable()->after('judul_aktivitas_mahasiswa');
            }
            if (!Schema::hasColumn('konversi_kampus_merdeka', 'nim')) {
                $table->string('nim')->index()->nullable()->after('id_semester');
            }
            if (!Schema::hasColumn('konversi_kampus_merdeka', 'nama_mahasiswa')) {
                $table->string('nama_mahasiswa')->nullable()->after('nim');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konversi_kampus_merdeka', function (Blueprint $table) {
            $table->dropColumn(['id_aktivitas_mahasiswa', 'judul_aktivitas_mahasiswa', 'id_semester', 'nim', 'nama_mahasiswa']);
        });
    }
};
