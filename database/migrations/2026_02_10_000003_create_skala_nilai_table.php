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
        Schema::create('skala_nilai', function (Blueprint $table) {
            $table->id();
            $table->string('id_bobot_nilai')->unique(); // From NeoFeeder
            $table->string('id_prodi')->index();
            $table->string('nilai_huruf', 5);
            $table->decimal('nilai_indeks', 4, 2);
            $table->decimal('bobot_minimum', 4, 2);
            $table->decimal('bobot_maksimum', 4, 2);
            $table->date('tanggal_mulai_efektif')->nullable();
            $table->date('tanggal_akhir_efektif')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skala_nilai');
    }
};
