<?php

namespace App\Services\Sync;

use App\Models\Reference;
use App\Models\RefWilayah;
use App\Models\ProgramStudi;

class ReferenceSyncService extends BaseSyncService
{
    /**
     * Generic method to sync a reference type from NeoFeeder.
     *
     * @param string $type       Reference type constant (e.g. 'agama')
     * @param string $apiMethod  NeoFeeder API method name (e.g. 'getAgama')
     * @param string $idField    Field name for external ID in API response (e.g. 'id_agama')
     * @param string $nameField  Field name for display name in API response (e.g. 'nama_agama')
     * @param \Closure|null $filter  Optional filter callback for records
     */
    private function syncReferenceType(
        string $type,
        string $apiMethod,
        string $idField,
        string $nameField,
        ?\Closure $filter = null,
    ): array {
        try {
            $response = $this->neoFeeder->$apiMethod();
            $batchCount = 0;
            $synced = 0;
            $skipped = 0;

            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $records = [];
                $now = now();

                foreach ($data as $item) {
                    // Apply custom filter if provided
                    if ($filter && !$filter($item)) {
                        $skipped++;
                        continue;
                    }

                    $records[] = [
                        'type' => $type,
                        'external_id' => (string) $item[$idField],
                        'nama' => $item[$nameField],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($records)) {
                    Reference::upsert($records, ['type', 'external_id'], ['nama', 'updated_at']);
                    $synced = count($records);
                }
            }

            return [
                'synced' => $synced,
                'total' => $batchCount,
                'total_all' => $batchCount,
                'skipped_combinations' => $skipped > 0 ? $skipped : null,
                'progress' => 100,
                'has_more' => false,
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Sync {$type} failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Sync Program Studi with pagination
     * Uses GetCount first, then fetches data with progress
     * 
     * @return array
     */
    public function syncProdi(int $offset = 0, int $limit = 100, ?string $syncSince = null): array
    {
        // Get total count from API
        // Get total count from API
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountProdi();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            // Context: Log warning but continue sync with manual paging
            \Illuminate\Support\Facades\Log::warning("SyncProdi: GetCount failed, relying on pagination end. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getProdi($limit, $offset, $filter);

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
                    'id_prodi' => $item['id_prodi'],
                    'kode_prodi' => $item['kode_program_studi'],
                    'nama_prodi' => $item['nama_program_studi'],
                    'jenjang' => $item['nama_jenjang_pendidikan'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            try {
                ProgramStudi::upsert(
                    $records,
                    ['id_prodi'],
                    ['kode_prodi', 'nama_prodi', 'jenjang', 'updated_at']
                );
                $synced = count($records);
            } catch (\Exception $e) {
                $errors[] = "Prodi Batch Error: " . $e->getMessage();
            }
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

    /**
     * Sync Semester with pagination
     * Uses GetCount first, then fetches data with progress
     * 
     * @return array
     */
    public function syncSemester(int $offset = 0, int $limit = 100, ?string $syncSince = null): array
    {
        // 1. Get total count
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountSemester();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncSemester: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch data
        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getSemester($limit, $offset, $filter);

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
                    'id_semester' => $item['id_semester'],
                    'nama_semester' => $item['nama_semester'],
                    'tahun' => $item['id_tahun_ajaran'],
                    'semester' => $item['semester'] == 1 ? 'ganjil' : 'genap',
                    'tanggal_mulai' => isset($item['tanggal_mulai']) ? date('Y-m-d', strtotime($item['tanggal_mulai'])) : null,
                    'tanggal_selesai' => isset($item['tanggal_selesai']) ? date('Y-m-d', strtotime($item['tanggal_selesai'])) : null,
                    'is_active' => $item['a_periode_aktif'] == '1',
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            try {
                \App\Models\TahunAkademik::upsert(
                    $records,
                    ['id_semester'],
                    ['nama_semester', 'tahun', 'semester', 'tanggal_mulai', 'tanggal_selesai', 'is_active', 'updated_at']
                );
                $synced = count($records);
            } catch (\Exception $e) {
                $errors[] = "Semester Batch Error: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : ($hasMore ? 0 : 100);

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

    public function syncAgama(?string $syncSince = null): array
    {
        return $this->syncReferenceType(
            Reference::TYPE_AGAMA,
            'getAgama',
            'id_agama',
            'nama_agama',
        );
    }

    public function syncWilayah(int $offset = 0, int $limit = 1000, ?string $syncSince = null): array
    {
        try {
            $filter = $this->getFilter('', $syncSince);
            $response = $this->neoFeeder->getWilayah($limit, $offset, $filter);
            $batchCount = 0;

            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $records = [];
                $now = now();

                foreach ($data as $item) {
                    $records[] = [
                        'id_wilayah' => $item['id_wilayah'],
                        'id_negara' => $item['id_negara'],
                        'nama_wilayah' => $item['nama_wilayah'],
                        'id_induk_wilayah' => $item['id_induk_wilayah'],
                        'id_level_wilayah' => (int) $item['id_level_wilayah'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($records)) {
                    RefWilayah::upsert($records, ['id_wilayah'], ['id_negara', 'nama_wilayah', 'id_induk_wilayah', 'id_level_wilayah', 'updated_at']);
                }
            }

            // Get total count for progress
            $totalAll = 0;
            try {
                $countResponse = $this->neoFeeder->getCountWilayah();
                if ($countResponse && isset($countResponse['data'])) {
                    $totalAll = $this->extractCount($countResponse['data']);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("SyncWilayah: GetCount failed. Error: " . $e->getMessage());
            }

            // Fallback for UI if count failed (Wilayah is approx 8000-10000)
            if ($totalAll == 0) {
                $totalAll = 10000;
            }

            $nextOffset = $offset + $batchCount;
            $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
            $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : ($hasMore ? 0 : 100);

            return [
                'synced' => $batchCount,
                'total' => $batchCount,
                'total_all' => $totalAll,
                'offset' => $offset,
                'next_offset' => $hasMore ? $nextOffset : null,
                'has_more' => $hasMore,
                'progress' => $progress
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncWilayah failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'errors' => [$e->getMessage()],
                'has_more' => false
            ];
        }
    }

    public function syncJenisTinggal(?string $syncSince = null): array
    {
        return $this->syncReferenceType(
            Reference::TYPE_JENIS_TINGGAL,
            'getJenisTinggal',
            'id_jenis_tinggal',
            'nama_jenis_tinggal',
        );
    }

    public function syncAlatTransportasi(?string $syncSince = null): array
    {
        return $this->syncReferenceType(
            Reference::TYPE_ALAT_TRANSPORTASI,
            'getAlatTransportasi',
            'id_alat_transportasi',
            'nama_alat_transportasi',
        );
    }

    public function syncPekerjaan(?string $syncSince = null): array
    {
        return $this->syncReferenceType(
            Reference::TYPE_PEKERJAAN,
            'getPekerjaan',
            'id_pekerjaan',
            'nama_pekerjaan',
        );
    }

    public function syncPenghasilan(?string $syncSince = null): array
    {
        return $this->syncReferenceType(
            Reference::TYPE_PENGHASILAN,
            'getPenghasilan',
            'id_penghasilan',
            'nama_penghasilan',
        );
    }

    public function syncKebutuhanKhusus(?string $syncSince = null): array
    {
        return $this->syncReferenceType(
            Reference::TYPE_KEBUTUHAN_KHUSUS,
            'getKebutuhanKhusus',
            'id_kebutuhan_khusus',
            'nama_kebutuhan_khusus',
            function (array $item): bool {
                $id = (int) $item['id_kebutuhan_khusus'];
                $name = $item['nama_kebutuhan_khusus'];

                $isPowerOfTwo = ($id > 0) && (($id & ($id - 1)) === 0);
                $isSingleName = !str_contains($name, ',');

                return $isPowerOfTwo || $isSingleName;
            },
        );
    }

    public function syncPembiayaan(?string $syncSince = null): array
    {
        return $this->syncReferenceType(
            Reference::TYPE_PEMBIAYAAN,
            'getPembiayaan',
            'id_pembiayaan',
            'nama_pembiayaan',
        );
    }

    public function getCountProdi(): int
    {
        try {
            $response = $this->neoFeeder->getCountProdi();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountSemester(): int
    {
        try {
            // Note: GetCountSemester might hang in some versions, but we use requestQuick
            $response = $this->neoFeeder->getCountSemester();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountWilayah(): int
    {
        try {
            $response = $this->neoFeeder->getCountWilayah();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
