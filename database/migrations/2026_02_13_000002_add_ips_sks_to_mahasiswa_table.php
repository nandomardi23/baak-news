<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom IPS dan SKS Total ke tabel mahasiswa
     * untuk menyimpan data akademik terbaru dari sync AKM
     */
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            if (!Schema::hasColumn('mahasiswa', 'ips')) {
                $table->decimal('ips', 4, 2)->nullable()->after('ipk');
            }
            if (!Schema::hasColumn('mahasiswa', 'sks_total')) {
                $table->integer('sks_total')->nullable()->after('sks_tempuh');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['ips', 'sks_total']);
        });
    }
};
