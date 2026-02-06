<?php

namespace Database\Seeders;

use App\Models\Pejabat;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Program Studi
        $prodiData = [
            ['id_prodi' => 'PS001', 'kode_prodi' => '14401', 'nama_prodi' => 'D-III Keperawatan', 'jenjang' => 'D3', 'jenis_program' => 'reguler', 'akreditasi' => 'B'],
            ['id_prodi' => 'PS002', 'kode_prodi' => '14501', 'nama_prodi' => 'S1 Keperawatan', 'jenjang' => 'S1', 'jenis_program' => 'reguler', 'akreditasi' => 'B'],
            ['id_prodi' => 'PS003', 'kode_prodi' => '14502', 'nama_prodi' => 'Profesi Ners', 'jenjang' => 'Profesi', 'jenis_program' => 'reguler', 'akreditasi' => 'B'],
            ['id_prodi' => 'PS004', 'kode_prodi' => '14503', 'nama_prodi' => 'S1 Keperawatan (RPL)', 'jenjang' => 'S1', 'jenis_program' => 'rpl', 'akreditasi' => 'B'],
        ];

        foreach ($prodiData as $prodi) {
            ProgramStudi::firstOrCreate(['id_prodi' => $prodi['id_prodi']], $prodi);
        }

        // Seed Tahun Akademik
        $tahunAkademikData = [
            ['id_semester' => '20241', 'nama_semester' => '2024/2025 Ganjil', 'tahun' => 2024, 'semester' => 'ganjil', 'is_active' => true],
            ['id_semester' => '20232', 'nama_semester' => '2023/2024 Genap', 'tahun' => 2024, 'semester' => 'genap', 'is_active' => false],
            ['id_semester' => '20231', 'nama_semester' => '2023/2024 Ganjil', 'tahun' => 2023, 'semester' => 'ganjil', 'is_active' => false],
        ];

        foreach ($tahunAkademikData as $ta) {
            TahunAkademik::firstOrCreate(['id_semester' => $ta['id_semester']], $ta);
        }

        // Seed Pejabat
        $pejabatData = [
            [
                'nama' => 'Ns. Eka Yudha Chrisanto',
                'nip' => '19850101 201001 1 001',
                'nidn' => '1001018501',
                'jabatan' => 'Ketua',
                'gelar_depan' => '',
                'gelar_belakang' => 'S.Kep, M.Kep',
                'is_active' => true,
            ],
            [
                'nama' => 'Ns. Maria Dewi',
                'nip' => '19870515 201201 2 002',
                'nidn' => '1515058701',
                'jabatan' => 'Kaprodi D3 Keperawatan',
                'gelar_depan' => '',
                'gelar_belakang' => 'S.Kep, M.Kep',
                'is_active' => true,
            ],
            [
                'nama' => 'Ns. Budi Santoso',
                'nip' => '19880620 201301 1 003',
                'nidn' => '2020068801',
                'jabatan' => 'Kaprodi S1 Keperawatan',
                'gelar_depan' => '',
                'gelar_belakang' => 'S.Kep, M.Kep',
                'is_active' => true,
            ],
        ];

        foreach ($pejabatData as $pejabat) {
            Pejabat::firstOrCreate(['nama' => $pejabat['nama'], 'jabatan' => $pejabat['jabatan']], $pejabat);
        }
    }
}
