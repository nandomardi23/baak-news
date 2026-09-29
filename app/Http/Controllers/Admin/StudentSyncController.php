<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Sync\StudentSyncService;
use App\Traits\ApiSyncResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentSyncController extends Controller
{
    use ApiSyncResponses;

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
}
