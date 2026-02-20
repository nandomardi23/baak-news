<?php

namespace App\Services\Sync;

use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Nilai;
use App\Models\AktivitasKuliah;
use App\Models\AktivitasMahasiswa;
use App\Models\AnggotaAktivitasMahasiswa;
use App\Models\BimbinganMahasiswa;
use App\Models\UjiMahasiswa;
use App\Models\KonversiKampusMerdeka;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcademicSyncService extends BaseSyncService
{
    public function syncKelasKuliah(int $offset = 0, int $limit = 2000, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountKelasKuliah();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncKelasKuliah: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getAllKelasKuliah($limit, $offset, $filter);

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
                    'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                    'id_prodi' => $item['id_prodi'],
                    'id_semester' => $item['id_semester'],
                    'id_matkul' => $item['id_matkul'],
                    'nama_kelas_kuliah' => $item['nama_kelas_kuliah'],
                    'sks' => $item['sks'],
                    'bahasan' => $item['bahasan'] ?? null,
                    'tanggal_mulai_efektif' => $item['tanggal_mulai_efektif'] ?? null,
                    'tanggal_akhir_efektif' => $item['tanggal_akhir_efektif'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            try {
                foreach (array_chunk($records, 500) as $chunk) {
                    KelasKuliah::upsert(
                        $chunk,
                        ['id_kelas_kuliah'],
                        ['id_prodi', 'id_semester', 'id_matkul', 'nama_kelas_kuliah', 'sks', 'bahasan', 'tanggal_mulai_efektif', 'tanggal_akhir_efektif', 'updated_at']
                    );
                }
                $synced = count($records);
            } catch (\Exception $e) {
                $errors[] = "Kelas Kuliah Batch Error: " . $e->getMessage();
                Log::error("KelasKuliah batch upsert failed", ['error' => $e->getMessage()]);
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

    public function syncDosenPengajar(int $offset = 0, int $limit = 2000, ?string $syncSince = null): array
    {
        // 1. Get total count from API
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountDosenPengajar();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncDosenPengajar: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch ALL dosen pengajar records in bulk (single API call with pagination)
        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getAllDosenPengajarKelasKuliah($limit, $offset, $filter);

        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
            // 3. Pre-fetch local ID mappings (2 queries)
            $apiKelasIds = collect($data)->pluck('id_kelas_kuliah')->unique()->filter()->toArray();
            $apiDosenIds = collect($data)->pluck('id_dosen')->unique()->filter()->toArray();

            // Map: id_kelas_kuliah (NeoFeeder) -> local kelas_kuliah.id
            $kelasMap = KelasKuliah::whereIn('id_kelas_kuliah', $apiKelasIds)
                ->pluck('id', 'id_kelas_kuliah');

            // Map: id_dosen (NeoFeeder) -> local dosen.id
            $dosenMap = \App\Models\Dosen::whereIn('id_dosen', $apiDosenIds)
                ->pluck('id', 'id_dosen');

            // 4. Build pivot records
            $pivotRecords = [];
            $kelasDosenUpdates = []; // For legacy id_dosen column on kelas_kuliah

            foreach ($data as $item) {
                $kelasLocalId = $kelasMap[$item['id_kelas_kuliah']] ?? null;
                $dosenLocalId = $dosenMap[$item['id_dosen']] ?? null;

                if (!$kelasLocalId || !$dosenLocalId)
                    continue;

                $pivotRecords[] = [
                    'kelas_kuliah_id' => $kelasLocalId,
                    'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                    'dosen_id' => $dosenLocalId,
                    'id_dosen' => $item['id_dosen'],
                    'id_aktivitas_mengajar' => $item['id_aktivitas_mengajar'] ?? null,
                    'id_registrasi_dosen' => $item['id_registrasi_dosen'] ?? null,
                    'sks_substansi_total' => $item['sks_substansi_total'] ?? 0,
                    'rencana_tatap_muka' => $item['rencana_tatap_muka'] ?? 0,
                    'realisasi_tatap_muka' => $item['realisasi_tatap_muka'] ?? 0,
                    'id_jenis_evaluasi' => $item['id_jenis_evaluasi'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];

                // Track last dosen for legacy column
                $kelasDosenUpdates[$item['id_kelas_kuliah']] = $item['id_dosen'];
            }

            // 5. Batch upsert pivot table
            if (!empty($pivotRecords)) {
                try {
                    foreach (array_chunk($pivotRecords, 500) as $chunk) {
                        DB::table('dosen_pengajar_kelas')->upsert(
                            $chunk,
                            ['kelas_kuliah_id', 'dosen_id'],
                            ['id_aktivitas_mengajar', 'id_registrasi_dosen', 'sks_substansi_total', 'rencana_tatap_muka', 'realisasi_tatap_muka', 'id_jenis_evaluasi', 'updated_at']
                        );
                    }
                    $synced = count($pivotRecords);
                } catch (\Exception $e) {
                    $errors[] = "Dosen Pengajar Batch Error: " . $e->getMessage();
                    Log::error("DosenPengajar batch upsert failed", ['error' => $e->getMessage()]);
                }
            }

            // 6. Batch update legacy id_dosen column on kelas_kuliah
            if (!empty($kelasDosenUpdates)) {
                try {
                    $dosenLocalMap = \App\Models\Dosen::whereIn('id_dosen', array_values($kelasDosenUpdates))
                        ->pluck('id', 'id_dosen');

                    foreach (array_chunk(array_keys($kelasDosenUpdates), 500) as $chunk) {
                        foreach ($chunk as $kelasId) {
                            $dosenIdNeo = $kelasDosenUpdates[$kelasId];
                            $dosenLocalId = $dosenLocalMap[$dosenIdNeo] ?? null;
                            if ($dosenLocalId) {
                                KelasKuliah::where('id_kelas_kuliah', $kelasId)
                                    ->update(['id_dosen' => $dosenLocalId]);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("DosenPengajar legacy update failed: " . $e->getMessage());
                }
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

    public function syncKrs(int $offset = 0, int $limit = 500, ?string $idSemester = null, ?string $syncSince = null): array
    {
        if (!$idSemester) {
            return $this->syncKrsAllSemesters($offset, $limit, $syncSince);
        }

        // 1. Get total count
        $totalAll = 0;
        $filter = $this->getFilter("id_periode = '{$idSemester}'", $syncSince);

        try {
            $countResponse = $this->neoFeeder->requestQuick('GetCountKRSMahasiswa', ['filter' => $filter]);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncKrs: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch bulk data
        $dateFilter = $syncSince ? $this->getFilter('', $syncSince) : '';
        $response = $this->neoFeeder->getKrsBySemester($idSemester, $limit, $offset, $dateFilter);
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
            // === PHASE 0: Pre-fetch all lookup mappings (3 queries) ===
            $idRegMahasiswas = collect($data)->pluck('id_registrasi_mahasiswa')->unique()->filter()->toArray();
            $idMatkuls = collect($data)->pluck('id_matkul')->unique()->filter()->toArray();

            $mahasiswaMap = Mahasiswa::whereIn('id_registrasi_mahasiswa', $idRegMahasiswas)
                ->pluck('id', 'id_registrasi_mahasiswa');

            $matkulMap = \App\Models\MataKuliah::whereIn('id_matkul', $idMatkuls)
                ->pluck('id', 'id_matkul');

            $semesterId = \App\Models\TahunAkademik::where('id_semester', $idSemester)->value('id');

            // === PHASE 1: Batch upsert KRS headers (1 query) ===
            $krsRecords = [];
            $seenKrsKeys = [];

            foreach ($data as $item) {
                $mahasiswaId = $mahasiswaMap[$item['id_registrasi_mahasiswa']] ?? null;
                if (!$mahasiswaId)
                    continue;

                $krsKey = $item['id_registrasi_mahasiswa'] . '|' . $item['id_periode'];
                if (isset($seenKrsKeys[$krsKey]))
                    continue; // Skip duplicate headers in same batch
                $seenKrsKeys[$krsKey] = true;

                $krsRecords[] = [
                    'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                    'id_semester' => $item['id_periode'],
                    'mahasiswa_id' => $mahasiswaId,
                    'tahun_akademik_id' => $semesterId,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            if (!empty($krsRecords)) {
                try {
                    Krs::upsert(
                        $krsRecords,
                        ['id_registrasi_mahasiswa', 'id_semester'],
                        ['mahasiswa_id', 'tahun_akademik_id', 'updated_at']
                    );
                } catch (\Exception $e) {
                    $errors[] = "KRS Header Batch Error: " . $e->getMessage();
                    Log::error("KRS Header Batch upsert failed", ['error' => $e->getMessage()]);
                }
            }

            // === PHASE 2: Fetch back KRS IDs for detail linking (1 query) ===
            $krsIdMap = Krs::whereIn('id_registrasi_mahasiswa', array_values($idRegMahasiswas))
                ->where('id_semester', $idSemester)
                ->pluck('id', 'id_registrasi_mahasiswa');

            // === PHASE 3: Batch upsert KRS details (1 query) ===
            $detailRecords = [];
            foreach ($data as $item) {
                $krsId = $krsIdMap[$item['id_registrasi_mahasiswa']] ?? null;
                if (!$krsId)
                    continue;

                $matkulLocalId = $matkulMap[$item['id_matkul']] ?? null;

                $detailRecords[] = [
                    'krs_id' => $krsId,
                    'id_matkul' => $item['id_matkul'],
                    'id_kelas_kuliah' => $item['id_kelas_kuliah'] ?? null,
                    'mata_kuliah_id' => $matkulLocalId,
                    'kode_mata_kuliah' => $item['kode_mata_kuliah'],
                    'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                    'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                    'nama_kelas_kuliah' => $item['nama_kelas_kuliah'] ?? null,
                    'angkatan' => $item['angkatan'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            if (!empty($detailRecords)) {
                try {
                    // Batch in chunks of 500 to avoid MySQL max_allowed_packet limits
                    foreach (array_chunk($detailRecords, 500) as $chunk) {
                        KrsDetail::upsert(
                            $chunk,
                            ['krs_id', 'id_matkul'],
                            ['id_kelas_kuliah', 'mata_kuliah_id', 'kode_mata_kuliah', 'nama_mata_kuliah', 'sks_mata_kuliah', 'nama_kelas_kuliah', 'angkatan', 'updated_at']
                        );
                    }
                    $synced = count($detailRecords);
                } catch (\Exception $e) {
                    $errors[] = "KRS Detail Batch Error: " . $e->getMessage();
                    Log::error("KRS Detail Batch upsert failed", ['error' => $e->getMessage()]);
                }
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

    public function syncNilai(int $offset = 0, int $limit = 2000, ?string $idSemester = null, ?string $syncSince = null): array
    {
        if (!$idSemester) {
            return $this->syncNilaiAllSemesters($offset, $limit, $syncSince);
        }

        // 1. Get total count
        $totalAll = 0;
        $filter = $this->getFilter("id_semester = '{$idSemester}'", $syncSince);

        try {
            $countResponse = $this->neoFeeder->requestQuick('GetCountNilaiPerkuliahanKelas', ['filter' => $filter]);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncNilai: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch bulk data
        $dateFilter = $syncSince ? $this->getFilter('', $syncSince) : '';
        $response = $this->neoFeeder->getNilaiBySemester($idSemester, $limit, $offset, $dateFilter);
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
            // Optimization: Pre-fetch mappings
            $idRegMahasiswas = collect($data)->pluck('id_registrasi_mahasiswa')->unique()->filter()->toArray();
            $idMatkuls = collect($data)->pluck('id_matkul')->unique()->filter()->toArray();

            $mahasiswaMap = Mahasiswa::whereIn('id_registrasi_mahasiswa', $idRegMahasiswas)
                ->pluck('id', 'id_registrasi_mahasiswa');

            $matkulMap = \App\Models\MataKuliah::whereIn('id_matkul', $idMatkuls)
                ->pluck('id', 'id_matkul');

            $semesterId = \App\Models\TahunAkademik::where('id_semester', $idSemester)->value('id');

            $upsertData = [];
            foreach ($data as $item) {
                // Skip if local mahasiswa not found
                $mahasiswaId = $mahasiswaMap[$item['id_registrasi_mahasiswa']] ?? null;
                if (!$mahasiswaId)
                    continue;

                $matkulId = $matkulMap[$item['id_matkul']] ?? null;

                // Validasi: Jangan insert jika Mata Kuliah belum disync (Mencegah Constraint Violation)
                if (!$matkulId) {
                    $errors[] = "Nilai {$item['nim']} - {$item['kode_mata_kuliah']}: Mata Kuliah belum disinkronisasi (ID: {$item['id_matkul']})";
                    continue;
                }

                $upsertData[] = [
                    'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                    'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                    'id_matkul' => $item['id_matkul'],
                    // Local IDs
                    'mahasiswa_id' => $mahasiswaId,
                    'mata_kuliah_id' => $matkulId,
                    'tahun_akademik_id' => $semesterId,
                    // Data
                    'nilai_angka' => $item['nilai_angka'] ?? 0,
                    'nilai_huruf' => $item['nilai_huruf'] ?? '',
                    'nilai_indeks' => $item['nilai_indeks'] ?? 0,
                    'id_periode' => $item['id_semester'],
                    'nama_mata_kuliah' => $item['nama_mata_kuliah'] ?? '',
                    'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                    'updated_at' => now(),
                ];
            }

            // Batch Upsert
            if (!empty($upsertData)) {
                try {
                    Nilai::upsert(
                        $upsertData,
                        ['id_registrasi_mahasiswa', 'id_kelas_kuliah', 'id_matkul'],
                        ['nilai_angka', 'nilai_huruf', 'nilai_indeks', 'updated_at', 'mahasiswa_id', 'mata_kuliah_id']
                    );
                    $synced = count($upsertData);
                } catch (\Exception $e) {
                    // Fallback to loop if bulk fails
                    foreach ($data as $item) {
                        try {
                            $mahasiswaId = $mahasiswaMap[$item['id_registrasi_mahasiswa']] ?? null;
                            $matkulId = $matkulMap[$item['id_matkul']] ?? null;

                            if (!$mahasiswaId)
                                continue;

                            // Validasi Check Again
                            if (!$matkulId) {
                                // Already logged in first pass, but good to be safe if Logic changes
                                continue;
                            }

                            Nilai::updateOrCreate(
                                [
                                    'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                                    'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                                    'id_matkul' => $item['id_matkul']
                                ],
                                [
                                    'mahasiswa_id' => $mahasiswaId,
                                    'mata_kuliah_id' => $matkulId,
                                    'tahun_akademik_id' => $semesterId,
                                    'nilai_angka' => $item['nilai_angka'] ?? 0,
                                    'nilai_huruf' => $item['nilai_huruf'] ?? '',
                                    'nilai_indeks' => $item['nilai_indeks'] ?? 0,
                                    'id_periode' => $item['id_semester'],
                                    'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                                    'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                                ]
                            );
                            $synced++;
                        } catch (\Exception $inner) {
                            $errors[] = "Nilai Line Error: " . $inner->getMessage();
                        }
                    }
                }
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
     * Sync Aktivitas Kuliah (AKM) - Optimized for Bulk
     */
    public function syncAktivitas(int $offset = 0, int $limit = 1000, ?string $idSemester = null, ?string $syncSince = null): array
    {
        if (!$idSemester) {
            // Fallback to student-by-student if no semester (slow, but keeps legacy compat)
            return $this->syncAktivitasLegacy($offset, $limit);
        }

        // Optimization: Skip future semesters
        $maxSemester = (date('Y') + 1) . '3';
        if ($idSemester > $maxSemester) {
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 100,
                'has_more' => false,
                'message' => 'Semester masa depan dilewati'
            ];
        }

        $baseFilter = "id_semester = '{$idSemester}'";
        $filter = $this->getFilter($baseFilter, $syncSince);

        // 1. Get total count for this semester
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->requestQuick('GetCountAktivitasKuliahMahasiswa', ['filter' => $filter]);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncAktivitas: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch bulk data
        $response = $this->neoFeeder->request('GetAktivitasKuliahMahasiswa', [
            'filter' => $filter,
            'limit' => $limit,
            'offset' => $offset
        ]);

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
                    'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                    'id_semester' => $item['id_semester'],
                    'nim' => $item['nim'],
                    'nama_mahasiswa' => $item['nama_mahasiswa'],
                    'id_status_mahasiswa' => $item['id_status_mahasiswa'],
                    'ips' => $item['ips'] ?? 0,
                    'ipk' => $item['ipk'] ?? 0,
                    'sks_semester' => $item['sks_semester'] ?? 0,
                    'sks_total' => $item['sks_total'] ?? 0,
                    'biaya_kuliah_smt' => $item['biaya_kuliah_smt'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Simpan ke tabel aktivitas_kuliah
            try {
                AktivitasKuliah::upsert(
                    $records,
                    ['id_registrasi_mahasiswa', 'id_semester'],
                    ['nim', 'nama_mahasiswa', 'id_status_mahasiswa', 'ips', 'ipk', 'sks_semester', 'sks_total', 'biaya_kuliah_smt', 'updated_at']
                );
                $synced = count($records);
            } catch (\Exception $e) {
                // Fallback: If bulk fails, try one by one to find the culprit
                foreach ($records as $record) {
                    try {
                        AktivitasKuliah::updateOrCreate(
                            [
                                'id_registrasi_mahasiswa' => $record['id_registrasi_mahasiswa'],
                                'id_semester' => $record['id_semester']
                            ],
                            collect($record)->except(['id_registrasi_mahasiswa', 'id_semester', 'created_at'])->toArray()
                        );
                        $synced++;
                    } catch (\Exception $inner) {
                        $errors[] = "Aktivitas {$record['nim']} (Sem: {$record['id_semester']}): " . $inner->getMessage();
                    }
                }
            }

            // Update IPK, IPS, SKS di tabel mahasiswa (ambil yang terbaru per mahasiswa)
            // Wrap in try-catch to be safe
            try {
                $this->updateMahasiswaAkademik($data);
            } catch (\Exception $e) {
                Log::warning("UpdateMahasiswaAkademik Failed: " . $e->getMessage());
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
     * Legacy student-by-student sync (Slow)
     */
    private function syncAktivitasLegacy(int $offset = 0, int $limit = 50): array
    {
        $totalStudents = Mahasiswa::count();
        $students = Mahasiswa::select('id_registrasi_mahasiswa', 'nim')
            ->skip($offset)
            ->take($limit)
            ->get();

        $batchCount = $students->count();
        $synced = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                $akmData = $this->neoFeeder->getAktivitasKuliahMahasiswa($student->id_registrasi_mahasiswa);
                if ($akmData && isset($akmData['data']) && !empty($akmData['data'])) {
                    // Simpan ke tabel aktivitas_kuliah
                    foreach ($akmData['data'] as $item) {
                        AktivitasKuliah::updateOrCreate(
                            [
                                'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                                'id_semester' => $item['id_semester'],
                            ],
                            [
                                'nim' => $item['nim'],
                                'nama_mahasiswa' => $item['nama_mahasiswa'],
                                'id_status_mahasiswa' => $item['id_status_mahasiswa'],
                                'ips' => $item['ips'] ?? 0,
                                'ipk' => $item['ipk'] ?? 0,
                                'sks_semester' => $item['sks_semester'] ?? 0,
                                'sks_total' => $item['sks_total'] ?? 0,
                                'biaya_kuliah_smt' => $item['biaya_kuliah_smt'] ?? 0,
                            ]
                        );
                    }

                    // Update mahasiswa table juga
                    $this->updateMahasiswaAkademik($akmData['data']);
                    $synced++;
                }
            } catch (\Exception $e) {
                $errors[] = "Aktivitas {$student->nim}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $nextOffset < $totalStudents;
        $progress = $totalStudents > 0 ? min(100, round($nextOffset / $totalStudents * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalStudents,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }

    public function syncBimbinganMahasiswa(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountBimbingMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncBimbingan: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getBimbingMahasiswa($limit, $offset, $filter);

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
                $idAktivitas = $item['id_aktivitas_mahasiswa'] ?? null;
                $idDosen = $item['id_dosen'] ?? null;

                if (!$idAktivitas || !$idDosen)
                    continue;

                $records[] = [
                    'id_aktivitas_mahasiswa' => $idAktivitas,
                    'id_dosen' => $idDosen,
                    'id_bimbingan_mahasiswa' => $item['id_bimbingan_mahasiswa'] ?? md5($idAktivitas . $idDosen),
                    'pembimbing_ke' => $item['pembimbing_ke'] ?? null,
                    'id_kategori_kegiatan' => $item['id_kategori_kegiatan'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            if (!empty($records)) {
                try {
                    foreach (array_chunk($records, 500) as $chunk) {
                        BimbinganMahasiswa::upsert(
                            $chunk,
                            ['id_aktivitas_mahasiswa', 'id_dosen'],
                            ['id_bimbingan_mahasiswa', 'pembimbing_ke', 'id_kategori_kegiatan', 'updated_at']
                        );
                    }
                    $synced = count($records);
                } catch (\Exception $e) {
                    $errors[] = "Bimbingan Batch Error: " . $e->getMessage();
                    Log::error("Bimbingan batch upsert failed", ['error' => $e->getMessage()]);
                }
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

    public function syncUjiMahasiswa(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountUjiMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncUji: GetCount failed. Error: " . $e->getMessage());
        }

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getUjiMahasiswa($limit, $offset, $filter);

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
                    'id_uji_mahasiswa' => $item['id_uji_mahasiswa'],
                    'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'] ?? '',
                    'id_dosen' => $item['id_dosen'] ?? '',
                    'penguji_ke' => $item['penguji_ke'] ?? null,
                    'id_kategori_kegiatan' => $item['id_kategori_kegiatan'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            try {
                foreach (array_chunk($records, 500) as $chunk) {
                    UjiMahasiswa::upsert(
                        $chunk,
                        ['id_uji_mahasiswa'],
                        ['id_aktivitas_mahasiswa', 'id_dosen', 'penguji_ke', 'id_kategori_kegiatan', 'updated_at']
                    );
                }
                $synced = count($records);
            } catch (\Exception $e) {
                $errors[] = "Uji Batch Error: " . $e->getMessage();
                Log::error("Uji batch upsert failed", ['error' => $e->getMessage()]);
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

    public function syncAktivitasMahasiswa(int $offset = 0, int $limit = 500, ?string $idSemester = null, ?string $syncSince = null): array
    {
        $totalAll = 0;
        $baseFilter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);

        try {
            $countResponse = $this->neoFeeder->getCountAktivitasMahasiswa($filter);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncAktivitasMhs: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getAktivitasMahasiswa($limit, $offset, $filter);

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
                $idAktivitas = $item['id_aktivitas'] ?? $item['id_aktivitas_mahasiswa'] ?? null;
                if (!$idAktivitas)
                    continue;

                $records[] = [
                    'id_aktivitas' => $idAktivitas,
                    'id_jenis_aktivitas' => $item['id_jenis_aktivitas'] ?? null,
                    'nama_jenis_aktivitas' => $item['nama_jenis_aktivitas'] ?? null,
                    'id_prodi' => $item['id_prodi'] ?? null,
                    'id_semester' => $item['id_semester'] ?? null,
                    'judul_aktivitas_mahasiswa' => $item['judul_aktivitas_mahasiswa'] ?? $item['judul'] ?? null,
                    'keterangan_aktivitas_mahasiswa' => $item['keterangan_aktivitas_mahasiswa'] ?? $item['keterangan'] ?? null,
                    'lokasi_kegiatan' => $item['lokasi_kegiatan'] ?? $item['lokasi'] ?? null,
                    'sk_tugas' => $item['sk_tugas'] ?? null,
                    'tanggal_sk_tugas' => $item['tanggal_sk_tugas'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            if (!empty($records)) {
                try {
                    foreach (array_chunk($records, 500) as $chunk) {
                        AktivitasMahasiswa::upsert(
                            $chunk,
                            ['id_aktivitas'],
                            ['id_jenis_aktivitas', 'nama_jenis_aktivitas', 'id_prodi', 'id_semester', 'judul_aktivitas_mahasiswa', 'keterangan_aktivitas_mahasiswa', 'lokasi_kegiatan', 'sk_tugas', 'tanggal_sk_tugas', 'updated_at']
                        );
                    }
                    $synced = count($records);
                } catch (\Exception $e) {
                    $errors[] = "Aktivitas Mhs Batch Error: " . $e->getMessage();
                    Log::error("AktivitasMahasiswa batch upsert failed", ['error' => $e->getMessage()]);
                }
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

    public function syncAnggotaAktivitasMahasiswa(int $offset = 0, int $limit = 500, ?string $idSemester = null, ?string $syncSince = null): array
    {
        $totalAll = 0;
        $baseFilter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);

        try {
            $countResponse = $this->neoFeeder->getCountAnggotaAktivitasMahasiswa($filter);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncAnggotaAktivitas: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getAnggotaAktivitasMahasiswa($limit, $offset, $filter);

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
                $idAktivitas = $item['id_aktivitas'] ?? $item['id_aktivitas_mahasiswa'] ?? null;

                $records[] = [
                    'id_anggota' => $item['id_anggota'],
                    'id_aktivitas' => $idAktivitas,
                    'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                    'nim' => $item['nim'],
                    'nama_mahasiswa' => $item['nama_mahasiswa'],
                    'id_peran_anggota' => $item['id_peran_anggota'],
                    'nama_peran_anggota' => $item['nama_peran_anggota'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            try {
                foreach (array_chunk($records, 500) as $chunk) {
                    AnggotaAktivitasMahasiswa::upsert(
                        $chunk,
                        ['id_anggota'],
                        ['id_aktivitas', 'id_registrasi_mahasiswa', 'nim', 'nama_mahasiswa', 'id_peran_anggota', 'nama_peran_anggota', 'updated_at']
                    );
                }
                $synced = count($records);
            } catch (\Exception $e) {
                $errors[] = "Anggota Batch Error: " . $e->getMessage();
                Log::error("AnggotaAktivitas batch upsert failed", ['error' => $e->getMessage()]);
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

    public function syncKonversiKampusMerdeka(int $offset = 0, int $limit = 500, ?string $idSemester = null, ?string $syncSince = null): array
    {
        $totalAll = 0;
        $baseFilter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);

        try {
            $countResponse = $this->neoFeeder->getCountKonversiKampusMerdeka($filter);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncKonversi: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getKonversiKampusMerdeka($limit, $offset, $filter);

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
                    'id_konversi_aktivitas' => $item['id_konversi_aktivitas'],
                    'id_matkul' => $item['id_matkul'],
                    'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                    'sks_mata_kuliah' => $item['sks_mata_kuliah'],
                    'nilai_angka' => $item['nilai_angka'],
                    'nilai_huruf' => $item['nilai_huruf'],
                    'nilai_indeks' => $item['nilai_indeks'],
                    'id_semester' => $item['id_semester'],
                    'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'] ?? null,
                    'judul_aktivitas_mahasiswa' => $item['judul_aktivitas_mahasiswa'] ?? null,
                    'id_anggota' => $item['id_anggota'] ?? null,
                    'nim' => $item['nim'] ?? null,
                    'nama_mahasiswa' => $item['nama_mahasiswa'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            try {
                foreach (array_chunk($records, 500) as $chunk) {
                    KonversiKampusMerdeka::upsert(
                        $chunk,
                        ['id_konversi_aktivitas'],
                        ['id_matkul', 'nama_mata_kuliah', 'sks_mata_kuliah', 'nilai_angka', 'nilai_huruf', 'nilai_indeks', 'id_semester', 'id_aktivitas_mahasiswa', 'judul_aktivitas_mahasiswa', 'id_anggota', 'nim', 'nama_mahasiswa', 'updated_at']
                    );
                }
                $synced = count($records);
            } catch (\Exception $e) {
                $errors[] = "Konversi Batch Error: " . $e->getMessage();
                Log::error("KonversiKampusMerdeka batch upsert failed", ['error' => $e->getMessage()]);
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
     * Sync KRS tanpa filter semester - ambil semua semester yang ada di DB
     */
    public function syncKrsAllSemesters(int $offset, int $limit, ?string $syncSince = null): array
    {
        // STRATEGI BARU V2: State Encoding di Offset
        // Offset = (SemesterIndex * 1,000,000) + InternalOffset
        // Ini memungkinkan kita memecah 1 semester menjadi banyak request kecil tanpa looping internal (anti-timeout).

        $OFFSET_MULTIPLIER = 1000000;
        $semesterIndex = intdiv($offset, $OFFSET_MULTIPLIER);
        $internalOffset = $offset % $OFFSET_MULTIPLIER;

        // 1. Ambil daftar semester (Prioritas Aktivitas Kuliah)
        $semesters = AktivitasKuliah::select('id_semester')
            ->distinct()
            ->orderBy('id_semester', 'desc')
            ->pluck('id_semester');

        // Fallback ke TahunAkademik jika kosong
        if ($semesters->isEmpty()) {
            $maxSemester = (date('Y') + 1) . '3';
            $semesters = \App\Models\TahunAkademik::where('id_semester', '<=', $maxSemester)
                ->orderBy('id_semester', 'desc')
                ->pluck('id_semester');
        }

        if ($semesters->isEmpty()) {
            return [
                'total' => 0,
                'synced' => 0,
                'errors' => ['Belum ada data Semester.'],
                'total_all' => 0,
                'has_more' => false,
                'progress' => 0,
            ];
        }

        // 2. Cek apakah index valid
        if ($semesterIndex >= $semesters->count()) {
            return [
                'total' => 0,
                'synced' => 0,
                'errors' => [],
                'total_all' => $semesters->count() * $OFFSET_MULTIPLIER,
                'has_more' => false,
                'progress' => 100,
            ];
        }

        $currentSemester = $semesters[$semesterIndex];

        // 3. Sync HANYA batch ini (Limit sesuai request, misal 100)
        // Kita gunakan $limit yang dikirim frontend (misal 100), bukan loop sampai habis.
        // STOP LOOPING! Looping di sini bikin timeout. Biarkan frontend yang looping request.
        $result = $this->syncKrs($internalOffset, $limit, $currentSemester, $syncSince);

        // 4. Tentukan Next Offset
        // Jika semester ini masih ada data ($result['has_more']), kita lanjut di semester yang sama
        // Jika habis, kita lompat ke semester berikutnya (Index + 1, Offset 0)

        $hasMoreDataInSemester = $result['has_more'];

        if ($hasMoreDataInSemester) {
            $nextInternalOffset = $result['next_offset']; // Biasanya current + limit
            $newGlobalOffset = ($semesterIndex * $OFFSET_MULTIPLIER) + $nextInternalOffset;
            $hasMore = true;
        } else {
            // Pindah ke semester berikutnya
            $newGlobalOffset = ($semesterIndex + 1) * $OFFSET_MULTIPLIER;
            $hasMore = ($semesterIndex + 1) < $semesters->count();
        }

        // 5. Progress Calculation
        $totalAllScaled = $semesters->count() * $OFFSET_MULTIPLIER;
        // Progress sekarang = Offset Global / Total Scaled

        // Kita kembalikan total dari batch ini saja
        return [
            'total' => $result['total'],
            'synced' => $result['synced'],
            'errors' => $result['errors'] ?? [],
            'total_all' => $totalAllScaled, // Kunci agar progress bar jalan mulus
            'offset' => $offset,
            'next_offset' => $hasMore ? $newGlobalOffset : null,
            'has_more' => $hasMore,
            'progress' => min(100, round(($offset / $totalAllScaled) * 100)),
            'message' => "Sync Semester {$currentSemester}..."
        ];
    }

    /**
     * Sync Nilai tanpa filter semester - ambil semua semester yang ada di DB
     */
    public function syncNilaiAllSemesters(int $offset, int $limit, ?string $syncSince = null): array
    {
        // STRATEGI BARU V2: State Encoding di Offset (Sama dengan KRS)

        $OFFSET_MULTIPLIER = 1000000;
        $semesterIndex = intdiv($offset, $OFFSET_MULTIPLIER);
        $internalOffset = $offset % $OFFSET_MULTIPLIER;

        // 1. Ambil daftar semester (Prioritas Aktivitas Kuliah)
        $semesters = AktivitasKuliah::select('id_semester')
            ->distinct()
            ->orderBy('id_semester', 'desc')
            ->pluck('id_semester');

        // Fallback
        if ($semesters->isEmpty()) {
            $maxSemester = (date('Y') + 1) . '3';
            $semesters = \App\Models\TahunAkademik::where('id_semester', '<=', $maxSemester)
                ->orderBy('id_semester', 'desc')
                ->pluck('id_semester');
        }

        if ($semesters->isEmpty()) {
            return [
                'total' => 0,
                'synced' => 0,
                'errors' => ['Belum ada data Semester atau Aktivitas Kuliah.'],
                'total_all' => 0,
                'has_more' => false,
                'progress' => 0,
            ];
        }

        if ($semesterIndex >= $semesters->count()) {
            return [
                'total' => 0,
                'synced' => 0,
                'errors' => [],
                'total_all' => $semesters->count() * $OFFSET_MULTIPLIER,
                'has_more' => false,
                'progress' => 100,
            ];
        }

        $currentSemester = $semesters[$semesterIndex];

        // 3. Sync HANYA batch ini
        $result = $this->syncNilai($internalOffset, $limit, $currentSemester, $syncSince);

        // 4. Determine Next Offset
        $hasMoreDataInSemester = $result['has_more'];

        if ($hasMoreDataInSemester) {
            $nextInternalOffset = $result['next_offset'];
            $newGlobalOffset = ($semesterIndex * $OFFSET_MULTIPLIER) + $nextInternalOffset;
            $hasMore = true;
        } else {
            // Next semester
            $newGlobalOffset = ($semesterIndex + 1) * $OFFSET_MULTIPLIER;
            $hasMore = ($semesterIndex + 1) < $semesters->count();
        }

        // 5. Progress Calculation
        $totalAllScaled = $semesters->count() * $OFFSET_MULTIPLIER;

        return [
            'total' => $result['total'],
            'synced' => $result['synced'],
            'errors' => $result['errors'] ?? [],
            'total_all' => $totalAllScaled,
            'offset' => $offset,
            'next_offset' => $hasMore ? $newGlobalOffset : null,
            'has_more' => $hasMore,
            'progress' => min(100, round(($offset / $totalAllScaled) * 100)),
            'message' => "Sync Semester {$currentSemester}..."
        ];
    }

    /**
     * Update IPK, IPS, dan SKS di tabel mahasiswa dari data AKM
     */
    private function updateMahasiswaAkademik(array $data): void
    {
        // Group by mahasiswa, ambil record dengan semester terbaru
        $latestPerStudent = [];
        foreach ($data as $item) {
            $idReg = $item['id_registrasi_mahasiswa'];
            if (!isset($latestPerStudent[$idReg]) || $item['id_semester'] > $latestPerStudent[$idReg]['id_semester']) {
                $latestPerStudent[$idReg] = $item;
            }
        }

        foreach ($latestPerStudent as $idReg => $item) {
            try {
                Mahasiswa::where('id_registrasi_mahasiswa', $idReg)->update([
                    'ipk' => $item['ipk'] ?? 0,
                    'ips' => $item['ips'] ?? 0,
                    'sks_total' => $item['sks_total'] ?? 0,
                ]);
            } catch (\Exception $e) {
                Log::warning("UpdateMahasiswaAkademik: Error for {$idReg}: " . $e->getMessage());
            }
        }
    }
    public function getCountKonversiKampusMerdeka(?string $idSemester = null, ?string $syncSince = null): int
    {
        $baseFilter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);
        try {
            $response = $this->neoFeeder->getCountKonversiKampusMerdeka($filter);
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountKelasKuliah(): int
    {
        try {
            $response = $this->neoFeeder->getCountKelasKuliah();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountDosenPengajar(): int
    {
        try {
            // Note: syncing uses loop over local classes, but global count is useful for total progress
            $response = $this->neoFeeder->getCountDosenPengajar();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountKrs(?string $idSemester = null, ?string $syncSince = null): int
    {
        $filter = $idSemester ? "id_periode = '{$idSemester}'" : "";
        $filter = $this->getFilter($filter, $syncSince);
        try {
            $response = $this->neoFeeder->requestQuick('GetCountKRSMahasiswa', ['filter' => $filter]);
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountNilai(?string $idSemester = null, ?string $syncSince = null): int
    {
        $filter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($filter, $syncSince);
        try {
            $response = $this->neoFeeder->requestQuick('GetCountNilaiPerkuliahanKelas', ['filter' => $filter]);
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountAktivitas(?string $idSemester = null, ?string $syncSince = null): int
    {
        $baseFilter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);
        try {
            $response = $this->neoFeeder->requestQuick('GetCountAktivitasKuliahMahasiswa', ['filter' => $filter]);
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountBimbingan(): int
    {
        try {
            $response = $this->neoFeeder->getCountBimbingMahasiswa();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountUji(): int
    {
        try {
            $response = $this->neoFeeder->getCountUjiMahasiswa();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountAktivitasMahasiswa(?string $idSemester = null, ?string $syncSince = null): int
    {
        $baseFilter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);
        try {
            $response = $this->neoFeeder->getCountAktivitasMahasiswa($filter);
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountAnggotaAktivitas(?string $idSemester = null, ?string $syncSince = null): int
    {
        $baseFilter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);
        try {
            $response = $this->neoFeeder->getCountAnggotaAktivitasMahasiswa($filter);
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
