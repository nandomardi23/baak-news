<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\ProgramStudi;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Log;

class NeoFeederSyncService
{
    protected NeoFeederService $neoFeeder;

    public function __construct(NeoFeederService $neoFeeder)
    {
        $this->neoFeeder = $neoFeeder;
    }

    /**
     * Sync Program Studi
     *
     * @return array{total: int, synced: int, inserted: int, updated: int, skipped: int, errors: array}
     */
    public function syncProdi(): array
    {
        $response = $this->neoFeeder->getProdi();
        return $this->processResponse($response, 'Program Studi', function ($item) {
            $existing = ProgramStudi::where('id_prodi', $item['id_prodi'])->first();
            
            $data = [
                'kode_prodi' => $item['kode_program_studi'] ?? '',
                'nama_prodi' => $item['nama_program_studi'] ?? '',
                'jenjang' => $item['jenjang_pendidikan'] ?? '',
                'jenis_program' => $item['jenis_program'] ?? 'reguler',
                'is_active' => true,
            ];
            
            if (!$existing) {
                ProgramStudi::create(array_merge(['id_prodi' => $item['id_prodi']], $data));
                return 'inserted';
            }
            
            // Check if any data changed
            $hasChanges = false;
            foreach ($data as $key => $value) {
                if ($existing->$key != $value) {
                    $hasChanges = true;
                    break;
                }
            }
            
            if ($hasChanges) {
                $existing->update($data);
                return 'updated';
            }
            
            return 'skipped';
        }, 'nama_program_studi');
    }

    /**
     * Sync Semester
     *
     * @return array{total: int, synced: int, errors: array}
     */
    public function syncSemester(): array
    {
        $response = $this->neoFeeder->getSemester();
        
        // Calculate filter range
        $currentYear = date('Y');
        $maxSemesterId = ($currentYear + 1) . '3';
        $minSemesterId = '20151';

        return $this->processResponse($response, 'Semester', function ($item) use ($minSemesterId, $maxSemesterId) {
            $idSemester = $item['id_semester'] ?? '';
            
            // Filter garbage/future data
            if ($idSemester < $minSemesterId || $idSemester > $maxSemesterId) {
                return; // Skip this item
            }

            $namaSemester = $item['nama_semester'] ?? '';
            preg_match('/(\d{4})/', $namaSemester, $matches);
            $tahun = $matches[1] ?? 0;
            $sem = str_contains(strtolower($namaSemester), 'ganjil') ? 'ganjil' : 'genap';

            TahunAkademik::updateOrCreate(
                ['id_semester' => $item['id_semester']],
                [
                    'nama_semester' => $namaSemester,
                    'tahun' => $tahun,
                    'semester' => $sem,
                    'is_active' => $item['a_periode_aktif'] ?? 0,
                    'tanggal_mulai' => $item['tanggal_mulai'] ?? null,
                    'tanggal_selesai' => $item['tanggal_selesai'] ?? null,
                ]
            );
        }, 'id_semester');
    }

    /**
     * Sync Mata Kuliah
     *
     * @return array{total: int, synced: int, errors: array}
     */
    public function syncMataKuliah(): array
    {
        // Build a map of id_prodi => local ProgramStudi id
        $prodiMap = ProgramStudi::pluck('id', 'id_prodi')->toArray();
        
        if (empty($prodiMap)) {
            throw new \Exception('Tidak ada Program Studi. Sync Program Studi terlebih dahulu.');
        }

        // Fetch all mata kuliah at once (no filter)
        $response = $this->neoFeeder->getMataKuliah();
        
        return $this->processResponse($response, 'Mata Kuliah', function ($item) use ($prodiMap) {
            $prodiId = $prodiMap[$item['id_prodi']] ?? null;
            
            MataKuliah::updateOrCreate(
                ['id_matkul' => $item['id_matkul']],
                [
                    'kode_matkul' => $item['kode_mata_kuliah'] ?? '',
                    'nama_matkul' => $item['nama_mata_kuliah'] ?? '',
                    'sks_mata_kuliah' => (int) ($item['sks_mata_kuliah'] ?? 0),
                    'sks_tatap_muka' => (int) ($item['sks_tatap_muka'] ?? 0),
                    'sks_praktek' => (int) ($item['sks_praktek'] ?? 0),
                    'sks_praktek_lapangan' => (int) ($item['sks_praktek_lapangan'] ?? 0),
                    'sks_simulasi' => (int) ($item['sks_simulasi'] ?? 0),
                    'program_studi_id' => $prodiId,
                    'id_prodi' => $item['id_prodi'] ?? null,
                ]
            );
        }, 'nama_mata_kuliah');
    }

    /**
     * Sync Mahasiswa
     *
     * @return array{total: int, synced: int, errors: array}
     */
    public function syncMahasiswa(): array
    {
        // Build a map of id_prodi => local ProgramStudi id
        $prodiMap = ProgramStudi::pluck('id', 'id_prodi')->toArray();
        
        if (empty($prodiMap)) {
            throw new \Exception('Tidak ada Program Studi. Sync Program Studi terlebih dahulu.');
        }

        // Fetch all mahasiswa at once (no filter)
        $dosenMap = Dosen::pluck('id', 'id_dosen')->toArray();
        $response = $this->neoFeeder->getMahasiswa();


        return $this->processResponse($response, 'Mahasiswa', function ($item) use ($prodiMap) {
            $prodiId = $prodiMap[$item['id_prodi'] ?? ''] ?? null;
            
            Mahasiswa::updateOrCreate(
                ['id_mahasiswa' => $item['id_mahasiswa'] ?? $item['id_registrasi_mahasiswa'] ?? null],
                [
                    'nim' => $item['nim'] ?? '',
                    'nama' => $item['nama_mahasiswa'] ?? '',
                    'tempat_lahir' => $item['tempat_lahir'] ?? null,
                    'tanggal_lahir' => isset($item['tanggal_lahir']) ? \Carbon\Carbon::parse($item['tanggal_lahir']) : null,
                    'jenis_kelamin' => $item['jenis_kelamin'] ?? null,
                    'alamat' => $item['jalan'] ?? null,
                    'no_hp' => $item['handphone'] ?? null,
                    'email' => $item['email'] ?? null,
                    'nama_ayah' => $item['nama_ayah'] ?? null,
                    'nama_ibu' => $item['nama_ibu_kandung'] ?? null,
                    'program_studi_id' => $prodiId,
                    'id_prodi' => $item['id_prodi'] ?? null,
                    'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'] ?? null,
                    'dosen_wali_id' => $dosenMap[$item['id_dosen_wali'] ?? ''] ?? null,
                    // angkatan tidak ada di API, gunakan 4 digit awal id_periode (format: 20231 = 2023)
                    'angkatan' => $item['angkatan'] ?? (isset($item['id_periode']) ? substr($item['id_periode'], 0, 4) : null),
                    'status_mahasiswa' => $item['id_status_mahasiswa'] ?? 'A',
                    'pekerjaan_ayah' => $item['nama_pekerjaan_ayah'] ?? null,
                    'pekerjaan_ibu' => $item['nama_pekerjaan_ibu'] ?? null,
                    'alamat_ortu' => $item['alamat_ayah'] ?? $item['alamat_ibu'] ?? null,
                ]
            );
        }, 'nim');
    }

    /**
     * Sync Biodata Mahasiswa (Single)
     * Fetches detailed data including parent info
     * 
     * @return string|null Returns 'updated', 'skipped', or null on failure
     */
    public function syncBiodataMahasiswa(Mahasiswa $mahasiswa): ?string
    {
        if (!$mahasiswa->id_mahasiswa) {
            return null;
        }

        $response = $this->neoFeeder->getBiodataMahasiswa($mahasiswa->id_mahasiswa);
        
        if (empty($response['data'])) {
            return null;
        }

        $data = $response['data'][0];
        
        // Build update data
        $updateData = [
            'nama_ayah' => $data['nama_ayah'] ?? $mahasiswa->nama_ayah,
            'nama_ibu' => $data['nama_ibu_kandung'] ?? $mahasiswa->nama_ibu,
            'pekerjaan_ayah' => $data['nama_pekerjaan_ayah'] ?? $mahasiswa->pekerjaan_ayah,
            'pekerjaan_ibu' => $data['nama_pekerjaan_ibu'] ?? $mahasiswa->pekerjaan_ibu,
            'alamat_ortu' => $data['jalan'] ?? $mahasiswa->alamat_ortu,
            'nik' => $data['nik'] ?? $mahasiswa->nik,
            'nisn' => $data['nisn'] ?? $mahasiswa->nisn,
            'npwp' => $data['npwp'] ?? $mahasiswa->npwp,
            'kewarganegaraan' => $data['kewarganegaraan'] ?? $mahasiswa->kewarganegaraan,
            'alamat' => $data['jalan'] ?? $mahasiswa->alamat,
            'dusun' => $data['dusun'] ?? $mahasiswa->dusun,
            'rt' => $data['rt'] ?? $mahasiswa->rt,
            'rw' => $data['rw'] ?? $mahasiswa->rw,
            'kelurahan' => $data['kelurahan'] ?? $mahasiswa->kelurahan,
            'kode_pos' => $data['kode_pos'] ?? $mahasiswa->kode_pos,
            'telepon' => $data['telepon'] ?? $mahasiswa->telepon,
            'no_hp' => $data['handphone'] ?? $mahasiswa->no_hp,
            'email' => $data['email'] ?? $mahasiswa->email,
        ];
        
        // Check if any data changed
        $hasChanges = false;
        foreach ($updateData as $key => $value) {
            if ($mahasiswa->$key != $value) {
                $hasChanges = true;
                break;
            }
        }
        
        if ($hasChanges) {
            $mahasiswa->update($updateData);
        }

        // EXTRA: Sync Status from GetListMahasiswa (since Biodata doesn't have it)
        try {
            $statusResponse = $this->neoFeeder->request('GetListMahasiswa', [
                'filter' => "id_mahasiswa = '{$mahasiswa->id_mahasiswa}'"
            ]);

            if ($statusResponse && !empty($statusResponse['data'])) {
                $statusData = $statusResponse['data'][0];
                $newStatus = $statusData['id_status_mahasiswa'] ?? $mahasiswa->status_mahasiswa;
                if ($mahasiswa->status_mahasiswa != $newStatus) {
                    $mahasiswa->update(['status_mahasiswa' => $newStatus]);
                    $hasChanges = true;
                }
            }
        } catch (\Exception $e) {
            \Log::warning("Failed to sync status for {$mahasiswa->nim}: " . $e->getMessage());
        }

        return $hasChanges ? 'updated' : 'skipped';
    }

    /**
     * Sync Dosen
     *
     * @return array{total: int, synced: int, errors: array}
     */
    public function syncDosen(): array
    {
        // Build a map of id_prodi => local ProgramStudi id
        $prodiMap = ProgramStudi::pluck('id', 'id_prodi')->toArray();

        // Fetch all dosen at once (no filter)
        $response = $this->neoFeeder->getDosen();
        
        return $this->processResponse($response, 'Dosen', function ($item) use ($prodiMap) {
            $prodiId = $prodiMap[$item['id_prodi'] ?? ''] ?? null;
            
            Dosen::updateOrCreate(
                ['id_dosen' => $item['id_dosen']],
                [
                    'nidn' => $item['nidn'] ?? null,
                    'nip' => $item['nip'] ?? null,
                    'nama' => $item['nama_dosen'] ?? '',
                    'gelar_depan' => $item['gelar_depan'] ?? null,
                    'gelar_belakang' => $item['gelar_belakang'] ?? null,
                    'jenis_kelamin' => $item['jenis_kelamin'] ?? null,
                    'tempat_lahir' => $item['tempat_lahir'] ?? null,
                    'tanggal_lahir' => isset($item['tanggal_lahir']) ? \Carbon\Carbon::parse($item['tanggal_lahir']) : null,
                    'jabatan_fungsional' => $item['nama_jabatan_fungsional'] ?? null,
                    'id_status_aktif' => $item['id_status_aktif'] ?? null,
                    'status_aktif' => $item['nama_status_aktif'] ?? null,
                    'program_studi_id' => $prodiId,
                    'id_prodi' => $item['id_prodi'] ?? null,
                ]
            );
        }, 'nama_dosen');
    }

    /**
     * Sync Nilai (Grades) for all mahasiswa
     * Supports pagination with offset (50 per batch - small to prevent timeout)
     *
     * @return array{total: int, synced: int, errors: array, total_all: int, has_more: bool, next_offset: int|null, progress: int}
     */
    public function syncNilai(int $offset = 0, int $limit = 50): array
    {
        // Get total count
        $totalAll = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->count();
        
        if ($totalAll === 0) {
            throw new \Exception('Tidak ada Mahasiswa dengan id_registrasi. Sync Mahasiswa terlebih dahulu.');
        }

        $mahasiswaList = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')
            ->orderBy('id')
            ->skip($offset)
            ->take($limit)
            ->get();

        // Build maps for lookups
        $matkulMap = MataKuliah::pluck('id', 'id_matkul')->toArray();
        $semesterMap = TahunAkademik::pluck('id', 'id_semester')->toArray();

        $totalTotal = 0;
        $totalSynced = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $allErrors = [];

        foreach ($mahasiswaList as $index => $mhs) {
            
            $response = $this->neoFeeder->getRiwayatNilaiMahasiswa($mhs->id_registrasi_mahasiswa);
            
            if (!$response || !isset($response['data'])) {
                // If no data response, count nothing as processed
                unset($response);
                continue;
            }

            foreach ($response['data'] as $item) {
                $totalTotal++;
                try {
                    $matkulId = $matkulMap[$item['id_matkul'] ?? ''] ?? null;
                    $semesterId = $semesterMap[$item['id_semester'] ?? ''] ?? null;

                    if (!$matkulId || !$semesterId) {
                        $skipped++; // Cannot process without relation
                        continue;
                    }

                    $nilai = Nilai::updateOrCreate(
                        [
                            'mahasiswa_id' => $mhs->id,
                            'mata_kuliah_id' => $matkulId,
                            'tahun_akademik_id' => $semesterId,
                        ],
                        [
                            'id_semester' => $item['id_semester'] ?? null,
                            'id_kelas_kuliah' => $item['id_kelas_kuliah'] ?? null,
                            'nilai_angka' => $item['nilai_angka'] ?? null,
                            'nilai_huruf' => $item['nilai_huruf'] ?? null,
                            'nilai_indeks' => $item['nilai_indeks'] ?? null,
                        ]
                    );

                    if ($nilai->wasRecentlyCreated) {
                        $inserted++;
                    } elseif ($nilai->wasChanged()) {
                        $updated++;
                    } else {
                        $skipped++;
                    }
                    
                    $totalSynced++;
                } catch (\Exception $e) {
                    $allErrors[] = "Nilai {$mhs->nim}: " . $e->getMessage();
                }
            }
            
            unset($response);
            
            if ($index % 10 === 0) {
                gc_collect_cycles();
            }
        }
        
        unset($mahasiswaList);
        gc_collect_cycles();

        $nextOffset = $offset + $limit;
        $hasMore = $nextOffset < $totalAll;
        $progress = min(100, round(($offset + $limit) / $totalAll * 100));

        return [
            'total' => $totalTotal,
            'synced' => $totalSynced,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $allErrors,
            'total_all' => $totalAll,
            'batch_count' => $limit,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }

    /**
     * Sync Nilai by Semester (BULK - RECOMMENDED for faster sync)
     * Fetches all nilai records for a specific semester in one API call
     * Much faster than per-student approach
     *
     * @param string $semesterId The semester ID to sync (e.g., "20241")
     * @param int $offset API offset for pagination
     * @param int $limit Records per API call
     * @return array
     */
    public function syncNilaiBySemester(string $semesterId, int $offset = 0, int $limit = 2000): array
    {
        // Build maps for lookups
        $matkulMap = MataKuliah::pluck('id', 'id_matkul')->toArray();
        $semesterMap = TahunAkademik::pluck('id', 'id_semester')->toArray();
        $mahasiswaMap = Mahasiswa::pluck('id', 'id_registrasi_mahasiswa')->toArray();
        
        if (empty($matkulMap)) {
            throw new \Exception('Tidak ada Mata Kuliah. Sync Mata Kuliah terlebih dahulu.');
        }
        
        if (empty($mahasiswaMap)) {
            throw new \Exception('Tidak ada Mahasiswa. Sync Mahasiswa terlebih dahulu.');
        }

        $response = $this->neoFeeder->getNilaiBySemester($semesterId, $limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API (timeout/connection error)');
        }
        
        if (isset($response['error_code']) && $response['error_code'] != 0) {
            throw new \Exception($response['error_desc'] ?? 'Error dari Neo Feeder');
        }

        $data = $response['data'] ?? [];
        $totalFromApi = count($data);
        $totalSynced = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $allErrors = [];

        foreach ($data as $index => $item) {
            try {
                $mahasiswaId = $mahasiswaMap[$item['id_registrasi_mahasiswa'] ?? ''] ?? null;
                $matkulId = $matkulMap[$item['id_matkul'] ?? ''] ?? null;
                $tahunAkademikId = $semesterMap[$item['id_semester'] ?? ''] ?? null;

                if (!$mahasiswaId || !$matkulId || !$tahunAkademikId) {
                    $skipped++;
                    continue;
                }

                $nilai = Nilai::updateOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswaId,
                        'mata_kuliah_id' => $matkulId,
                        'tahun_akademik_id' => $tahunAkademikId,
                    ],
                    [
                        'id_semester' => $item['id_semester'] ?? null,
                        'id_kelas_kuliah' => $item['id_kelas_kuliah'] ?? null,
                        'nilai_angka' => $item['nilai_angka'] ?? null,
                        'nilai_huruf' => $item['nilai_huruf'] ?? null,
                        'nilai_indeks' => $item['nilai_indeks'] ?? null,
                    ]
                );
                
                if ($nilai->wasRecentlyCreated) {
                    $inserted++;
                } elseif ($nilai->wasChanged()) {
                    $updated++;
                } else {
                    $skipped++;
                }
                
                $totalSynced++;
            } catch (\Exception $e) {
                $nim = $item['nim'] ?? 'Unknown';
                $allErrors[] = "Nilai {$nim}: " . $e->getMessage();
            }
            
            // Garbage collection every 500 records
            if ($index % 500 === 0 && $index > 0) {
                gc_collect_cycles();
            }
        }
        
        gc_collect_cycles();

        $hasMore = $totalFromApi === $limit; // If we got exactly limit records, there might be more
        $nextOffset = $hasMore ? $offset + $limit : null;

        return [
            'semester_id' => $semesterId,
            'total' => $totalFromApi,
            'synced' => $totalSynced,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $allErrors,
            'offset' => $offset,
            'next_offset' => $nextOffset,
            'has_more' => $hasMore,
        ];
    }

    /**
     * Sync Aktivitas Kuliah (IPK, SKS) for all mahasiswa
     * Supports pagination with offset (50 per batch - small to prevent timeout)
     *
     * @return array{total: int, synced: int, errors: array, total_all: int, has_more: bool, next_offset: int|null, progress: int}
     */
    /**
     * Sync Aktivitas Kuliah (IPK, SKS) for all mahasiswa
     * Supports pagination with offset
     * 
     * @return array
     */
    public function syncAktivitasKuliah(int $offset = 0, int $limit = 50): array
    {
        // Get total count
        $totalAll = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->count();
        
        if ($totalAll === 0) {
            throw new \Exception('Tidak ada Mahasiswa dengan id_registrasi. Sync Mahasiswa terlebih dahulu.');
        }

        $mahasiswaList = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')
            ->orderBy('id')
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalTotal = 0;
        $totalSynced = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $allErrors = [];

        foreach ($mahasiswaList as $index => $mhs) {
            /** @var Mahasiswa $mhs */
            $totalTotal++;
            
            // Fetch activity history for this student
            $response = $this->neoFeeder->getAktivitasKuliahMahasiswa($mhs->id_registrasi_mahasiswa);
            
            if (!$response || !isset($response['data']) || empty($response['data'])) {
                $skipped++;
                continue;
            }

            try {
                $data = $response['data'];
                
                // Sort by semester descending to get the latest status
                usort($data, function($a, $b) {
                    return $b['id_semester'] <=> $a['id_semester'];
                });
                
                // Get latest activity
                $latest = $data[0];
                
                $updateData = [
                    'status_mahasiswa' => $latest['id_status_mahasiswa'] ?? $latest['status_mahasiswa'] ?? null,
                ];

                if (isset($latest['ipk'])) $updateData['ipk'] = $latest['ipk'];
                
                // Check sks_total or sks_tot or total_sks
                $sks = $latest['sks_total'] ?? $latest['sks_tot'] ?? $latest['total_sks'] ?? null;
                if ($sks !== null) $updateData['sks_tempuh'] = $sks;

                // Update Mahasiswa
                $mhs->fill($updateData);
                if ($mhs->isDirty()) {
                     $mhs->save();
                     $updated++;
                } else {
                     $skipped++;
                }
                
                $totalSynced++;
            } catch (\Exception $e) {
                $allErrors[] = "Aktivitas {$mhs->nim}: " . $e->getMessage();
            }
            
            // Garbage collection
            if ($index % 10 === 0) {
                gc_collect_cycles();
            }

            // Abort if too many errors (likely API down)
            if (count($allErrors) >= 5 && $totalSynced === 0) {
                 $allErrors[] = "Abort: Too many consecutive errors. Checking connection...";
                 break;
            }
        }
        
        gc_collect_cycles();

        $nextOffset = $offset + $limit;
        $hasMore = $nextOffset < $totalAll;
        $progress = min(100, round(($offset + $limit) / $totalAll * 100));

        return [
            'total' => $totalTotal,
            'synced' => $totalSynced,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $allErrors,
            'total_all' => $totalAll,
            'batch_count' => $limit,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }

    /**
     * Helper to process standard Feeder responses
     * 
     * Callback should return:
     * - 'inserted' for new records
     * - 'updated' for changed records
     * - 'skipped' for unchanged records
     * - null/void for legacy behavior (counts as synced)
     */
    protected function processResponse(?array $response, string $context, callable $callback, string $identifierKey): array
    {
        if (!$response) {
            throw new \Exception("Gagal menghubungi Neo Feeder ($context). Periksa kredensial.");
        }

        if (isset($response['error_code']) && $response['error_code'] != 0) {
            throw new \Exception($response['error_desc'] ?? "Error dari Neo Feeder ($context)");
        }

        $data = $response['data'] ?? [];
        
        // Handle case where data might be null/empty
        if (!is_array($data)) {
            $data = [];
        }

        $total = count($data);
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                $result = $callback($item);
                
                // Track detailed stats based on callback return
                if ($result === 'inserted') {
                    $inserted++;
                    $synced++;
                } elseif ($result === 'updated') {
                    $updated++;
                    $synced++;
                } elseif ($result === 'skipped') {
                    $skipped++;
                } else {
                    // Legacy behavior: count as synced
                    $synced++;
                }
            } catch (\Exception $e) {
                $id = $item[$identifierKey] ?? 'Unknown';
                $errors[] = "$context $id: " . $e->getMessage();
            }
        }

        return [
            'total' => $total,
            'synced' => $synced,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }
    /**
     * Sync KRS Mahasiswa (Per Student)
     * Fetches ListPerkuliahanMahasiswa which contains classes taken
     */
    public function syncKrsMahasiswa(string $idRegistrasi): array
    {
        $response = $this->neoFeeder->getKrsMahasiswa("id_registrasi_mahasiswa = '{$idRegistrasi}'");
        
        if (!$response || !isset($response['data']) || !is_array($response['data'])) {
             return ['synced' => 0, 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0, 'errors' => []];
        }
        
        $data = $response['data'];
        $synced = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        
        // Group by Semester
        $grouped = [];
        foreach ($data as $item) {
            // Mapping: id_semester column is usually 'id_periode' in GetKRSMahasiswa
            $sem = $item['id_periode'] ?? $item['id_semester'] ?? null;
            if ($sem) {
                $grouped[$sem][] = $item;
            }
        }
        
        $mahasiswa = Mahasiswa::where('id_registrasi_mahasiswa', $idRegistrasi)->first();
        if (!$mahasiswa) {
             return ['synced' => 0, 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'total' => 0, 'errors' => ["Mahasiswa not found locally"]];
        }

        foreach ($grouped as $idSemester => $items) {
             try {
                $ta = TahunAkademik::where('id_semester', $idSemester)->first();
                if (!$ta) continue;

                $krs = Krs::firstOrCreate(
                    [
                        'mahasiswa_id' => $mahasiswa->id,
                        'tahun_akademik_id' => $ta->id,
                        'id_semester' => $idSemester,
                    ],
                    [
                        'id_registrasi_mahasiswa' => $idRegistrasi,
                        'is_approved' => true,
                    ]
                );
                
                if ($krs->wasRecentlyCreated) {
                    $inserted++;
                } else {
                    $updated++;
                }
                
                // Reset details for this semester to handle dropped classes
                $krs->details()->delete();
                
                foreach ($items as $detail) {
                     // Mapping Fields
                     $idMatkul = $detail['id_matkul'] ?? null;
                     $kodeMk = $detail['kode_mata_kuliah'] ?? $detail['kode_mk'] ?? null;
                     $idKelas = $detail['id_kelas'] ?? $detail['id_kelas_kuliah'] ?? null;
                     $namaKelas = $detail['nama_kelas_kuliah'] ?? 'A';

                     // Find Mata Kuliah
                     $mkQuery = MataKuliah::query();
                     if (!empty($idMatkul)) {
                         $mkQuery->where('id_matkul', $idMatkul);
                     } else {
                         $mkQuery->where('kode_matkul', $kodeMk);
                     }
                     $mk = $mkQuery->first();
                     
                     if (!$mk) {
                         // Fallback: try code if id failed
                         if (!empty($kodeMk)) {
                             $mk = MataKuliah::where('kode_matkul', $kodeMk)->first();
                         }
                     }
                     
                     if ($mk) {
                        $krsDetail = KrsDetail::create([
                             'krs_id' => $krs->id,
                             'mata_kuliah_id' => $mk->id,
                             'id_kelas_kuliah' => $idKelas,
                             'nama_kelas' => $namaKelas,
                        ]);

                        // Fetch Dosen Pengajar if Kelas is set
                        if ($idKelas && $krsDetail) {
                            try {
                                $dosenResponse = $this->neoFeeder->getDosenPengajarKelasKuliah($idKelas);
                                if ($dosenResponse && !empty($dosenResponse['data'])) {
                                    // Usually ambil dosen pertama (urutan 1)
                                    $ajar = $dosenResponse['data'][0];
                                    $idDosen = $ajar['id_dosen'] ?? null;
                                    
                                    if ($idDosen) {
                                        // Cari local dosen
                                        $localDosen = Dosen::where('id_dosen', $idDosen)->first();
                                        if ($localDosen) {
                                            $krsDetail->update([
                                                'dosen_id' => $localDosen->id,
                                                'nama_dosen' => $localDosen->nama_lengkap // Use accessor if avail or nama
                                            ]);
                                        } else {
                                            // Fallback just name
                                            $krsDetail->update([
                                                'nama_dosen' => $ajar['nama_dosen'] ?? null
                                            ]);
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                // Ignore error fetching dosen, continue
                            }
                        }
                     }
                }
                
                $synced++;
             } catch (\Exception $e) {
                 $errors[] = "Sem $idSemester: " . $e->getMessage();
             }
        }
        
        return [
            'total' => count($grouped),
            'synced' => $synced,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Sync KRS By Semester (Bulk)
     */
    public function syncKrsSemester(string $idSemester, int $limit = 1000, int $offset = 0): array
    {
        $response = $this->neoFeeder->getKrsBySemester($idSemester, $limit, $offset);
        
        if (!$response || empty($response['data'])) {
             return ['synced' => 0, 'total' => 0, 'has_more' => false, 'next_offset' => $offset];
        }

        $items = $response['data'];
        $count = count($items);
        
        // 1. Get all unique Student IDs
        $registrasiIds = collect($items)->pluck('id_registrasi_mahasiswa')->unique()->values()->all();
        
        // 2. Map to Local Mahasiswa IDs
        $mahasiswaMap = Mahasiswa::whereIn('id_registrasi_mahasiswa', $registrasiIds)
            ->pluck('id', 'id_registrasi_mahasiswa');
            
        // 3. Get Semester
        $ta = TahunAkademik::where('id_semester', $idSemester)->first();
        if (!$ta) {
             return ['synced' => 0, 'total' => 0, 'has_more' => false, 'next_offset' => $offset, 'error' => "Semester $idSemester not found"];
        }

        $synced = 0;
        $groupedByStudent = collect($items)->groupBy('id_registrasi_mahasiswa');
        
        foreach ($groupedByStudent as $regId => $studentItems) {
            $localMhsId = $mahasiswaMap[$regId] ?? null;
            if (!$localMhsId) continue; 
            
            $krs = Krs::firstOrCreate(
                [
                    'mahasiswa_id' => $localMhsId,
                    'tahun_akademik_id' => $ta->id,
                    'id_semester' => $idSemester,
                ],
                [
                    'id_registrasi_mahasiswa' => $regId,
                    'is_approved' => true,
                ]
            );
            
            foreach ($studentItems as $detail) {
                 $idMatkul = $detail['id_matkul'] ?? null;
                 $kodeMk = $detail['kode_mata_kuliah'] ?? $detail['kode_mk'] ?? null;
                 $idKelas = $detail['id_kelas'] ?? $detail['id_kelas_kuliah'] ?? null;
                 $namaKelas = $detail['nama_kelas_kuliah'] ?? 'A';

                 $mkQuery = MataKuliah::query();
                 if (!empty($idMatkul)) {
                     $mkQuery->where('id_matkul', $idMatkul);
                 } else {
                     $mkQuery->where('kode_matkul', $kodeMk);
                 }
                 $mk = $mkQuery->first();
                 
                 if (!$mk && !empty($kodeMk)) {
                     $mk = MataKuliah::where('kode_matkul', $kodeMk)->first();
                 }
                 
                 if ($mk) {
                    KrsDetail::updateOrCreate([
                         'krs_id' => $krs->id,
                         'mata_kuliah_id' => $mk->id,
                    ], [
                         'id_kelas_kuliah' => $idKelas,
                         'nama_kelas' => $namaKelas,
                    ]);
                 }
            }
            $synced++;
        }
        
        return [
            'synced' => $synced,
            'total_from_api' => $response['total'] ?? $count, 
            'has_more' => $count >= $limit,
            'next_offset' => $offset + $limit
        ];
    }

    /**
     * Sync KRS for all mahasiswa (Batch - 50 students)
     */
    public function syncKrs(int $offset = 0, int $limit = 50): array
    {
         // Get total count
        $totalAll = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')->count();
        
        if ($totalAll === 0) {
            throw new \Exception('Tidak ada Mahasiswa dengan id_registrasi. Sync Mahasiswa terlebih dahulu.');
        }
    
        $mahasiswaList = Mahasiswa::whereNotNull('id_registrasi_mahasiswa')
            ->orderBy('id')
            ->skip($offset)
            ->take($limit)
            ->get();
            
        $totalSynced = 0;
        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $allErrors = [];
        
        foreach ($mahasiswaList as $mhs) {
             // Removed usleep delay
             $result = $this->syncKrsMahasiswa($mhs->id_registrasi_mahasiswa);
             $totalSynced += $result['synced'];
             $inserted += $result['inserted'] ?? 0;
             $updated += $result['updated'] ?? 0;
             $skipped += $result['skipped'] ?? 0;
             
             if (!empty($result['errors'])) {
                 $allErrors[] = $mhs->nim . ': ' . implode(', ', $result['errors']);
             }
        }
        
        $nextOffset = $offset + $limit;
        $hasMore = $nextOffset < $totalAll;
        $progress = min(100, round(($offset + $limit) / $totalAll * 100));
        
        return [
             'total' => $limit, 
             'synced' => $totalSynced,
             'inserted' => $inserted,
             'updated' => $updated,
             'skipped' => $skipped,
             'errors' => $allErrors,
             'total_all' => $totalAll,
             'batch_count' => $mahasiswaList->count(),
             'offset' => $offset,
             'next_offset' => $hasMore ? $nextOffset : null,
             'has_more' => $hasMore,
             'progress' => $progress,
        ];
    }
}

