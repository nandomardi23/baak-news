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
            // Adjust 'dosen_id' type/size to match 'dosen' table's primary key or id_dosen column.
            // In create_dosens_table: $table->id(); (BigInteger).
            // But there is also $table->string('id_dosen')->unique();
            // Assuming we want to link via the internal unsignedBigInteger ID.
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pejabat', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->dropColumn('dosen_id');
        });
    }
};
