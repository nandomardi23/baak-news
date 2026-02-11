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
     * Sync Referensi (Agama, Wilayah, etc)
     */
    public function syncReferensi(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        try {
            $type = $request->input('type');
            $subType = $request->input('sub_type');
            
            if ($type === 'wilayah') {
                $offset = $request->input('offset', 0);
                $limit = $request->input('limit', 1000);
                $result = $syncService->syncWilayah($offset, $limit);
                return $this->successResponse('Sync Wilayah berhasil', $result);
            }

            if ($subType) {
                $methodName = 'sync' . str_replace('_', '', ucwords($subType, '_'));
                if (method_exists($syncService, $methodName)) {
                    $result = $syncService->$methodName();
                    return $this->successResponse("Sync $subType berhasil", $result);
                }
                return $this->errorResponse("Sub-tipe referensi $subType tidak ditemukan", 400);
            }

            // Fallback: Sync all simple references (may be slow)
            $synced = 0;
            $res = $syncService->syncAgama(); $synced += $res['synced'] ?? 0;
            $res = $syncService->syncJenisTinggal(); $synced += $res['synced'] ?? 0;
            $res = $syncService->syncAlatTransportasi(); $synced += $res['synced'] ?? 0;
            $res = $syncService->syncPekerjaan(); $synced += $res['synced'] ?? 0;
            $res = $syncService->syncPenghasilan(); $synced += $res['synced'] ?? 0;
            $res = $syncService->syncKebutuhanKhusus(); $synced += $res['synced'] ?? 0;
            $res = $syncService->syncPembiayaan(); $synced += $res['synced'] ?? 0;

            return $this->successResponse('Sync Referensi berhasil', ['synced' => $synced]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Program Studi
     */
    public function syncProdi(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 100);
            
            $result = $syncService->syncProdi($offset, $limit);
            
            return $this->successResponse('Sync Program Studi berhasil', $result);
        } catch (\Exception $e) {
            Log::error('Sync Prodi Error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Semester
     */
    public function syncSemester(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 100);
            
            $result = $syncService->syncSemester($offset, $limit);
            
            return $this->successResponse('Sync Semester berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Kurikulum
     */
    public function syncKurikulum(Request $request, CurriculumSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 100);
            
            $result = $syncService->syncKurikulum($offset, $limit);
            
            return $this->successResponse('Sync Kurikulum berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Mata Kuliah
     */
    public function syncMataKuliah(Request $request, CurriculumSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 2000);
            
            $result = $syncService->syncMataKuliah($offset, $limit);
            
            return $this->successResponse('Sync Mata Kuliah berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Mahasiswa
     */
    public function syncMahasiswa(Request $request, StudentSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 2000); 
            
            $result = $syncService->syncMahasiswa($offset, $limit);
            
            return $this->successResponse('Sync Mahasiswa berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Sync Mahasiswa Detail (Biodata) - Single Student
     */
    public function syncMahasiswaDetail(Request $request, StudentSyncService $syncService): JsonResponse
    {
        try {
            $id = $request->input('id');
            $mahasiswa = Mahasiswa::findOrFail($id);
            
            $status = $syncService->syncBiodata($mahasiswa);
            
            return $this->successResponse('Sync Biodata berhasil', ['status' => $status]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Biodata Mahasiswa (Batch)
     */
    public function syncBiodata(Request $request, StudentSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 50); 
            
            $students = Mahasiswa::skip($offset)->take($limit)->get();
            $totalStudents = Mahasiswa::count();
            
            $synced = 0;
            $skipped = 0;
            $errors = [];
            
            foreach ($students as $student) {
                try {
                    /** @var \App\Models\Mahasiswa $student */
                    $res = $syncService->syncBiodata($student);
                    if ($res === 'updated') $synced++;
                    else $skipped++;
                } catch (\Exception $e) {
                    $errors[] = "Biodata {$student->nim}: " . $e->getMessage();
                }
            }
            
            $nextOffset = $offset + $students->count();
            $hasMore = $nextOffset < $totalStudents;
            $progress = $totalStudents > 0 ? min(100, round($nextOffset / $totalStudents * 100)) : 100;

            return $this->successResponse('Sync Biodata Batch berhasil', [
                'synced' => $synced,
                'skipped' => $skipped,
                'errors' => $errors,
                'total' => $students->count(),
                'total_all' => $totalStudents,
                'has_more' => $hasMore,
                'next_offset' => $hasMore ? $nextOffset : null,
                'progress' => $progress
            ]);
            
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Dosen
     */
    public function syncDosen(Request $request, LecturerSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 500);
            
            $result = $syncService->syncDosen($offset, $limit);
            
            return $this->successResponse('Sync Dosen berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Nilai (Grades)
     */
    public function syncNilai(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 50);
            
            $result = $syncService->syncNilai($offset, $limit);
            
            return $this->successResponse('Sync Nilai berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync KRS
     */
    public function syncKrs(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 50);
            
            $result = $syncService->syncKrs($offset, $limit);
            
            return $this->successResponse('Sync KRS berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Aktivitas Kuliah (AKM)
     */
    public function syncAktivitasKuliah(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 50);
            
            $result = $syncService->syncAktivitas($offset, $limit);
            
            return $this->successResponse('Sync Aktivitas Kuliah berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Kelas Kuliah
     */
    public function syncKelasKuliah(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 2000);
            
            $result = $syncService->syncKelasKuliah($offset, $limit);
            
            return $this->successResponse('Sync Kelas Kuliah berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Dosen Pengajar
     */
    public function syncDosenPengajar(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 100);
            
            $result = $syncService->syncDosenPengajar($offset, $limit);
            
            return $this->successResponse('Sync Dosen Pengajar berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Sync Ajar Dosen (Real Teaching Activity)
     */
    public function syncAjarDosen(Request $request, LecturerSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 500);

            $result = $syncService->syncAjarDosen($offset, $limit);

            return $this->successResponse('Sync Ajar Dosen berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Bimbingan Mahasiswa
     */
    public function syncBimbinganMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 500);

            $result = $syncService->syncBimbinganMahasiswa($offset, $limit);

            return $this->successResponse('Sync Bimbingan Mahasiswa berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Uji Mahasiswa
     */
    public function syncUjiMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 500);

            $result = $syncService->syncUjiMahasiswa($offset, $limit);

            return $this->successResponse('Sync Uji Mahasiswa berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Aktivitas Mahasiswa (Non-Class)
     */
    public function syncAktivitasMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 500);

            $result = $syncService->syncAktivitasMahasiswa($offset, $limit);

            return $this->successResponse('Sync Aktivitas Mahasiswa berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Anggota Aktivitas Mahasiswa
     */
    public function syncAnggotaAktivitasMahasiswa(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 500);

            $result = $syncService->syncAnggotaAktivitasMahasiswa($offset, $limit);

            return $this->successResponse('Sync Anggota Aktivitas Mahasiswa berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Konversi Kampus Merdeka
     */
    public function syncKonversiKampusMerdeka(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        try {
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 500);

            $result = $syncService->syncKonversiKampusMerdeka($offset, $limit);

            return $this->successResponse('Sync Konversi Kampus Merdeka berhasil', $result);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
