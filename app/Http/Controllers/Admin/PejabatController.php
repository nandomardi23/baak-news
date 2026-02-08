<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pejabat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PejabatController extends Controller
{
    public function index(): Response
    {
        $pejabat = Pejabat::orderBy('is_active', 'desc')
            ->orderBy('jabatan')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'nama' => $item->nama,
                'nama_lengkap' => $item->nama_lengkap,
                'nip' => $item->nip,
                'nidn' => $item->nidn,
                'nik' => $item->nik,
                'jabatan' => $item->jabatan,
                'pangkat_golongan' => $item->pangkat_golongan,
                'gelar_depan' => $item->gelar_depan,
                'gelar_belakang' => $item->gelar_belakang,
                'periode_awal' => $item->periode_awal?->format('d M Y'),
                'periode_akhir' => $item->periode_akhir?->format('d M Y'),
                'tandatangan_path' => $item->tandatangan_path,
                'is_active' => $item->is_active,
            ]);

        return Inertia::render('Admin/Pejabat/Index', [
            'pejabat' => $pejabat,
            'jabatanOptions' => $this->getJabatanOptions(),
            'dosenOptions' => $this->getDosenOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Pejabat/Form', [
            'jabatanOptions' => $this->getJabatanOptions(),
            'dosenOptions' => $this->getDosenOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'nidn' => 'nullable|string|max:50',
            'nik' => 'nullable|string|max:16',
            'jabatan' => 'required|string|max:100',
            'pangkat_golongan' => 'nullable|string|max:100',
            'gelar_depan' => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:100',
            'periode_awal' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after:periode_awal',
            'tandatangan' => 'nullable|image|mimes:png,jpg,jpeg|max:1024',
            'is_active' => 'boolean',
            'dosen_id' => 'nullable|exists:dosen,id',
        ]);

        if ($request->hasFile('tandatangan')) {
            $validated['tandatangan_path'] = $request->file('tandatangan')
                ->store('tandatangan', 'public');
        }

        unset($validated['tandatangan']);

        $pejabat = Pejabat::create($validated);

        ActivityLog::log('created', "Menambahkan pejabat baru: {$pejabat->nama_lengkap} ({$pejabat->jabatan})", $pejabat);

        return redirect()->route('admin.pejabat.index')
            ->with('success', 'Pejabat berhasil ditambahkan');
    }

    public function edit(Pejabat $pejabat): Response
    {
        return Inertia::render('Admin/Pejabat/Form', [
            'pejabat' => [
                'id' => $pejabat->id,
                'nama' => $pejabat->nama,
                'nip' => $pejabat->nip,
                'nidn' => $pejabat->nidn,
                'nik' => $pejabat->nik,
                'jabatan' => $pejabat->jabatan,
                'pangkat_golongan' => $pejabat->pangkat_golongan,
                'gelar_depan' => $pejabat->gelar_depan,
                'gelar_belakang' => $pejabat->gelar_belakang,
                'periode_awal' => $pejabat->periode_awal?->format('Y-m-d'),
                'periode_akhir' => $pejabat->periode_akhir?->format('Y-m-d'),
                'tandatangan_path' => $pejabat->tandatangan_path,
                'is_active' => $pejabat->is_active,
                'dosen_id' => $pejabat->dosen_id,
            ],
            'jabatanOptions' => $this->getJabatanOptions(),
            'dosenOptions' => $this->getDosenOptions(),
        ]);
    }

    public function update(Request $request, Pejabat $pejabat): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'nidn' => 'nullable|string|max:50',
            'nik' => 'nullable|string|max:16',
            'jabatan' => 'required|string|max:100',
            'pangkat_golongan' => 'nullable|string|max:100',
            'gelar_depan' => 'nullable|string|max:50',
            'gelar_belakang' => 'nullable|string|max:100',
            'periode_awal' => 'nullable|date',
            'periode_akhir' => 'nullable|date|after:periode_awal',
            'tandatangan' => 'nullable|image|mimes:png,jpg,jpeg|max:1024',
            'is_active' => 'boolean',
            'dosen_id' => 'nullable|exists:dosen,id',
        ]);

        if ($request->hasFile('tandatangan')) {
            // Delete old file
            if ($pejabat->tandatangan_path) {
                Storage::disk('public')->delete($pejabat->tandatangan_path);
            }
            $validated['tandatangan_path'] = $request->file('tandatangan')
                ->store('tandatangan', 'public');
        }

        unset($validated['tandatangan']);

        $pejabat->update($validated);

        ActivityLog::log('updated', "Memperbarui data pejabat: {$pejabat->nama_lengkap} ({$pejabat->jabatan})", $pejabat);

        return redirect()->route('admin.pejabat.index')
            ->with('success', 'Pejabat berhasil diperbarui');
    }

    public function destroy(Pejabat $pejabat): RedirectResponse
    {
        $description = "Menghapus pejabat: {$pejabat->nama_lengkap} ({$pejabat->jabatan})";

        if ($pejabat->tandatangan_path) {
            Storage::disk('public')->delete($pejabat->tandatangan_path);
        }

        $pejabat->delete();

        ActivityLog::log('deleted', $description);

        return redirect()->route('admin.pejabat.index')
            ->with('success', 'Pejabat berhasil dihapus');
    }

    private function getJabatanOptions(): array
    {
        // Get active master jabatans
        $masterJabatans = \App\Models\Jabatan::where('is_active', true)
            ->orderBy('nama_jabatan')
            ->pluck('nama_jabatan')
            ->toArray();

        // Get additional jabatans already used in Pejabat (in case some were deleted from master but still used)
        $usedJabatans = Pejabat::whereNotNull('jabatan')
            ->distinct()
            ->pluck('jabatan')
            ->toArray();

        // Merge, unique, and sort
        $merged = array_unique(array_merge($masterJabatans, $usedJabatans));
        sort($merged);

        return array_values($merged);
    }

    private function getDosenOptions(): array
    {
        return \App\Models\Dosen::active()
            ->orderBy('nama')
            ->get()
            ->map(fn($dosen) => [
                'id' => $dosen->id,
                'nama' => $dosen->nama,
                'nama_lengkap' => $dosen->nama_lengkap, // Assuming accessor exists
                'nip' => $dosen->nip,
                'nidn' => $dosen->nidn,
                'gelar_depan' => $dosen->gelar_depan,
                'gelar_belakang' => $dosen->gelar_belakang,
            ])
            ->toArray();
    }
}
