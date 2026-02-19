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

        foreach ($data as $item) {
            try {
                KelasKuliah::updateOrCreate(
                    ['id_kelas_kuliah' => $item['id_kelas_kuliah']],
                    [
                        'id_prodi' => $item['id_prodi'],
                        'id_semester' => $item['id_semester'],
                        'id_matkul' => $item['id_matkul'],
                        'nama_kelas_kuliah' => $item['nama_kelas_kuliah'],
                        'sks' => $item['sks'],
                        'bahasan' => $item['bahasan'] ?? null,
                        'tanggal_mulai_efektif' => $item['tanggal_mulai_efektif'] ?? null,
                        'tanggal_akhir_efektif' => $item['tanggal_akhir_efektif'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Kelas {$item['nama_kelas_kuliah']}: " . $e->getMessage();
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

    public function syncDosenPengajar(int $offset = 0, int $limit = 100, ?string $syncSince = null): array
    {
        // 1. Get total classes count
        $countResponse = $this->neoFeeder->getCountKelasKuliah();
        $totalClasses = 0;
        if ($countResponse && isset($countResponse['data'])) {
            $totalClasses = $this->extractCount($countResponse['data']);
        }

        // 2. Determine batch sizing for internal loop
        // We will loop through local classes to fetch lecturers
        $localClasses = KelasKuliah::skip($offset)->take($limit)->get();
        if ($localClasses->isEmpty()) {
            return [
                'total' => 0,
                'synced' => 0,
                'errors' => [],
                'total_all' => $totalClasses,
                'offset' => $offset,
                'next_offset' => null,
                'has_more' => false,
                'progress' => 100,
            ];
        }

        $synced = 0;
        $errors = [];
        $batchCount = $localClasses->count();

        /** @var \App\Models\KelasKuliah $kelas */
        foreach ($localClasses as $kelas) {
            try {
                $lecturers = $this->neoFeeder->getDosenPengajarKelasKuliah($kelas->id_kelas_kuliah);
                
                if ($lecturers && isset($lecturers['data'])) {
                    // Sync Pivot Table (dosen_pengajar_kelas)
                    // First, detach all existing lecturers for this class to handle removals
                    $kelas->dosenPengajar()->detach();

                    foreach ($lecturers['data'] as $lecturer) {
                        try {
                            $dosenId = $lecturer['id_dosen'];
                            
                            // Attach to pivot
                            $kelas->dosenPengajar()->attach($dosenId, [
                                'id_aktivitas_mengajar' => $lecturer['id_aktivitas_mengajar'] ?? null,
                                'id_registrasi_dosen' => $lecturer['id_registrasi_dosen'] ?? null,
                                'sks_substansi_total' => $lecturer['sks_substansi_total'] ?? 0,
                                'rencana_tatap_muka' => $lecturer['rencana_tatap_muka'] ?? 0,
                                'realisasi_tatap_muka' => $lecturer['realisasi_tatap_muka'] ?? 0,
                                'id_jenis_evaluasi' => $lecturer['id_jenis_evaluasi'] ?? null,
                            ]);

                            // Legacy update (optional, keeps old column filled for backward compat)
                            $kelas->update(['id_dosen' => $dosenId]); 

                        } catch (\Exception $e) {
                           // Ignore pivot duplicates or missing dosen
                        }
                    }
                    $synced++;
                }
            } catch (\Exception $e) {
                $errors[] = "Dosen Pengajar {$kelas->nama_kelas_kuliah}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $nextOffset < $totalClasses;
        $progress = $totalClasses > 0 ? min(100, round($nextOffset / $totalClasses * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalClasses,
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
            // Optimization: Pre-fetch mappings
            $idRegMahasiswas = collect($data)->pluck('id_registrasi_mahasiswa')->unique()->filter()->toArray();
            $idMatkuls = collect($data)->pluck('id_matkul')->unique()->filter()->toArray();
            
            // Map: id_registrasi_mahasiswa -> id
            // We need to fetch by id_registrasi_mahasiswa because that's what we have from API
            $mahasiswaMap = Mahasiswa::whereIn('id_registrasi_mahasiswa', $idRegMahasiswas)
                ->pluck('id', 'id_registrasi_mahasiswa'); // key=neo_id, value=local_id

            // Map: id_matkul -> id
            $matkulMap = \App\Models\MataKuliah::whereIn('id_matkul', $idMatkuls)
                ->pluck('id', 'id_matkul');

            // Map: id_semester -> id (Should be constant as we have $idSemester, but let's be safe)
            $semesterId = \App\Models\TahunAkademik::where('id_semester', $idSemester)->value('id');

            foreach ($data as $item) {
                try {
                    // Skip if local mahasiswa not found (optimization: don't even try if mapping missing)
                     $mahasiswaId = $mahasiswaMap[$item['id_registrasi_mahasiswa']] ?? null;
                     if (!$mahasiswaId) {
                         // Optional: You could log this, but it might spam. 
                         // Usually we only sync KRS for students we have.
                         continue; 
                     }

                    // 1. Handle Parent KRS (Header)
                    // Use updateOrCreate but with cached ID availability
                    // We can't easily upsert parent then child in one go because we need parent ID.
                    // But we can optimize by checking if we already processed this student in this loop? 
                    // No, updateOrCreate is fine for Header as there are few Headers per batch (1 per student).
                    $krs = Krs::updateOrCreate(
                        [
                            'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                            'id_semester' => $item['id_periode']
                        ],
                        [
                            'mahasiswa_id' => $mahasiswaId, // Direct ID set
                            'tahun_akademik_id' => $semesterId,
                            'nim' => $item['nim'],
                            'id_prodi' => $item['id_prodi'],
                            'updated_at' => now(),
                        ]
                    );

                    // 2. Handle Detail KRS
                    $matkulLocalId = $matkulMap[$item['id_matkul']] ?? null;
                    
                    KrsDetail::updateOrCreate(
                        [
                            'krs_id' => $krs->id, // Dependent on parent
                            'id_matkul' => $item['id_matkul']
                        ],
                        [
                            'id_kelas_kuliah' => $item['id_kelas_kuliah'] ?? null, // Handle missing class ID (e.g. non-class enrollment)
                            'mata_kuliah_id' => $matkulLocalId,
                            'kode_mata_kuliah' => $item['kode_mata_kuliah'],
                            'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                            'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                            'nama_kelas_kuliah' => $item['nama_kelas_kuliah'] ?? null,
                            'angkatan' => $item['angkatan'] ?? null,
                            'updated_at' => now(),
                        ]
                    );
                    
                    $synced++;
                } catch (\Exception $e) {
                    $errors[] = "KRS {$item['nim']} - {$item['kode_mata_kuliah']}: " . $e->getMessage();
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
                 if (!$mahasiswaId) continue;
                 
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
                              
                              if(!$mahasiswaId) continue;
                              
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

        foreach ($data as $item) {
            try {
                // Fix: Handle missing id_bimbingan_mahasiswa
                $idAktivitas = $item['id_aktivitas_mahasiswa'] ?? null;
                $idDosen = $item['id_dosen'] ?? null;

                if (!$idAktivitas || !$idDosen) {
                    continue; // Skip if essential keys are missing
                }

                $idBimbingan = $item['id_bimbingan_mahasiswa'] ?? md5($idAktivitas . $idDosen);

                BimbinganMahasiswa::updateOrCreate(
                    [
                        'id_aktivitas_mahasiswa' => $idAktivitas,
                        'id_dosen' => $idDosen
                    ],
                    [
                        'id_bimbingan_mahasiswa' => $idBimbingan,
                        'pembimbing_ke' => $item['pembimbing_ke'] ?? null,
                        'id_kategori_kegiatan' => $item['id_kategori_kegiatan'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Bimbingan Sync Error: " . $e->getMessage();
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

        foreach ($data as $item) {
            try {
                UjiMahasiswa::updateOrCreate(
                    ['id_uji_mahasiswa' => $item['id_uji_mahasiswa']],
                    [
                        'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'] ?? '',
                        'id_dosen' => $item['id_dosen'] ?? '',
                        'penguji_ke' => $item['penguji_ke'] ?? null,
                        'id_kategori_kegiatan' => $item['id_kategori_kegiatan'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Uji Sync Error: " . $e->getMessage();
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

        foreach ($data as $item) {
            try {
                // Fix: DB column is id_aktivitas, API might be id_aktivitas or id_aktivitas_mahasiswa
                $idAktivitas = $item['id_aktivitas'] ?? $item['id_aktivitas_mahasiswa'] ?? null;
                
                if (!$idAktivitas) {
                    continue; // Skip if ID is missing
                }

                AktivitasMahasiswa::updateOrCreate(
                    ['id_aktivitas' => $idAktivitas],
                    [
                        'id_jenis_aktivitas' => $item['id_jenis_aktivitas'] ?? null,
                        'nama_jenis_aktivitas' => $item['nama_jenis_aktivitas'] ?? null,
                        'id_prodi' => $item['id_prodi'] ?? null,
                        'id_semester' => $item['id_semester'] ?? null,
                        'judul_aktivitas_mahasiswa' => $item['judul_aktivitas_mahasiswa'] ?? $item['judul'] ?? null,
                        'keterangan_aktivitas_mahasiswa' => $item['keterangan_aktivitas_mahasiswa'] ?? $item['keterangan'] ?? null,
                        'lokasi_kegiatan' => $item['lokasi_kegiatan'] ?? $item['lokasi'] ?? null,
                        'sk_tugas' => $item['sk_tugas'] ?? null,
                        'tanggal_sk_tugas' => $item['tanggal_sk_tugas'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Aktivitas Mhs Error: " . $e->getMessage();
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

        foreach ($data as $item) {
            try {
                // Fix: DB column is id_aktivitas, handle missing keys
                $idAktivitas = $item['id_aktivitas'] ?? $item['id_aktivitas_mahasiswa'] ?? null;
                
                AnggotaAktivitasMahasiswa::updateOrCreate(
                    ['id_anggota' => $item['id_anggota']],
                    [
                        'id_aktivitas' => $idAktivitas, // Fixed column name
                        'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                        'nim' => $item['nim'],
                        'nama_mahasiswa' => $item['nama_mahasiswa'],
                        'id_peran_anggota' => $item['id_peran_anggota'],
                        'nama_peran_anggota' => $item['nama_peran_anggota'],
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Anggota Error: " . $e->getMessage();
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

        foreach ($data as $item) {
            try {
                KonversiKampusMerdeka::updateOrCreate(
                    ['id_konversi_aktivitas' => $item['id_konversi_aktivitas']],
                    [
                        'id_matkul' => $item['id_matkul'],
                        'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                        'sks_mata_kuliah' => $item['sks_mata_kuliah'],
                        'nilai_angka' => $item['nilai_angka'],
                        'nilai_huruf' => $item['nilai_huruf'],
                        'nilai_indeks' => $item['nilai_indeks'],
                        'id_semester' => $item['id_semester'],
                        'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'] ?? null, // Allow null
                        'judul_aktivitas_mahasiswa' => $item['judul_aktivitas_mahasiswa'] ?? null,
                        'id_anggota' => $item['id_anggota'] ?? null,
                        'nim' => $item['nim'] ?? null,
                        'nama_mahasiswa' => $item['nama_mahasiswa'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Konversi Error: " . $e->getMessage();
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
    /**
     * Sync KRS tanpa filter semester - ambil semua semester yang ada di DB
     */
    public function syncKrsAllSemesters(int $offset, int $limit, ?string $syncSince = null): array
    {
        // Ambil semua semester dari DB
        $semesters = \App\Models\TahunAkademik::orderBy('id_semester', 'desc')->pluck('id_semester');
        
        if ($semesters->isEmpty()) {
            return [
                'total' => 0, 'synced' => 0,
                'errors' => ['Sync Semester terlebih dahulu sebelum sync KRS'],
                'total_all' => 0, 'has_more' => false, 'progress' => 0,
            ];
        }

        $semesterIndex = intdiv($offset, $limit);
        
        if ($semesterIndex >= $semesters->count()) {
            return [
                'total' => 0, 'synced' => 0, 'errors' => [],
                'total_all' => $semesters->count() * $limit, 'has_more' => false, 'progress' => 100,
            ];
        }

        $currentSemester = $semesters[$semesterIndex];
        $result = $this->syncKrs(0, $limit, $currentSemester, $syncSince);
        
        // Lanjut fetch halaman berikutnya untuk semester ini
        // Loop internal untuk menghabiskan satu semester sekaligus
        // Note: Ini bisa timeout jika semester sangat besar. 
        // Idealnya kita return partial dan resume, tapi struktur "AllSemester" ini berasumsi 1 request = 1 semester.
        // Untuk kestabilan, jika data > limit, kita ambil sisanya di request yang sama (looping)
        // ATAU kita ubah struktur agar offset itu record-based.
        // TAPI karena user minta quick fix: kita pertahankan loop internal tapi hati-hati timeout.
        while ($result['has_more'] ?? false) {
            // Safety break untuk menghindari infinite loop / timeout
            if (($result['total'] ?? 0) > 5000) break; 

            $nextResult = $this->syncKrs($result['next_offset'], $limit, $currentSemester, $syncSince);
            $result['synced'] += $nextResult['synced'];
            $result['total'] += $nextResult['total'];
            $result['errors'] = array_merge($result['errors'] ?? [], $nextResult['errors'] ?? []);
            $result = array_merge($result, [
                'has_more' => $nextResult['has_more'],
                'next_offset' => $nextResult['next_offset'],
            ]);
        }

        $nextSemesterIndex = $semesterIndex + 1;
        $hasMore = $nextSemesterIndex < $semesters->count();
        // Calculate progress based on Scaled Total
        $totalAllScaled = $semesters->count() * $limit;
        $currentProgressScaled = $nextSemesterIndex * $limit;
        $progress = min(100, round(($currentProgressScaled / $totalAllScaled) * 100));

        return [
            'total' => $result['total'],
            'synced' => $result['synced'],
            'errors' => $result['errors'] ?? [],
            'total_all' => $totalAllScaled, // Scaled total to match offset mechanism
            'offset' => $offset,
            'next_offset' => $hasMore ? ($nextSemesterIndex * $limit) : null,
            'has_more' => $hasMore,
            'progress' => $progress,
            'message' => "Semester {$currentSemester} selesai ({$nextSemesterIndex}/{$semesters->count()})",
        ];
    }

    /**
     * Sync Nilai tanpa filter semester - ambil semua semester yang ada di DB
     */
    /**
     * Sync Nilai tanpa filter semester - ambil semua semester yang ada di DB
     */
    public function syncNilaiAllSemesters(int $offset, int $limit, ?string $syncSince = null): array
    {
        $semesters = \App\Models\TahunAkademik::orderBy('id_semester', 'desc')->pluck('id_semester');
        
        if ($semesters->isEmpty()) {
            return [
                'total' => 0, 'synced' => 0,
                'errors' => ['Sync Semester terlebih dahulu sebelum sync Nilai'],
                'total_all' => 0, 'has_more' => false, 'progress' => 0,
            ];
        }

        $semesterIndex = intdiv($offset, $limit);
        
        if ($semesterIndex >= $semesters->count()) {
            return [
                'total' => 0, 'synced' => 0, 'errors' => [],
                'total_all' => $semesters->count() * $limit, 'has_more' => false, 'progress' => 100,
            ];
        }

        $currentSemester = $semesters[$semesterIndex];
        $result = $this->syncNilai(0, $limit, $currentSemester, $syncSince);
        
        while ($result['has_more'] ?? false) {
             if (($result['total'] ?? 0) > 5000) break; // Safety break

            $nextResult = $this->syncNilai($result['next_offset'], $limit, $currentSemester, $syncSince);
            $result['synced'] += $nextResult['synced'];
            $result['total'] += $nextResult['total'];
            $result['errors'] = array_merge($result['errors'] ?? [], $nextResult['errors'] ?? []);
            $result = array_merge($result, [
                'has_more' => $nextResult['has_more'],
                'next_offset' => $nextResult['next_offset'],
            ]);
        }

        $nextSemesterIndex = $semesterIndex + 1;
        $hasMore = $nextSemesterIndex < $semesters->count();
        
        $totalAllScaled = $semesters->count() * $limit;
        $currentProgressScaled = $nextSemesterIndex * $limit;
        $progress = min(100, round(($currentProgressScaled / $totalAllScaled) * 100));

        return [
            'total' => $result['total'],
            'synced' => $result['synced'],
            'errors' => $result['errors'] ?? [],
            'total_all' => $totalAllScaled,
            'offset' => $offset,
            'next_offset' => $hasMore ? ($nextSemesterIndex * $limit) : null,
            'has_more' => $hasMore,
            'progress' => $progress,
            'message' => "Semester {$currentSemester} selesai ({$nextSemesterIndex}/{$semesters->count()})",
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
