<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NeoFeederSyncService;
use App\Services\ReferenceSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * Sync Program Studi (with pagination)
     */
    public function syncProdi(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(300);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncProdi($offset);
            return $this->successResponse('Program Studi', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['total'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Prodi Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Semester (with pagination)
     */
    public function syncSemester(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(300);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncSemester($offset);
            return $this->successResponse('Semester', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['total'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Semester Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Mata Kuliah (with pagination)
     */
    public function syncMataKuliah(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(600);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncMataKuliah($offset);
            return $this->successResponse('Mata Kuliah', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['total'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Mata Kuliah Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Mahasiswa (with pagination)
     */
    public function syncMahasiswa(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(600);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncMahasiswa($offset);
            return $this->successResponse('Mahasiswa', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['total'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Mahasiswa Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Dosen (with pagination)
     */
    public function syncDosen(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(300);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncDosen($offset);
            return $this->successResponse('Dosen', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['total'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Dosen Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Mahasiswa Detail (Single)
     */
    public function syncMahasiswaDetail(Request $request, NeoFeederSyncService $syncService): \Illuminate\Http\RedirectResponse
    {
        $id = $request->input('id');
        $mahasiswa = \App\Models\Mahasiswa::findOrFail($id);
        
        try {
            $success = $syncService->syncBiodataMahasiswa($mahasiswa);
            
            if ($success) {
                return back()->with('success', 'Detail mahasiswa berhasil disinkronisasi');
            } else {
                return back()->with('error', 'Gagal mengambil data dari Neo Feeder (Data Kosong)');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Biodata Mahasiswa (Bulk with Pagination)
     * GetBiodataMahasiswa: Update biodata lengkap untuk semua mahasiswa
     * Supports pagination with offset parameter (200 records per batch)
     */
    public function syncBiodata(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        // Increase execution time for bulk sync
        set_time_limit(600);
        
        $batchSize = 200;
        $offset = (int) $request->input('offset', 0);
        
        try {
            // Get total count for reference
            $totalAll = \App\Models\Mahasiswa::whereNotNull('id_mahasiswa')
                ->where('id_mahasiswa', '!=', '')
                ->count();
            
            // Get batch of mahasiswa
            $mahasiswaList = \App\Models\Mahasiswa::whereNotNull('id_mahasiswa')
                ->where('id_mahasiswa', '!=', '')
                ->orderBy('id')
                ->skip($offset)
                ->take($batchSize)
                ->get();
            
            $batchCount = $mahasiswaList->count();
            $updated = 0;
            $skipped = 0;
            $failed = 0;
            $errors = [];
            
            \Log::info("Starting biodata sync batch: offset={$offset}, batch_size={$batchCount}, total={$totalAll}");
            
            foreach ($mahasiswaList as $mahasiswa) {
                try {
                    $result = $syncService->syncBiodataMahasiswa($mahasiswa);
                    if ($result === 'updated') {
                        $updated++;
                    } elseif ($result === 'skipped') {
                        $skipped++;
                    } else {
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "{$mahasiswa->nim}: " . $e->getMessage();
                    \Log::error("Biodata sync error for {$mahasiswa->nim}: " . $e->getMessage());
                }
            }
            
            $nextOffset = $offset + $batchSize;
            $hasMore = $nextOffset < $totalAll;
            $progress = min(100, round(($offset + $batchCount) / $totalAll * 100));
            
            \Log::info("Biodata sync batch completed: {$updated} updated, {$skipped} skipped, {$failed} failed, progress={$progress}%");
            
            return response()->json([
                'success' => true,
                'message' => "Batch {$offset}-" . ($offset + $batchCount) . " dari {$totalAll}: {$updated} update, {$skipped} sama, {$failed} gagal",
                'data' => [
                    'total_from_api' => $totalAll,
                    'batch_size' => $batchCount,
                    'synced' => $updated + $skipped,
                    'inserted' => 0,  // Biodata never inserts, only updates existing
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'failed' => $failed,
                    'offset' => $offset,
                    'next_offset' => $hasMore ? $nextOffset : null,
                    'has_more' => $hasMore,
                    'progress' => $progress,
                    'errors' => array_slice($errors, 0, 5),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error("Biodata sync fatal error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync Nilai (with pagination - 1000 per batch)
     */
    public function syncNilai(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(1800);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncNilai($offset, 200);
            
            return $this->successResponse('Nilai', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['batch_count'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Nilai Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Nilai by Semester (BULK - FASTER)
     * Use this for initial sync - much faster than per-student approach
     */
    public function syncNilaiSemester(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(1800);
        
        $semesterId = $request->input('semester_id');
        $offset = (int) $request->input('offset', 0);
        
        if (!$semesterId) {
            return $this->errorResponse('semester_id is required');
        }
        
        try {
            $result = $syncService->syncNilaiBySemester($semesterId, $offset, 2000);
            
            return $this->successResponse('Nilai Semester ' . $semesterId, $result['total'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Nilai Semester Error', ['message' => $e->getMessage(), 'semester' => $semesterId]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync KRS by Semester (BULK)
     */
    public function syncKrsSemester(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(1800);
        
        $semesterId = $request->input('semester_id');
        $offset = (int) $request->input('offset', 0);
        
        if (!$semesterId) {
            return $this->errorResponse('semester_id is required');
        }
        
        try {
            // Using syncKrsSemester (2000 per batch)
            $result = $syncService->syncKrsSemester($semesterId, 2000, $offset);

            return $this->successResponse('KRS Semester ' . $semesterId, $result['total_from_api'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'offset' => $offset,
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync KRS Semester Error', ['message' => $e->getMessage(), 'semester' => $semesterId]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync KRS Global (Batch by Student)
     */
    public function syncKrs(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(1800);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncKrs($offset, 200);

            return $this->successResponse('KRS', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['batch_count'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync KRS Global Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Aktivitas Kuliah (Bulk by Semester)
     */
    /**
     * Sync Aktivitas Kuliah (By Student - Pagination)
     */
    public function syncAktivitasKuliah(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(1800);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            // Use per-student sync (limit 10 per batch to prevent timeout)
            $result = $syncService->syncAktivitasKuliah($offset, 10);

            return $this->successResponse('Aktivitas Kuliah', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['batch_count'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Aktivitas Kuliah Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Nilai Auto - Calculate semesters from earliest enrollment to current
     * Returns list of semesters to sync, then client calls syncNilaiSemester for each
     */
    public function syncNilaiAuto(Request $request): JsonResponse
    {
        // Get earliest angkatan from mahasiswa (or use default 2015 if null)
        $earliestAngkatan = \App\Models\Mahasiswa::whereNotNull('angkatan')
            ->where('angkatan', '!=', '')
            ->min('angkatan');
        
        // If no angkatan data, try to get from id_registrasi (periode format: 20231, 20222, etc)
        if (!$earliestAngkatan) {
            // Get earliest period from semester data that has nilai
            // Or default to 5 years back
            $currentYear = (int) date('Y');
            $earliestAngkatan = $currentYear - 5; // Default: 5 years back
        }
        
        // Current semester calculation
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        
        // Determine current semester: Ganjil (Aug-Jan) = 1, Genap (Feb-Jul) = 2
        if ($currentMonth >= 8 || $currentMonth <= 1) {
            // Ganjil semester
            $currentSemesterId = ($currentMonth >= 8 ? $currentYear : $currentYear - 1) . '1';
        } else {
            // Genap semester
            $currentSemesterId = ($currentYear - 1) . '2';
        }
        
        // Generate list of semesters from earliest to current
        $semesters = [];
        $startYear = (int) $earliestAngkatan;
        $endYear = (int) substr($currentSemesterId, 0, 4);
        $endSemType = (int) substr($currentSemesterId, 4, 1);
        
        for ($year = $startYear; $year <= $endYear; $year++) {
            // Ganjil (1)
            $semId = $year . '1';
            if ($semId <= $currentSemesterId) {
                $semesters[] = [
                    'id_semester' => $semId,
                    'nama' => "{$year}/" . ($year + 1) . " Ganjil"
                ];
            }
            
            // Genap (2)
            $semId = $year . '2';
            if ($semId <= $currentSemesterId) {
                $semesters[] = [
                    'id_semester' => $semId,
                    'nama' => "{$year}/" . ($year + 1) . " Genap"
                ];
            }
            
            // Pendek (3) - optional
            $semId = $year . '3';
            if ($semId <= $currentSemesterId) {
                $semesters[] = [
                    'id_semester' => $semId,
                    'nama' => "{$year}/" . ($year + 1) . " Pendek"
                ];
            }
        }
        
        // Verify semesters exist in database
        $existingSemesters = \App\Models\TahunAkademik::whereIn('id_semester', collect($semesters)->pluck('id_semester'))
            ->pluck('id_semester')
            ->toArray();
        
        $semesters = array_filter($semesters, fn($s) => in_array($s['id_semester'], $existingSemesters));
        $semesters = array_values($semesters); // Re-index
        
        return response()->json([
            'success' => true,
            'message' => 'Semester range calculated',
            'data' => [
                'earliest_angkatan' => $earliestAngkatan,
                'current_semester' => $currentSemesterId,
                'semesters' => $semesters,
                'total_semesters' => count($semesters),
            ]
        ]);
    }

    /**
     * Sync Reference Data
     * type: 'basic' (Agama, Pekerjaan, etc) or 'wilayah' (Regions)
     */
    public function syncReferensi(Request $request, ReferenceSyncService $refService): JsonResponse
    {
        session()->save();
        set_time_limit(600);
        
        $type = $request->input('type', 'basic');
        $offset = (int) $request->input('offset', 0);
        
        try {
            if ($type === 'wilayah') {
                $result = $refService->syncWilayah($offset, 2000);
                
                return $this->successResponse('Wilayah', $result['total_all'], $result['synced'], [], [
                    'offset' => $result['offset'],
                    'next_offset' => $result['next_offset'],
                    'has_more' => $result['has_more'],
                    'progress' => $result['progress'],
                ]);
            } else {
                // Sync all basic references
                $results = $refService->syncAllBasicReferences();
                
                // Calculate total synced
                $totalSynced = 0;
                $details = [];
                foreach ($results as $key => $res) {
                    if (isset($res['count'])) {
                        $totalSynced += $res['count'];
                        $details[$key] = $res['count'];
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil sync data referensi dasar',
                    'data' => [
                        'synced' => $totalSynced,
                        'details' => $details
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Sync Referensi Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Kurikulum (with pagination)
     * Includes syncing Mata Kuliah Kurikulum if requested? No, keep separate for now or chained.
     */
    public function syncKurikulum(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(300);
        
        $offset = (int) $request->input('offset', 0);
        $type = $request->input('type', 'kurikulum'); // 'kurikulum' or 'matkul_kurikulum'
        
        try {
            if ($type === 'matkul_kurikulum') {
                $result = $syncService->syncMatkulKurikulum($offset, 2000);
                $label = 'Mata Kuliah Kurikulum';
            } else {
                $result = $syncService->syncKurikulum($offset, 100);
                $label = 'Kurikulum';
            }

            return $this->successResponse($label, $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['total'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Kurikulum Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Kelas Kuliah (Classes)
     */
    public function syncKelasKuliah(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(1800);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncKelasKuliah($offset, 2000);

            return $this->successResponse('Kelas Kuliah', $result['total'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'total_all' => $result['total_all'] ?? 0,
                'progress' => $result['progress'] ?? 100,
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Kelas Kuliah Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Dosen Pengajar (Lecturer for each class)
     */

    /**
     * Sync Skala Nilai (Grading Scale)
     */
    public function syncSkalaNilai(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(300);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncSkalaNilai($offset, 500);

            return $this->successResponse('Skala Nilai', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['inserted'] ?? 0,
                'updated' => $result['updated'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
                'batch_size' => $result['total'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Skala Nilai Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Sync Dosen Pengajar (Lecturer for each class)
     */
    public function syncDosenPengajar(Request $request, NeoFeederSyncService $syncService): JsonResponse
    {
        session()->save();
        set_time_limit(1800);
        
        $offset = (int) $request->input('offset', 0);
        
        try {
            $result = $syncService->syncDosenPengajar($offset, 100);

            return $this->successResponse('Dosen Pengajar', $result['total_all'], $result['synced'], $result['errors'], [
                'inserted' => $result['assignments'] ?? 0, // Total assignments created
                'updated' => $result['synced'], // Classes with lecturer found
                'skipped' => 0,
                'failed' => $result['failed'],
                'batch_size' => $result['batch_count'],
                'offset' => $result['offset'],
                'next_offset' => $result['next_offset'],
                'has_more' => $result['has_more'],
                'progress' => $result['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Sync Dosen Pengajar Error', ['message' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Build success response with optional detailed stats
     */
    private function successResponse(string $type, int $total, int $synced, array $errors, ?array $extras = null): JsonResponse
    {
        $data = [
            'total_from_api' => $total,
            'synced' => $synced,
            'failed' => count($errors),
            'errors' => array_slice($errors, 0, 10),
        ];

        // Add detailed stats if available
        if ($extras !== null) {
            // Merge all extras into data
            $data = array_merge($data, $extras);
        }
        
        return response()->json([
            'success' => true,
            'message' => "Berhasil sync {$type}",
            'data' => $data,
        ]);
    }

    /**
     * Build error response
     */
    private function errorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ]);
    }
}

