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
    public function syncKelasKuliah(int $offset = 0, int $limit = 2000): array
    {
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountKelasKuliah();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncKelasKuliah: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getAllKelasKuliah($limit, $offset);
        
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

    public function syncDosenPengajar(int $offset = 0, int $limit = 100): array
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
    
    public function syncKrs(int $offset = 0, int $limit = 500, ?string $idSemester = null): array
    {
        if (!$idSemester) {
            // Sync semua semester (lebih lambat tapi complete)
            return $this->syncKrsAllSemesters($offset, $limit);
        }

        // 1. Get total count
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->request('GetCountKRSMahasiswa', ['filter' => "id_periode = '{$idSemester}'"]);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncKrs: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch bulk data
        $response = $this->neoFeeder->getKrsBySemester($idSemester, $limit, $offset);
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
            // Group by student to create parents first
            $studentsData = [];
            foreach ($data as $item) {
                $key = $item['id_registrasi_mahasiswa'] . '_' . $item['id_periode'];
                if (!isset($studentsData[$key])) {
                    $studentsData[$key] = [
                        'id_registrasi_mahasiswa' => $item['id_registrasi_mahasiswa'],
                        'id_semester' => $item['id_periode'],
                        'nim' => $item['nim'],
                        'id_prodi' => $item['id_prodi'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Upsert Parents
            Krs::upsert(array_values($studentsData), ['id_registrasi_mahasiswa', 'id_semester'], ['nim', 'id_prodi', 'updated_at']);

            // Get Parent IDs for Detail mapping
            $parentMap = Krs::whereIn('id_registrasi_mahasiswa', array_column($studentsData, 'id_registrasi_mahasiswa'))
                ->where('id_semester', $idSemester)
                ->get()
                ->pluck('id', 'id_registrasi_mahasiswa');

            $details = [];
            foreach ($data as $item) {
                $parentId = $parentMap[$item['id_registrasi_mahasiswa']] ?? null;
                if ($parentId) {
                    $details[] = [
                        'id_krs' => $parentId,
                        'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                        'id_matkul' => $item['id_matkul'],
                        'kode_mata_kuliah' => $item['kode_mata_kuliah'],
                        'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                        'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                        'nama_kelas_kuliah' => $item['nama_kelas_kuliah'],
                        'angkatan' => $item['angkatan'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Bulk Upsert Details
            $this->batchUpsert(KrsDetail::class, $details, ['id_krs', 'id_kelas_kuliah'], ['id_matkul', 'kode_mata_kuliah', 'nama_mata_kuliah', 'sks_mata_kuliah', 'nama_kelas_kuliah', 'angkatan', 'updated_at']);
            $synced = count($details);
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

    public function syncNilai(int $offset = 0, int $limit = 2000, ?string $idSemester = null): array
    {
        if (!$idSemester) {
            // Sync semua semester (lebih lambat tapi complete)
            return $this->syncNilaiAllSemesters($offset, $limit);
        }

        // 1. Get total count
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->request('GetCountNilaiPerkuliahanKelas', ['filter' => "id_semester = '{$idSemester}'"]);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncNilai: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch bulk data
        $response = $this->neoFeeder->getNilaiBySemester($idSemester, $limit, $offset);
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
                    'id_kelas_kuliah' => $item['id_kelas_kuliah'],
                    'id_matkul' => $item['id_matkul'],
                    'nilai_angka' => $item['nilai_angka'] ?? 0,
                    'nilai_huruf' => $item['nilai_huruf'] ?? '',
                    'nilai_indeks' => $item['nilai_indeks'] ?? 0,
                    'id_periode' => $item['id_semester'],
                    'nama_mata_kuliah' => $item['nama_mata_kuliah'],
                    'sks_mata_kuliah' => $item['sks_mata_kuliah'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $this->batchUpsert(Nilai::class, $records, ['id_registrasi_mahasiswa', 'id_kelas_kuliah'], [
                'nilai_angka', 'nilai_huruf', 'nilai_indeks', 'id_periode', 'nama_mata_kuliah', 'sks_mata_kuliah', 'updated_at'
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
    
    /**
     * Sync Aktivitas Kuliah (AKM) - Optimized for Bulk
     */
    public function syncAktivitas(int $offset = 0, int $limit = 1000, ?string $idSemester = null): array
    {
        if (!$idSemester) {
            // Fallback to student-by-student if no semester (slow, but keeps legacy compat)
            return $this->syncAktivitasLegacy($offset, $limit);
        }

        // 1. Get total count for this semester
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->request('GetCountAktivitasKuliahMahasiswa', ['filter' => "id_semester = '{$idSemester}'"]);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            Log::warning("SyncAktivitas: GetCount failed. Error: " . $e->getMessage());
        }

        // 2. Fetch bulk data
        $response = $this->neoFeeder->request('GetAktivitasKuliahMahasiswa', [
            'filter' => "id_semester = '{$idSemester}'",
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
            $this->batchUpsert(AktivitasKuliah::class, $records, ['id_registrasi_mahasiswa', 'id_semester'], [
                'nim', 'nama_mahasiswa', 'id_status_mahasiswa', 'ips', 'ipk', 'sks_semester', 'sks_total', 'biaya_kuliah_smt', 'updated_at'
            ]);

            // Update IPK, IPS, SKS di tabel mahasiswa (ambil yang terbaru per mahasiswa)
            $this->updateMahasiswaAkademik($data);
            
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
    
    public function syncBimbinganMahasiswa(int $offset = 0, int $limit = 500): array
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
        
        $response = $this->neoFeeder->getBimbingMahasiswa($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                BimbinganMahasiswa::updateOrCreate(
                    ['id_bimbingan_mahasiswa' => $item['id_bimbingan_mahasiswa']],
                    [
                        'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'] ?? '',
                        'id_dosen' => $item['id_dosen'] ?? '',
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
    
    public function syncUjiMahasiswa(int $offset = 0, int $limit = 500): array
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

        $response = $this->neoFeeder->getUjiMahasiswa($limit, $offset);
        
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
                    ['id_uji' => $item['id_uji']],
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

    public function syncAktivitasMahasiswa(int $offset = 0, int $limit = 500, ?string $idSemester = null): array
    {
        $totalAll = 0;
        $filter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        
        try {
            $countResponse = $this->neoFeeder->getCountAktivitasMahasiswa($filter);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncAktivitasMhs: GetCount failed. Error: " . $e->getMessage());
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
                AktivitasMahasiswa::updateOrCreate(
                    ['id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa']],
                    [
                        'id_jenis_aktivitas' => $item['id_jenis_aktivitas'],
                        'nama_jenis_aktivitas' => $item['nama_jenis_aktivitas'],
                        'id_prodi' => $item['id_prodi'],
                        'id_semester' => $item['id_semester'],
                        'judul_aktivitas_mahasiswa' => $item['judul_aktivitas_mahasiswa'],
                        'keterangan_aktivitas_mahasiswa' => $item['keterangan_aktivitas_mahasiswa'] ?? null,
                        'lokasi_kegiatan' => $item['lokasi_kegiatan'] ?? null,
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
    
    public function syncAnggotaAktivitasMahasiswa(int $offset = 0, int $limit = 500, ?string $idSemester = null): array
    {
        $totalAll = 0;
        $filter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        
        try {
            $countResponse = $this->neoFeeder->getCountAnggotaAktivitasMahasiswa($filter);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncAnggotaAktivitas: GetCount failed. Error: " . $e->getMessage());
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
                AnggotaAktivitasMahasiswa::updateOrCreate(
                    ['id_anggota' => $item['id_anggota']],
                    [
                        'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'],
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
    
    public function syncKonversiKampusMerdeka(int $offset = 0, int $limit = 500, ?string $idSemester = null): array
    {
        $totalAll = 0;
        $filter = $idSemester ? "id_semester = '{$idSemester}'" : "";
        
        try {
            $countResponse = $this->neoFeeder->getCountKonversiKampusMerdeka($filter);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncKonversi: GetCount failed. Error: " . $e->getMessage());
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
                        'id_aktivitas_mahasiswa' => $item['id_aktivitas_mahasiswa'],
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
    private function syncKrsAllSemesters(int $offset, int $limit): array
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

        $totalSynced = 0;
        $totalErrors = [];
        $totalCount = 0;

        // Hitung index semester berdasarkan offset 
        $semesterIndex = intdiv($offset, $limit);
        
        if ($semesterIndex >= $semesters->count()) {
            return [
                'total' => 0, 'synced' => 0, 'errors' => [],
                'total_all' => $semesters->count(), 'has_more' => false, 'progress' => 100,
            ];
        }

        $currentSemester = $semesters[$semesterIndex];
        $result = $this->syncKrs(0, $limit, $currentSemester);
        
        // Lanjut fetch halaman berikutnya untuk semester ini
        while ($result['has_more'] ?? false) {
            $nextResult = $this->syncKrs($result['next_offset'], $limit, $currentSemester);
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
        $progress = min(100, round(($nextSemesterIndex / $semesters->count()) * 100));

        return [
            'total' => $result['total'],
            'synced' => $result['synced'],
            'errors' => $result['errors'] ?? [],
            'total_all' => $semesters->count(),
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
    private function syncNilaiAllSemesters(int $offset, int $limit): array
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
                'total_all' => $semesters->count(), 'has_more' => false, 'progress' => 100,
            ];
        }

        $currentSemester = $semesters[$semesterIndex];
        $result = $this->syncNilai(0, $limit, $currentSemester);
        
        while ($result['has_more'] ?? false) {
            $nextResult = $this->syncNilai($result['next_offset'], $limit, $currentSemester);
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
        $progress = min(100, round(($nextSemesterIndex / $semesters->count()) * 100));

        return [
            'total' => $result['total'],
            'synced' => $result['synced'],
            'errors' => $result['errors'] ?? [],
            'total_all' => $semesters->count(),
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
}
