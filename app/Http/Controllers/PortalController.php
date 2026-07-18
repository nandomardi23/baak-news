<?php

namespace App\Http\Controllers;

use App\Models\DokumenTemplate;
use App\Models\KalenderAkademik;
use App\Models\Mahasiswa;
use App\Models\Pejabat;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    public function index(Request $request): Response
    {
        // Fase 4: Cache Program Studi
        $prodi = Cache::remember('public.prodi', 3600 * 24, function () {
            return ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);
        });
        
        $templatesQuery = DokumenTemplate::query();

        if ($request->filled('search_template')) {
            $templatesQuery->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search_template . '%')
                  ->orWhere('deskripsi', 'like', '%' . $request->search_template . '%');
            });
        }

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $templatesQuery->where('kategori', $request->kategori);
        }

        $templates = $templatesQuery->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        return Inertia::render('Landing/Home', [
            'prodi' => $prodi,
            'templates' => $templates,
            'filters' => $request->only(['search_template', 'kategori']),
        ]);
    }

    public function profile(): Response
    {
        // Fase 4: Cache Pejabat
        $pejabat = Cache::remember('public.pejabat', 3600 * 24, function () {
            return Pejabat::active()
                ->where('jabatan', 'not like', '%Kaprodi%')
                ->where('jabatan', 'not like', '%Program Studi%')
                ->where('jabatan', 'not like', '%Ketua%') // Exclude Ketua Stikes
                ->orderBy('id') // Simple ordering for now
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'nama' => $p->nama_lengkap,
                    'jabatan' => $p->jabatan,
                    'nip' => $p->nip,
                    'nidn' => $p->nidn,
                    'pangkat_golongan' => $p->pangkat_golongan,
                    'foto_path' => $p->foto_path,
                    'periode_awal' => $p->periode_awal?->format('Y'),
                    'periode_akhir' => $p->periode_akhir?->format('Y'),
                ])->toArray();
        });

        return Inertia::render('Landing/Profile', [
            'pejabat' => $pejabat,
        ]);
    }

    public function search(Request $request): Response
    {
        $request->validate([
            'search' => 'required|string|min:3',
        ]);

        $mahasiswa = Mahasiswa::with('programStudi')
            ->search($request->search)
            ->active()
            ->take(20)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'nim' => $item->nim,
                'nama' => $item->nama,
                'prodi' => $item->programStudi?->nama_prodi,
                'angkatan' => $item->angkatan,
            ]);

        return Inertia::render('Landing/SearchResult', [
            'mahasiswa' => $mahasiswa,
            'search' => $request->search,
        ]);
    }

    public function kalender(): Response
    {
        // Fase 4: Cache Kalender Akademik
        $activeTahun = Cache::remember('public.active_tahun', 3600 * 2, function () {
            return TahunAkademik::where('is_active', true)->first();
        });

        $kalender = Cache::remember('public.kalender.' . ($activeTahun?->id ?? 'all'), 3600 * 2, function () use ($activeTahun) {
            return KalenderAkademik::with('tahunAkademik')
                ->when($activeTahun, fn($q) => $q->where('tahun_akademik_id', $activeTahun->id))
                ->orderBy('tanggal_mulai')
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'deskripsi' => $item->deskripsi,
                    'tanggal_mulai' => $item->tanggal_mulai->format('Y-m-d'),
                    'tanggal_selesai' => $item->tanggal_selesai?->format('Y-m-d'),
                    'tanggal_format' => $item->tanggal_format,
                    'jenis' => $item->jenis,
                    'jenis_label' => $item->jenis_label,
                    'warna' => $item->warna ?: $item->default_color,
                    'duration_days' => $item->duration_days,
                ])->toArray();
        });

        $upcomingEvents = Cache::remember('public.kalender_upcoming.' . ($activeTahun?->id ?? 'all'), 3600 * 2, function () use ($activeTahun) {
            return KalenderAkademik::upcoming()
                ->when($activeTahun, fn($q) => $q->where('tahun_akademik_id', $activeTahun->id))
                ->take(5)
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'judul' => $item->judul,
                    'tanggal_format' => $item->tanggal_format,
                    'jenis_label' => $item->jenis_label,
                    'warna' => $item->warna ?: $item->default_color,
                ])->toArray();
        });

        return Inertia::render('Landing/Kalender', [
            'kalender' => $kalender,
            'tahunAkademik' => $activeTahun ? [
                'id' => $activeTahun->id,
                'nama' => $activeTahun->nama_semester,
            ] : null,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function downloadDokumenTemplate(DokumenTemplate $dokumen_template)
    {
        if ($dokumen_template->file_path && Storage::disk('public')->exists($dokumen_template->file_path)) {
            return response()->download(storage_path('app/public/' . $dokumen_template->file_path), $dokumen_template->nama . '.' . $dokumen_template->file_type);
        }
        abort(404, 'File tidak ditemukan.');
    }
}
