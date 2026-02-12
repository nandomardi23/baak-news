<?php

namespace App\Services\Sync;

use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\MatkulKurikulum;

class CurriculumSyncService extends BaseSyncService
{
    public function syncKurikulum(int $offset = 0, int $limit = 100): array
    {
        $totalAll = 0;
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountKurikulum();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncKurikulum: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getKurikulum($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                Kurikulum::updateOrCreate(
                    ['id_kurikulum' => $item['id_kurikulum']],
                    [
                        'nama_kurikulum' => $item['nama_kurikulum'],
                        'id_prodi' => $item['id_prodi'],
                        'id_semester' => $item['id_semester'],
                        'jumlah_sks_lulus' => $item['jumlah_sks_lulus'],
                        'jumlah_sks_wajib' => $item['jumlah_sks_wajib'],
                        'jumlah_sks_pilihan' => $item['jumlah_sks_pilihan'],
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Kurikulum {$item['nama_kurikulum']}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }

    public function syncMataKuliah(int $offset = 0, int $limit = 500): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountMataKuliah();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncMataKuliah: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getMatkulKurikulum($limit, $offset); 
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                // 1. Sync Mata Kuliah Master
                $mk = MataKuliah::updateOrCreate(
                    ['id_matkul' => $item['id_matkul']],
                    [
                        'kode_matkul' => $item['kode_mata_kuliah'],
                        'nama_matkul' => $item['nama_mata_kuliah'],
                        'id_prodi' => $item['id_prodi'],
                        'sks_mata_kuliah' => $item['sks_mata_kuliah'],
                        'sks_tatap_muka' => $item['sks_tatap_muka'],
                        'sks_praktek' => $item['sks_praktek'],
                        'sks_praktek_lapangan' => $item['sks_praktek_lapangan'],
                        'sks_simulasi' => $item['sks_simulasi'],
                    ]
                );

                // 2. Sync Matkul Kurikulum Relation
                if (isset($item['id_kurikulum'])) {
                    MatkulKurikulum::updateOrCreate(
                        ['id_matkul' => $item['id_matkul'], 'id_kurikulum' => $item['id_kurikulum']],
                        [
                            'semester' => $item['semester'],
                            'sks_mata_kuliah' => $item['sks_mata_kuliah'],
                            'sks_tatap_muka' => $item['sks_tatap_muka'],
                            'sks_praktek' => $item['sks_praktek'],
                            'sks_praktek_lapangan' => $item['sks_praktek_lapangan'],
                            'sks_simulasi' => $item['sks_simulasi'],
                            'apakah_wajib' => $item['apakah_wajib'],
                        ]
                    );
                }

                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Matkul {$item['kode_mata_kuliah']}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }
}
