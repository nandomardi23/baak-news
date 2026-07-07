<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KalenderAkademik;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KalenderController extends Controller
{


    public function index(Request $request): Response
    {
        $tahunAkademikId = $request->input('tahun_akademik_id');
        $jenis = $request->input('jenis');

        // Get active or selected tahun akademik
        $activeTahun = $tahunAkademikId
            ? TahunAkademik::find($tahunAkademikId)
            : TahunAkademik::where('is_active', true)->first();

        $query = KalenderAkademik::with('tahunAkademik');
        
        if ($activeTahun) {
            $query->where('tahun_akademik_id', $activeTahun->id);
        }

        $kalender = $query->jenis($jenis)
            ->latest()
            ->get()
            ->map(fn(KalenderAkademik $item) => [
                'id' => $item->id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'tanggal_mulai' => $item->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai' => $item->tanggal_selesai?->format('Y-m-d'),
                'tanggal_format' => $item->tanggal_format,
                'jenis' => $item->jenis,
                'jenis_label' => $item->jenis_label,
                'warna' => $item->warna ?: $item->default_color,
                'tahun_akademik' => $item->tahunAkademik?->nama_semester,
                'duration_days' => $item->duration_days,
            ]);

        $dynamicJenis = KalenderAkademik::select('jenis')
            ->distinct()
            ->pluck('jenis')
            ->filter(fn($j) => !array_key_exists($j, KalenderAkademik::JENIS_OPTIONS))
            ->map(fn($j) => [
                'value' => $j,
                'label' => ucfirst($j),
                'color' => '#6B7280',
            ])->values();

        $jenisOptions = collect(KalenderAkademik::JENIS_OPTIONS)->map(fn($opt, $key) => [
            'value' => $key,
            'label' => $opt['label'],
            'color' => $opt['color'],
        ])->values()->concat($dynamicJenis);

        return Inertia::render('Admin/Kalender/Index', [
            'kalender' => $kalender,
            'filters' => [
                'tahun_akademik_id' => $activeTahun?->id,
                'jenis' => $jenis,
            ],
            'tahunAkademikOptions' => TahunAkademik::orderByDesc('id')->get(['id', 'nama_semester as nama']),
            'jenisOptions' => $jenisOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis' => 'required|string|max:50',
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'warna' => 'nullable|string|max:7',
        ]);

        KalenderAkademik::create($validated);

        return back()->with('success', 'Event kalender berhasil ditambahkan');
    }

    public function update(Request $request, KalenderAkademik $kalender): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis' => 'required|string|max:50',
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'warna' => 'nullable|string|max:7',
        ]);

        $kalender->update($validated);

        return back()->with('success', 'Event kalender berhasil diperbarui');
    }

    public function destroy(KalenderAkademik $kalender): RedirectResponse
    {
        try {
            $kalender->delete();
            return back()->with('success', 'Event kalender berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->with('error', 'Data tidak bisa dihapus karena sedang berelasi dengan data lain.');
            }
            return back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}
