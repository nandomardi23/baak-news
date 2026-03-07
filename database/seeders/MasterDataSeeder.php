<?php

namespace Database\Seeders;

use App\Models\TahunAkademik;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Tahun Akademik
        $tahunAkademikData = [
            ['id_semester' => '20241', 'nama_semester' => '2024/2025 Ganjil', 'tahun' => 2024, 'semester' => 'ganjil', 'is_active' => true],
            ['id_semester' => '20232', 'nama_semester' => '2023/2024 Genap', 'tahun' => 2024, 'semester' => 'genap', 'is_active' => false],
            ['id_semester' => '20231', 'nama_semester' => '2023/2024 Ganjil', 'tahun' => 2023, 'semester' => 'ganjil', 'is_active' => false],
        ];

        foreach ($tahunAkademikData as $ta) {
            TahunAkademik::firstOrCreate(['id_semester' => $ta['id_semester']], $ta);
        }

    }
}
