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
        Schema::table('kalender_akademik', function (Blueprint $table) {
            $table->string('jenis', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kalender_akademik', function (Blueprint $table) {
            $table->enum('jenis', ['pendaftaran', 'perkuliahan', 'ujian', 'libur', 'lainnya'])->change();
        });
    }
};
