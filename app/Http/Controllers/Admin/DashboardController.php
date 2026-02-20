<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\ProgramStudi;
use App\Models\SuratPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->getStats(),
            'mahasiswaPerProdi' => $this->getMahasiswaPerProdi(),
            'suratPerStatus' => $this->getSuratPerStatus(),
            'recentPengajuan' => $this->getRecentPengajuan(),
            'pengajuanPerJenis' => $this->getPengajuanPerJenis(),
            'mahasiswaPerAngkatan' => $this->getMahasiswaPerAngkatan(),
            'ipkDistribution' => $this->getIpkDistribution(),
            'monthlyPengajuan' => $this->getMonthlyPengajuan(),
        ]);
    }

    private function getStats(): array
    {
        return Cache::remember('dashboard_stats', 600, function () {
            return [
                'total_mahasiswa' => Mahasiswa::count(),
                'mahasiswa_aktif' => Mahasiswa::active()->count(),
                'total_dosen' => Dosen::count(),
                'dosen_aktif' => Dosen::active()->count(),
                'total_prodi' => ProgramStudi::active()->count(),
                'pengajuan_pending' => SuratPengajuan::pending()->count(),
                'pengajuan_hari_ini' => SuratPengajuan::whereDate('created_at', today())->count(),
                'pengajuan_bulan_ini' => SuratPengajuan::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];
        });
    }

    private function getMahasiswaPerProdi(): array
    {
        return ProgramStudi::active()
            ->withCount(['mahasiswa' => fn($q) => $q->active()])
            ->orderByDesc('mahasiswa_count')
            ->get()
            ->map(fn($prodi) => [
                'nama' => $prodi->nama_prodi ?? $prodi->nama,
                'total' => $prodi->mahasiswa_count,
            ])
            ->toArray();
    }

    private function getSuratPerStatus(): array
    {
        return SuratPengajuan::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }

    private function getRecentPengajuan()
    {
        return SuratPengajuan::with('mahasiswa:id,nim,nama')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'mahasiswa' => [
                    'nim' => $s->mahasiswa->nim ?? '-',
                    'nama' => $s->mahasiswa->nama ?? '-',
                ],
                'jenis_surat' => $s->jenis_surat,
                'status' => $s->status,
                'status_label' => $s->status_label,
                'status_badge' => $s->status_badge,
                'created_at' => $s->created_at->format('d M Y'),
            ]);
    }

    private function getPengajuanPerJenis(): array
    {
        return SuratPengajuan::selectRaw('jenis_surat, count(*) as total')
            ->groupBy('jenis_surat')
            ->pluck('total', 'jenis_surat')
            ->toArray();
    }

    private function getMahasiswaPerAngkatan(): array
    {
        return Mahasiswa::active()
            ->selectRaw('angkatan, count(*) as total')
            ->groupBy('angkatan')
            ->orderByDesc('angkatan')
            ->get()
            ->map(fn($m) => [
                'angkatan' => (string) $m->angkatan,
                'total' => $m->total,
            ])
            ->toArray();
    }

    private function getIpkDistribution(): array
    {
        $ranges = [
            '3.50 - 4.00' => [3.50, 4.00],
            '3.00 - 3.49' => [3.00, 3.49],
            '2.50 - 2.99' => [2.50, 2.99],
            '2.00 - 2.49' => [2.00, 2.49],
            '< 2.00' => [0, 1.99],
        ];

        $result = [];
        foreach ($ranges as $label => [$min, $max]) {
            $result[] = [
                'range' => $label,
                'total' => Mahasiswa::active()
                    ->whereBetween('ipk', [$min, $max])
                    ->count(),
            ];
        }
        return $result;
    }

    private function getMonthlyPengajuan()
    {
        // Optimization: Single query aggregation instead of 12 separate queries
        $data = SuratPengajuan::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, count(*) as total')
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('year', 'month')
            ->get()
            ->mapWithKeys(fn($item) => [$item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT) => $item->total]);

        $monthlyPengajuan = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $monthlyPengajuan->push([
                'bulan' => $date->translatedFormat('M Y'),
                'total' => $data[$key] ?? 0,
            ]);
        }
        return $monthlyPengajuan;
    }
}

