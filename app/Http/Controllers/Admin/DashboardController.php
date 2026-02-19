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

    // ... (other methods unchanged) ...

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

