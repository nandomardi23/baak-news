<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\ProgramStudi;
use App\Models\SuratPengajuan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
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

        // Mahasiswa per prodi
        $mahasiswaPerProdi = ProgramStudi::withCount(['mahasiswa' => fn($q) => $q->active()])
            ->orderByDesc('mahasiswa_count')
            ->get()
            ->map(fn($prodi) => [
                'nama' => $prodi->nama_prodi,
                'total' => $prodi->mahasiswa_count,
            ]);

        // Surat per status
        $suratPerStatus = SuratPengajuan::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->total]);

        // Get recent pengajuan
        $recentPengajuan = SuratPengajuan::with(['mahasiswa', 'processedBy'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'mahasiswa' => [
                    'nim' => $item->mahasiswa->nim,
                    'nama' => $item->mahasiswa->nama,
                ],
                'jenis_surat' => $item->jenis_surat_label,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'status_badge' => $item->status_badge,
                'created_at' => $item->created_at->format('d M Y H:i'),
            ]);

        // Pengajuan per jenis surat
        $pengajuanPerJenis = SuratPengajuan::selectRaw('jenis_surat, count(*) as total')
            ->groupBy('jenis_surat')
            ->get()
            ->mapWithKeys(fn($item) => [$item->jenis_surat => $item->total]);

        // Mahasiswa per angkatan (new)
        $mahasiswaPerAngkatan = Mahasiswa::active()
            ->selectRaw('angkatan, count(*) as total')
            ->whereNotNull('angkatan')
            ->groupBy('angkatan')
            ->orderByDesc('angkatan')
            ->take(10)
            ->get()
            ->map(fn($item) => [
                'angkatan' => $item->angkatan,
                'total' => $item->total,
            ]);

        // IPK distribution (optimized - 1 query instead of 5)
        $ipkRaw = Mahasiswa::active()
            ->selectRaw("
                COUNT(CASE WHEN ipk >= 3.50 AND ipk <= 4.00 THEN 1 END) as cumlaude,
                COUNT(CASE WHEN ipk >= 3.00 AND ipk < 3.50 THEN 1 END) as sangat_baik,
                COUNT(CASE WHEN ipk >= 2.50 AND ipk < 3.00 THEN 1 END) as baik,
                COUNT(CASE WHEN ipk >= 2.00 AND ipk < 2.50 THEN 1 END) as cukup,
                COUNT(CASE WHEN ipk > 0 AND ipk < 2.00 THEN 1 END) as kurang
            ")
            ->first();
        
        $ipkDistribution = [
            ['range' => '3.50 - 4.00', 'total' => $ipkRaw->cumlaude ?? 0],
            ['range' => '3.00 - 3.49', 'total' => $ipkRaw->sangat_baik ?? 0],
            ['range' => '2.50 - 2.99', 'total' => $ipkRaw->baik ?? 0],
            ['range' => '2.00 - 2.49', 'total' => $ipkRaw->cukup ?? 0],
            ['range' => '< 2.00', 'total' => $ipkRaw->kurang ?? 0],
        ];

        // Monthly pengajuan trend (last 12 months)
        $monthlyPengajuan = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = SuratPengajuan::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $monthlyPengajuan->push([
                'bulan' => $date->translatedFormat('M Y'),
                'total' => $count,
            ]);
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'mahasiswaPerProdi' => $mahasiswaPerProdi,
            'suratPerStatus' => $suratPerStatus,
            'recentPengajuan' => $recentPengajuan,
            'pengajuanPerJenis' => $pengajuanPerJenis,
            'mahasiswaPerAngkatan' => $mahasiswaPerAngkatan,
            'ipkDistribution' => $ipkDistribution,
            'monthlyPengajuan' => $monthlyPengajuan,
        ]);
    }
}

