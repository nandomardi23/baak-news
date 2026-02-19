<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Sync\ReferenceSyncService;
use App\Services\Sync\CurriculumSyncService;
use App\Services\Sync\StudentSyncService;
use App\Services\Sync\LecturerSyncService;
use App\Services\Sync\AcademicSyncService;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    // Controller for handling Neo Feeder synchronization
    /**
     * Standardized Success Response
     */
    private function successResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Standardized Error Response
     */
    private function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    /**
     * Generic Sync Handler to reduce duplication
     */
    private function handleSync(Request $request, callable $callback, string $successMessage): JsonResponse
    {
        try {
            // Optimization: Prevent timeout and memory leaks during heavy sync
            set_time_limit(300); 
            \Illuminate\Support\Facades\DB::disableQueryLog();

            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 100); // Default limit 100 for stability
            $idSemester = $request->input('id_semester');
            $syncSince = $request->input('sync_since');

            // Execute the callback with processed parameters
            $result = $callback($offset, $limit, $idSemester, $syncSince);

            return $this->successResponse($successMessage, $result);
        } catch (\Exception $e) {
            Log::error("Sync Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Referensi (Agama, Wilayah, etc)
     */
    public function syncReferensi(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        try {
            $type = $request->input('type');
            $subType = $request->input('sub_type');
            
            // Handle Wilayah
            if ($type === 'wilayah') {
                if ($request->boolean('only_count')) {
                     return $this->successResponse('Count Wilayah', ['total' => $syncService->getCountWilayah()]);
                }
                return $this->handleSync($request, function($offset, $limit, $idSemester, $syncSince) use ($syncService) {
                    return $syncService->syncWilayah($offset, $limit, $syncSince);
                }, 'Sync Wilayah berhasil');
            }

            // Handle Sub-types (Agama, etc.) - simple count is just their total as they are small
            if ($subType) {
                 if ($request->boolean('only_count')) {
                    // For small tables, just return a dummy non-zero or quick count if available. 
                    // Or we can just let the sync flow happen as it's fast. 
                    // But to keep consistency, let's return 1 (unknown) or actual count if we implemented it.
                    // Since we didn't implement specialized getCount for Agama etc (they use request quick in sync), 
                    // we can skip or just return 0 to force sync to start. 
                    // Actually, let's just use the sync method itself to get count if we want, but they are single-page.
                    return $this->successResponse('Count Referensi', ['total' => 100]); // Dummy > 0 to start sync
                }

                $methodName = 'sync' . \Illuminate\Support\Str::studly($subType);
                if (method_exists($syncService, $methodName)) {
                    return $this->handleSync($request, function($offset, $limit, $idSemester, $syncSince) use ($syncService, $methodName) {
                        return $syncService->$methodName($syncSince);
                    }, "Sync Referensi $subType berhasil");
                }
                return $this->errorResponse("Sub-tipe referensi $subType tidak ditemukan", 400);
            }

            if ($request->boolean('only_count')) {
                // Referensi Umum is a composite sync. Calculating exact total is slow.
                // Return a dummy positive number to ensure frontend proceeds to sync.
                return $this->successResponse('Count Referensi', ['total' => 100]);
            }

            // Fallback: Sync all simple references
            $synced = 0;
            $simpleSyncs = ['Agama', 'JenisTinggal', 'AlatTransportasi', 'Pekerjaan', 'Penghasilan', 'KebutuhanKhusus', 'Pembiayaan'];
            
            foreach ($simpleSyncs as $sync) {
                $method = 'sync' . $sync;
                $res = $syncService->$method();
                $synced += $res['synced'] ?? 0;
            }

            return $this->successResponse('Sync Referensi berhasil', [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function syncProdi(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Prodi', ['total' => $syncService->getCountProdi()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncProdi($o, $l, $ss), 'Sync Prodi berhasil');
    }

    public function syncSemester(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Semester', ['total' => $syncService->getCountSemester()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncSemester($o, $l, $ss), 'Sync Semester berhasil');
    }

    public function syncKurikulum(Request $request, CurriculumSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Kurikulum', ['total' => $syncService->getCountKurikulum()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncKurikulum($o, $l, $ss), 'Sync Kurikulum berhasil');
    }

    public function syncMataKuliah(Request $request, CurriculumSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Mata Kuliah', ['total' => $syncService->getCountMataKuliah()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncMataKuliah($o, $l, $ss), 'Sync Mata Kuliah berhasil');
    }

    public function syncMahasiswa(Request $request, StudentSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Mahasiswa', ['total' => $syncService->getCountMahasiswa()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncMahasiswa($o, $l, $ss), 'Sync Mahasiswa berhasil');
    }

    public function syncMahasiswaDetail(Request $request, StudentSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Riwayat Pendidikan', ['total' => $syncService->getCountRiwayatPendidikan()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncRiwayatPendidikan($o, $l, $ss), 'Sync Riwayat Pendidikan berhasil');
    }

    public function syncMahasiswaLulusDO(Request $request, StudentSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Mahasiswa Lulus/DO', ['total' => $syncService->getCountMahasiswaLulusDO()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncMahasiswaLulusDO($o, $l, $ss), 'Sync Mahasiswa Lulus/DO berhasil');
    }

    public function syncBiodata(Request $request, StudentSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Biodata', ['total' => $syncService->getCountBiodata()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncBiodata($o, $l, $ss), 'Sync Biodata berhasil');
    }

    public function syncDosen(Request $request, LecturerSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Dosen', ['total' => $syncService->getCountDosen()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncDosen($o, $l, $ss), 'Sync Dosen berhasil');
    }

    public function syncAjarDosen(Request $request, LecturerSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Ajar Dosen', ['total' => $syncService->getCountAjarDosen($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncAjarDosen($o, $l, $ss), 'Sync Ajar Dosen berhasil');
    }

    public function syncKrs(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count KRS', ['total' => $syncService->getCountKrs($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, function($o, $l, $sem, $ss) use ($syncService) {
            return $sem ? $syncService->syncKrs($o, $l, $sem, $ss) : $syncService->syncKrsAllSemesters($o, $l, $ss);
        }, 'Sync KRS berhasil');
    }

    public function syncNilai(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count Nilai', ['total' => $syncService->getCountNilai($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, function($o, $l, $sem, $ss) use ($syncService) {
            return $sem ? $syncService->syncNilai($o, $l, $sem, $ss) : $syncService->syncNilaiAllSemesters($o, $l, $ss);
        }, 'Sync Nilai berhasil');
    }

    public function syncAktivitasKuliah(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count Aktivitas Kuliah', ['total' => $syncService->getCountAktivitas($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, function($o, $l, $sem, $ss) use ($syncService) {
            return $syncService->syncAktivitas($o, $l, $sem, $ss);
        }, 'Sync Aktivitas Kuliah berhasil');
    }
    
    // Alias for 'aktivitas' endpoint
    public function syncAktivitas(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        return $this->syncAktivitasKuliah($request, $syncService);
    }

    public function syncKelasKuliah(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Kelas Kuliah', ['total' => $syncService->getCountKelasKuliah()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncKelasKuliah($o, $l, $ss), 'Sync Kelas Kuliah berhasil');
    }

    public function syncDosenPengajar(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Dosen Pengajar', ['total' => $syncService->getCountDosenPengajar()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncDosenPengajar($o, $l, $ss), 'Sync Dosen Pengajar berhasil');
    }

    public function syncAktivitasMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count Aktivitas Mahasiswa', ['total' => $syncService->getCountAktivitasMahasiswa($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncAktivitasMahasiswa($o, $l, $ss), 'Sync Aktivitas Mahasiswa berhasil');
    }

    public function syncAnggotaAktivitasMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count Anggota Aktivitas', ['total' => $syncService->getCountAnggotaAktivitas($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncAnggotaAktivitasMahasiswa($o, $l, $ss), 'Sync Anggota Aktivitas Mahasiswa berhasil');
    }

    public function syncKonversiKampusMerdeka(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count Konversi', ['total' => $syncService->getCountKonversiKampusMerdeka($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncKonversiKampusMerdeka($o, $l, $ss), 'Sync Konversi Kampus Merdeka berhasil');
    }
    
    public function syncBimbinganMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Bimbingan', ['total' => $syncService->getCountBimbingan()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncBimbinganMahasiswa($o, $l, $ss), 'Sync Bimbingan Mahasiswa berhasil');
    }
    
    public function syncUjiMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Uji Mahasiswa', ['total' => $syncService->getCountUji()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncUjiMahasiswa($o, $l, $ss), 'Sync Uji Mahasiswa berhasil');
    }
}
