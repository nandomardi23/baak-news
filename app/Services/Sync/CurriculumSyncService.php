<?php

namespace App\Services\Sync;

use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\MatkulKurikulum;

class CurriculumSyncService extends BaseSyncService
{
    public function syncKurikulum(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountKurikulum();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncKurikulum: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getKurikulum($limit, $offset, $filter);
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
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

            $this->batchUpsert(Kurikulum::class, $records, ['id_kurikulum'], [
                'nama_kurikulum', 'id_prodi', 'id_semester', 'jumlah_sks_lulus', 'jumlah_sks_wajib', 'jumlah_sks_pilihan', 'updated_at'
            ]);
            $synced = count($records);
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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

    public function syncMataKuliah(int $offset = 0, int $limit = 2000, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountMatkulKurikulum();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncMataKuliah: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getMatkulKurikulum($limit, $offset, $filter);
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
            $mkRecords = [];
            $relRecords = [];
            
            foreach ($data as $item) {
                // 1. Mata Kuliah Master
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

                // 2. Relation
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

            $this->batchUpsert(MataKuliah::class, $mkRecords, ['id_matkul'], [
                'kode_matkul', 'nama_matkul', 'id_prodi', 'sks_mata_kuliah', 'sks_tatap_muka', 'sks_praktek', 'sks_praktek_lapangan', 'sks_simulasi', 'updated_at'
            ]);
            
            if (!empty($relRecords)) {
                $this->batchUpsert(MatkulKurikulum::class, $relRecords, ['id_matkul', 'id_kurikulum'], [
                    'semester', 'sks_mata_kuliah', 'sks_tatap_muka', 'sks_praktek', 'sks_praktek_lapangan', 'sks_simulasi', 'apakah_wajib', 'updated_at'
                ]);
            }
            
            $synced = count($mkRecords);
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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
