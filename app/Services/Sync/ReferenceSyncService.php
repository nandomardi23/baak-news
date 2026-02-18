<?php

namespace App\Services\Sync;

use App\Models\RefAgama;
use App\Models\RefWilayah;
use App\Models\ProgramStudi;

use App\Models\RefJenisTinggal;
use App\Models\RefAlatTransportasi;
use App\Models\RefPekerjaan;
use App\Models\RefPenghasilan;
use App\Models\RefKebutuhanKhusus;
use App\Models\RefPembiayaan;

class ReferenceSyncService extends BaseSyncService
{
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

        foreach ($data as $item) {
            try {
                ProgramStudi::updateOrCreate(
                    ['id_prodi' => $item['id_prodi']],
                    [
                        'kode_prodi' => $item['kode_program_studi'],
                        'nama_prodi' => $item['nama_program_studi'],
                        'jenjang' => $item['nama_jenjang_pendidikan'],
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Prodi {$item['nama_program_studi']}: " . $e->getMessage();
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

        foreach ($data as $item) {
            try {
                \App\Models\TahunAkademik::updateOrCreate(
                    ['id_semester' => $item['id_semester']],
                    [
                        'nama_semester' => $item['nama_semester'],
                        'tahun' => $item['id_tahun_ajaran'], // Mapping id_tahun_ajaran to tahun
                        'semester' => $item['semester'] == 1 ? 'ganjil' : 'genap', // Adjust based on data
                        'tanggal_mulai' => $item['tanggal_mulai'],
                        'tanggal_selesai' => $item['tanggal_selesai'],
                        'is_active' => $item['a_periode_aktif'] == '1',
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                // Specific catch to allow continuing
                $errors[] = "Semester {$item['nama_semester']}: " . $e->getMessage();
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
        try {
            $response = $this->neoFeeder->getAgama();
            $batchCount = 0;
            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $records = [];
                $now = now();
                foreach ($data as $item) {
                    $records[] = [
                        'id_agama' => $item['id_agama'],
                        'nama_agama' => $item['nama_agama'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($records)) {
                    RefAgama::upsert($records, ['id_agama'], ['nama_agama', 'updated_at']);
                }
            }
            return [
                'synced' => $batchCount,
                'total' => $batchCount,
                'total_all' => $batchCount,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncAgama failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
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
                        'id_level_wilayah' => (int)$item['id_level_wilayah'],
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
        try {
            $response = $this->neoFeeder->getJenisTinggal();
            $batchCount = 0;
            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $records = [];
                $now = now();
                foreach ($data as $item) {
                    $records[] = [
                        'id_jenis_tinggal' => $item['id_jenis_tinggal'],
                        'nama_jenis_tinggal' => $item['nama_jenis_tinggal'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($records)) {
                    RefJenisTinggal::upsert($records, ['id_jenis_tinggal'], ['nama_jenis_tinggal', 'updated_at']);
                }
            }
            return [
                'synced' => $batchCount,
                'total' => $batchCount,
                'total_all' => $batchCount,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncJenisTinggal failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncAlatTransportasi(?string $syncSince = null): array
    {
        try {
            $response = $this->neoFeeder->getAlatTransportasi();
            $batchCount = 0;
            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $now = now();
                $records = [];
                foreach ($data as $item) {
                    $records[] = [
                        'id_alat_transportasi' => $item['id_alat_transportasi'],
                        'nama_alat_transportasi' => $item['nama_alat_transportasi'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($records)) {
                    RefAlatTransportasi::upsert($records, ['id_alat_transportasi'], ['nama_alat_transportasi', 'updated_at']);
                }
            }
            return [
                'synced' => $batchCount,
                'total' => $batchCount,
                'total_all' => $batchCount,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncAlatTransportasi failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncPekerjaan(?string $syncSince = null): array
    {
        try {
            $response = $this->neoFeeder->getPekerjaan();
            $batchCount = 0;
            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $now = now();
                $records = [];
                foreach ($data as $item) {
                    $records[] = [
                        'id_pekerjaan' => $item['id_pekerjaan'],
                        'nama_pekerjaan' => $item['nama_pekerjaan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($records)) {
                    RefPekerjaan::upsert($records, ['id_pekerjaan'], ['nama_pekerjaan', 'updated_at']);
                }
            }
            return [
                'synced' => $batchCount,
                'total' => $batchCount,
                'total_all' => $batchCount,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncPekerjaan failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncPenghasilan(?string $syncSince = null): array
    {
        try {
            $response = $this->neoFeeder->getPenghasilan();
            $batchCount = 0;
            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $now = now();
                $records = [];
                foreach ($data as $item) {
                    $records[] = [
                        'id_penghasilan' => $item['id_penghasilan'],
                        'nama_penghasilan' => $item['nama_penghasilan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($records)) {
                    RefPenghasilan::upsert($records, ['id_penghasilan'], ['nama_penghasilan', 'updated_at']);
                }
            }
            return [
                'synced' => $batchCount,
                'total' => $batchCount,
                'total_all' => $batchCount,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncPenghasilan failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncKebutuhanKhusus(?string $syncSince = null): array
    {
        try {
            // We request without a large limit because it often causes timeouts on some Neo Feeder versions.
            // Reference data is usually small enough for default limits.
            $response = $this->neoFeeder->request('GetKebutuhanKhusus', []);
            $synced = 0;
            $skipped = 0;

            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    $id = (int)$item['id_kebutuhan_khusus'];
                    $name = $item['nama_kebutuhan_khusus'];

                    // Base categories have IDs that are powers of 2 (1, 2, 4, 8, etc.)
                    // and typically don't have commas in the name (which indicate combinations).
                    $isPowerOfTwo = ($id > 0) && (($id & ($id - 1)) === 0);
                    $isSingleName = !str_contains($name, ',');

                    if ($isPowerOfTwo || $isSingleName) {
                        RefKebutuhanKhusus::updateOrCreate(
                            ['id_kebutuhan_khusus' => $item['id_kebutuhan_khusus']],
                            ['nama_kebutuhan_khusus' => $item['nama_kebutuhan_khusus']]
                        );
                        $synced++;
                    } else {
                        $skipped++;
                    }
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced + $skipped,
                'total_all' => $synced + $skipped,
                'skipped_combinations' => $skipped,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncKebutuhanKhusus failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncPembiayaan(?string $syncSince = null): array
    {
        try {
            $response = $this->neoFeeder->getPembiayaan();
            $batchCount = 0;
            if ($response && isset($response['data'])) {
                $data = $response['data'];
                $batchCount = count($data);
                $now = now();
                $records = [];
                foreach ($data as $item) {
                    $records[] = [
                        'id_pembiayaan' => $item['id_pembiayaan'],
                        'nama_pembiayaan' => $item['nama_pembiayaan'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($records)) {
                    RefPembiayaan::upsert($records, ['id_pembiayaan'], ['nama_pembiayaan', 'updated_at']);
                }
            }
            return [
                'synced' => $batchCount,
                'total' => $batchCount,
                'total_all' => $batchCount,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncPembiayaan failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
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
