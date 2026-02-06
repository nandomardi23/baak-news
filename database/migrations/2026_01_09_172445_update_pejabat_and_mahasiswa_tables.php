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
        Schema::table('pejabat', function (Blueprint $table) {
            $table->string('pangkat_golongan')->nullable()->after('jabatan');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('pekerjaan_ayah')->nullable()->after('nama_ayah');
            $table->string('pekerjaan_ibu')->nullable()->after('nama_ibu');
            $table->text('alamat_ortu')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('pejabat', function (Blueprint $table) {
            $table->dropColumn('pangkat_golongan');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['pekerjaan_ayah', 'pekerjaan_ibu', 'alamat_ortu']);
        });
    }
};
