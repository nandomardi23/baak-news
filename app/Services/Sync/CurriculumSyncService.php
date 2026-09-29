<?php

namespace App\Services\Sync;

use App\Models\Kurikulum;
use App\Models\MataKuliah;
use App\Models\MatkulKurikulum;
use App\Services\Sync\Mappers\CurriculumDataMapper;

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
            $records = CurriculumDataMapper::mapKurikulum($data);

            $this->batchUpsert(Kurikulum::class, $records, ['id_kurikulum'], [
                'nama_kurikulum', 'id_prodi', 'id_semester', 'jumlah_sks_lulus', 'jumlah_sks_wajib', 'jumlah_sks_pilihan', 'updated_at'
            ]);
            $synced = count($records);
        }

        return $this->buildPaginatedResult($batchCount, $synced, $errors, $totalAll, $offset, $limit);
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
            $mapped = CurriculumDataMapper::mapMataKuliah($data);
            $mkRecords = $mapped['mkRecords'];
            $relRecords = $mapped['relRecords'];

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

        return $this->buildPaginatedResult($batchCount, $synced, $errors, $totalAll, $offset, $limit);
    }
    public function getCountKurikulum(): int
    {
        try {
            $response = $this->neoFeeder->getCountKurikulum();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountMataKuliah(): int
    {
        try {
            $response = $this->neoFeeder->getCountMatkulKurikulum();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }


}
