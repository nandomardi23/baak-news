<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AktivitasKuliahController extends Controller
{
    use \App\Traits\HasDataTable;

    public function index(Request $request): Response
    {
        $query = $this->buildQuery($request);

        // Apply search, sort, pagination
        $data = $this->applyDataTable($query, $request, [
            'nim',
            'nama_mahasiswa'
        ], 25);

        // Transform results
        $data->through(fn($item) => $this->transformData($item));

        // Filters data
        [$prodi, $semesters] = $this->getFilters();

        // Summary stats
        $stats = $this->calculateStats($request);

        return Inertia::render('Admin/AktivitasKuliah/Index', [
            'aktivitasKuliah' => $data,
            'prodi' => $prodi,
            'semesters' => $semesters,
            'stats' => $stats,
            'filters' => $request->only(['search', 'prodi', 'semester', 'status', 'sort_field', 'sort_direction']),
        ]);
    }

    private function buildQuery(Request $request)
    {
        $query = AktivitasKuliah::query()
            ->with(['mahasiswa.programStudi', 'semester']);

        if ($request->filled('semester')) {
            $query->where('id_semester', $request->semester);
        }

        if ($request->filled('prodi')) {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('program_studi_id', $request->prodi);
            });
        }

        if ($request->filled('status')) {
            $query->where('id_status_mahasiswa', $request->status);
        }

        return $query;
    }

    private function transformData(AktivitasKuliah $item): array
    {
        return [
            'id' => $item->id,
            'nim' => $item->nim,
            'nama_mahasiswa' => $item->nama_mahasiswa,
            'semester' => $item->semester?->nama_semester,
            'id_semester' => $item->id_semester,
            'prodi' => $item->mahasiswa?->programStudi?->nama_prodi,
            'id_status_mahasiswa' => $item->id_status_mahasiswa,
            'status' => $this->statusLabel($item->id_status_mahasiswa),
            'ips' => $item->ips !== null ? (float) $item->ips : null,
            'ipk' => $item->ipk !== null ? (float) $item->ipk : null,
            'sks_semester' => $item->sks_semester,
            'sks_total' => $item->sks_total,
        ];
    }

    private function getFilters(): array
    {
        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);
        $semesters = TahunAkademik::whereIn(
            'id_semester',
            AktivitasKuliah::select('id_semester')->distinct()
        )->orderBy('id_semester', 'desc')->get(['id_semester', 'nama_semester'])->values();

        return [$prodi, $semesters];
    }

    private function calculateStats(Request $request): array
    {
        $statsQuery = AktivitasKuliah::query();
        
        if ($request->filled('semester')) {
            $statsQuery->where('id_semester', $request->semester);
        }
        if ($request->filled('prodi')) {
            $statsQuery->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('program_studi_id', $request->prodi);
            });
        }

        return [
            'total' => $statsQuery->count(),
            'rata_ipk' => round((float) $statsQuery->avg('ipk'), 2),
            'rata_ips' => round((float) $statsQuery->avg('ips'), 2),
        ];
    }

    private function statusLabel(?string $id): string
    {
        return match ($id) {
            'A' => 'Aktif',
            'C' => 'Cuti',
            'D' => 'Drop Out',
            'K' => 'Keluar',
            'L' => 'Lulus',
            'N' => 'Non-Aktif',
            default => $id ?? '-',
        };
    }
}
