<?php

namespace App\Services\Sync\Mappers;

class CurriculumDataMapper
{
    public static function mapKurikulum(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_kurikulum' => $item['id_kurikulum'],
                'nama_kurikulum' => $item['nama_kurikulum'],
                'id_prodi' => $item['id_prodi'],
                'id_semester' => $item['id_semester'],
                'jumlah_sks_lulus' => $item['jumlah_sks_lulus'] ?? 0,
                'jumlah_sks_wajib' => $item['jumlah_sks_wajib'] ?? 0,
                'jumlah_sks_pilihan' => $item['jumlah_sks_pilihan'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return $records;
    }

    public static function mapMataKuliah(array $data): array
    {
        $mkRecords = [];
        $relRecords = [];
        
        foreach ($data as $item) {
            $mkRecords[] = [
                'id_matkul' => $item['id_matkul'],
                'kode_matkul' => $item['kode_mata_kuliah'],
                'nama_matkul' => $item['nama_mata_kuliah'],
                'id_prodi' => $item['id_prodi'],
                'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                'sks_tatap_muka' => $item['sks_tatap_muka'] ?? 0,
                'sks_praktek' => $item['sks_praktek'] ?? 0,
                'sks_praktek_lapangan' => $item['sks_praktek_lapangan'] ?? 0,
                'sks_simulasi' => $item['sks_simulasi'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (isset($item['id_kurikulum'])) {
                $relRecords[] = [
                    'id_matkul' => $item['id_matkul'],
                    'id_kurikulum' => $item['id_kurikulum'],
                    'semester' => $item['semester'],
                    'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                    'sks_tatap_muka' => $item['sks_tatap_muka'] ?? 0,
                    'sks_praktek' => $item['sks_praktek'] ?? 0,
                    'sks_praktek_lapangan' => $item['sks_praktek_lapangan'] ?? 0,
                    'sks_simulasi' => $item['sks_simulasi'] ?? 0,
                    'apakah_wajib' => $item['apakah_wajib'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        return ['mkRecords' => $mkRecords, 'relRecords' => $relRecords];
    }
}
