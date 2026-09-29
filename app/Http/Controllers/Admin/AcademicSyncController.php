<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Services\Sync\AcademicSyncService;
use App\Traits\ApiSyncResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicSyncController extends Controller
{
    use ApiSyncResponses;

    public function syncKrs(Request $request, AcademicSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count KRS', ['total' => $syncService->getCountKrs($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, function($o, $l, $sem, $ss) use ($syncService) {
            return $sem ? $syncService->syncKrs($o, $l, $sem, $ss) : $syncService->syncKrsAllSemesters($o, $l, $ss);
        }, 'Sync KRS berhasil');
    }

    public function syncKrsMahasiswa(Mahasiswa $mahasiswa, AcademicSyncService $syncService): \Illuminate\Http\RedirectResponse
    {
        try {
            if (!$mahasiswa->id_registrasi_mahasiswa) {
                throw new \Exception("Mahasiswa belum memiliki ID Registrasi");
            }

            $result = $syncService->syncKrsMahasiswa($mahasiswa->id_registrasi_mahasiswa);

            $msg = "Berhasil sync KRS: {$result['synced']} semester synced, {$result['total']} total dari API.";
            if (($result['skipped'] ?? 0) > 0) {
                $msg .= " {$result['skipped']} semester dilewati (belum ada di data semester lokal).";
            }
            if (!empty($result['errors'])) {
                $msg .= " Errors: " . count($result['errors']);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sync KRS: ' . $e->getMessage());
        }
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
