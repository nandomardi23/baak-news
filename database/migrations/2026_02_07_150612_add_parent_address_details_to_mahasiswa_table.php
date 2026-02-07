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
            $table->string('rt_ortu')->nullable()->after('alamat_ortu');
            $table->string('rw_ortu')->nullable()->after('rt_ortu');
            $table->string('kelurahan_ortu')->nullable()->after('rw_ortu');
            $table->string('kecamatan_ortu')->nullable()->after('kelurahan_ortu');
            $table->string('kota_kabupaten_ortu')->nullable()->after('kecamatan_ortu');
            $table->string('provinsi_ortu')->nullable()->after('kota_kabupaten_ortu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn([
                'rt_ortu',
                'rw_ortu',
                'kelurahan_ortu',
                'kecamatan_ortu',
                'kota_kabupaten_ortu',
                'provinsi_ortu',
            ]);
        });
    }
};
