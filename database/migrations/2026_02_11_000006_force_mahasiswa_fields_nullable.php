<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Using DB::statement to ensure compatibility and force the change
        $fields = [
            'dusun', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota_kabupaten', 'provinsi', 'kode_pos',
            'no_hp', 'email', 'nama_ayah', 'pekerjaan_ayah', 'nama_ibu', 'pekerjaan_ibu',
            'nik', 'nisn', 'npwp', 'telepon', 'status_mahasiswa', 'id_prodi', 'angkatan'
        ];

        foreach ($fields as $field) {
            try {
                // Get current definition to preserve type but set NULL
                $table = 'mahasiswa';
                $results = DB::select("SHOW COLUMNS FROM {$table} LIKE '{$field}'");
                if (!empty($results)) {
                    $type = $results[0]->Type;
                    DB::statement("ALTER TABLE {$table} MODIFY COLUMN `{$field}` {$type} NULL");
                }
            } catch (\Exception $e) {
                // Ignore if field doesn't exist
            }
        }
        
        // Ensure status_mahasiswa is nullable since the sync uses nama_status_mahasiswa
        DB::statement("ALTER TABLE mahasiswa MODIFY COLUMN status_mahasiswa VARCHAR(50) NULL");
    }

    public function down(): void
    {
    }
};
