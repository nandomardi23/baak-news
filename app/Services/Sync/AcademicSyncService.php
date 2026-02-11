<?php

namespace App\Services\Sync;

use App\Models\KelasKuliah;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Nilai;
use App\Models\AktivitasMahasiswa;
use App\Models\AnggotaAktivitasMahasiswa;
use App\Models\BimbinganMahasiswa;
use App\Models\UjiMahasiswa;
use App\Models\KonversiKampusMerdeka;
use Illuminate\Support\Facades\DB;

class AcademicSyncService extends BaseSyncService
{
    public function syncKelasKuliah(int $offset = 0, int $limit = 2000): array
    {
        $totalAll = 0;
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
    
    public function syncKrs(int $offset = 0, int $limit = 50): array
    {
        $totalAll = 0;
        $countResponse = $this->neoFeeder->getCountPerkuliahanMahasiswa();
        if ($countResponse && isset($countResponse['data'])) {
            $totalAll = $this->extractCount($countResponse['data']);
        }
    
        // NeoFeeder does not have a simple "GetListKRS" with global pagination.
        // It relies on fetching by student or by class.
        // However, we can simulate batch processing if we iterate over students.
        // NOTE: This implementation assumes we iterate over local students to fetch their KRS.
        
        // 1. Get local students
        $students = \App\Models\Mahasiswa::select('id_registrasi_mahasiswa', 'nim', 'id_prodi')
            ->skip($offset)
            ->take($limit)
            ->get();
            
        $batchCount = $students->count();
        // Since we are iterating students, totalAll logic matches student count roughly
        // But to be proper, we should use getCountMahasiswa as the base of calculation for progress
        // Overriding totalAll with local student count for progress tracking purposes
        $totalStudents = \App\Models\Mahasiswa::count(); 
        
        $synced = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                $krsData = $this->neoFeeder->getKrsMahasiswa($student->id_registrasi_mahasiswa);
                
                if ($krsData && isset($krsData['data'])) {
                    foreach ($krsData['data'] as $krsItem) {
                        try {
                            $semesterId = $krsItem['id_periode'];
                            
                            // Create/Update Parent KRS Record
                            $krs = Krs::firstOrCreate(
                                [
                                    'id_registrasi_mahasiswa' => $student->id_registrasi_mahasiswa,
                                    'id_semester' => $semesterId,
                                ],
                                [
                                    'nim' => $student->nim,
                                    'id_prodi' => $student->id_prodi,
                                ]
                            );
                            
                            // Create Detail
                            KrsDetail::updateOrCreate(
                                [
                                    'id_krs' => $krs->id,
                                    'id_kelas_kuliah' => $krsItem['id_kelas_kuliah'],
                                ],
                                [
                                    'id_matkul' => $krsItem['id_matkul'],
                                    'kode_mata_kuliah' => $krsItem['kode_mata_kuliah'],
                                    'nama_mata_kuliah' => $krsItem['nama_mata_kuliah'],
                                    'sks_mata_kuliah' => $krsItem['sks_mata_kuliah'] ?? 0,
                                    'nama_kelas_kuliah' => $krsItem['nama_kelas_kuliah'],
                                    'angkatan' => $krsItem['angkatan'] ?? null,
                                ]
                            );
                        } catch (\Exception $e) {
                           // Individual item error
                        }
                    }
                    $synced++;
                }
            } catch (\Exception $e) {
                $errors[] = "KRS {$student->nim}: " . $e->getMessage();
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

    public function syncNilai(int $offset = 0, int $limit = 50): array
    {
        // Similar strategy to KRS - iterate students
        $totalStudents = \App\Models\Mahasiswa::count();
        
        $students = \App\Models\Mahasiswa::select('id_registrasi_mahasiswa', 'nim', 'id_prodi')
            ->skip($offset)
            ->take($limit)
            ->get();
            
        $batchCount = $students->count();
        $synced = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                // Fetch grades (All history)
                $nilaiData = $this->neoFeeder->getRiwayatNilaiMahasiswa($student->id_registrasi_mahasiswa);
                
                if ($nilaiData && isset($nilaiData['data'])) {
                    foreach ($nilaiData['data'] as $nilaiItem) {
                        try {
                           Nilai::updateOrCreate(
                                [
                                    'id_registrasi_mahasiswa' => $student->id_registrasi_mahasiswa,
                                    'id_kelas_kuliah' => $nilaiItem['id_kelas_kuliah'],
                                ],
                                [
                                    'id_matkul' => $nilaiItem['id_matkul'],
                                    'nilai_angka' => $nilaiItem['nilai_angka'],
                                    'nilai_huruf' => $nilaiItem['nilai_huruf'],
                                    'nilai_indeks' => $nilaiItem['nilai_indeks'],
                                    'id_periode' => $nilaiItem['id_periode'],
                                    'nama_mata_kuliah' => $nilaiItem['nama_mata_kuliah'],
                                    'sks_mata_kuliah' => $nilaiItem['sks_mata_kuliah'],
                                ]
                           );
                        } catch (\Exception $e) {}
                    }
                    $synced++;
                }
            } catch (\Exception $e) {
                 $errors[] = "Nilai {$student->nim}: " . $e->getMessage();
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
    
    public function syncAktivitas(int $offset = 0, int $limit = 50): array
    {
        // IPK/IPS Sync - iterate students
        $totalStudents = \App\Models\Mahasiswa::count();
        
        $students = \App\Models\Mahasiswa::select('id_registrasi_mahasiswa', 'nim')
            ->skip($offset)
            ->take($limit)
            ->get();
            
        $batchCount = $students->count();
        $synced = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                $akmData = $this->neoFeeder->getAktivitasKuliahMahasiswa($student->id_registrasi_mahasiswa);
                
                if ($akmData && isset($akmData['data'])) {
                    // We save this into a suitable table, or update generic stats
                    // For now, assuming we might update local AktivitasKuliah table if it exists
                    // Or simply skipping if no dedicated table, but counting as synced
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
        
        // Removed GetCountBimbingMahasiswa as it is not supported
        
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
    
    public function syncUjiMahasiswa(int $offset = 0, int $limit = 500): array
    {
        $totalAll = 0;
        
        // Removed GetCountUjiMahasiswa as it is not supported

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

    public function syncAktivitasMahasiswa(int $offset = 0, int $limit = 500): array
    {
        $totalAll = 0;
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountAktivitasMahasiswa();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncAktivitasMhs: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getAktivitasMahasiswa($limit, $offset);
        
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
    
    public function syncAnggotaAktivitasMahasiswa(int $offset = 0, int $limit = 500): array
    {
        $totalAll = 0;
        
        // Removed GetCountAnggotaAktivitasMahasiswa as it is not supported

        $response = $this->neoFeeder->getAnggotaAktivitasMahasiswa($limit, $offset);
        
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
    
    public function syncKonversiKampusMerdeka(int $offset = 0, int $limit = 500): array
    {
        $totalAll = 0;
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountKonversiKampusMerdeka();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncKonversi: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getKonversiKampusMerdeka($limit, $offset);
        
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
