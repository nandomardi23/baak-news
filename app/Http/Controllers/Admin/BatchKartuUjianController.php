<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BatchKartuUjianController extends Controller
{
    /**
     * Show batch kartu ujian page with student list
     */
    public function index(Request $request): Response
    {
        $tahunAkademik = TahunAkademik::orderBy('id_semester', 'desc')->get();
        $prodi = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'nama_prodi']);

        // Get unique angkatan values
        $angkatanList = Mahasiswa::active()
            ->whereNotNull('angkatan')
            ->where('angkatan', '!=', '')
            ->distinct()
            ->orderByDesc('angkatan')
            ->pluck('angkatan');

        $mahasiswa = collect();
        $selectedTa = null;

        if ($request->filled('tahun_akademik_id')) {
            $selectedTa = TahunAkademik::find($request->tahun_akademik_id);

            $query = Mahasiswa::with(['programStudi'])
                ->whereHas('krs', function ($q) use ($request) {
                    $q->where('tahun_akademik_id', $request->tahun_akademik_id);
                })
                ->active();

            if ($request->filled('angkatan')) {
                $query->where('angkatan', $request->angkatan);
            }

            if ($request->filled('prodi_id')) {
                $query->where('program_studi_id', $request->prodi_id);
            }

            $mahasiswa = $query->orderBy('nama')->get()->map(fn($m) => [
                'id' => $m->id,
                'nim' => $m->nim,
                'nama' => $m->nama,
                'prodi' => $m->programStudi?->nama_prodi,
                'angkatan' => $m->angkatan,
            ]);
        }

        return Inertia::render('Admin/Mahasiswa/BatchKartuUjian', [
            'tahunAkademik' => $tahunAkademik->map(fn($ta) => [
                'id' => $ta->id,
                'nama' => $ta->nama_semester,
            ]),
            'prodi' => $prodi,
            'angkatanList' => $angkatanList,
            'mahasiswa' => $mahasiswa,
            'filters' => $request->only(['tahun_akademik_id', 'angkatan', 'prodi_id']),
            'selectedSemester' => $selectedTa?->nama_semester,
        ]);
    }

    /**
     * Batch print kartu ujian for multiple students (admin only)
     */
    public function print(Request $request): BinaryFileResponse|\Illuminate\Http\Response
    {
        $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'angkatan' => 'nullable|string',
            'prodi_id' => 'nullable|exists:program_studi,id',
        ]);

        $tahunAkademik = TahunAkademik::findOrFail($request->tahun_akademik_id);

        // Build query for students who have KRS in this semester
        $query = Mahasiswa::with([
            'programStudi',
            'krs' => function ($q) use ($tahunAkademik) {
                $q->where('tahun_akademik_id', $tahunAkademik->id);
            }
        ])
            ->whereHas('krs', function ($q) use ($tahunAkademik) {
                $q->where('tahun_akademik_id', $tahunAkademik->id);
            })
            ->active();

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        if ($request->filled('prodi_id')) {
            $query->where('program_studi_id', $request->prodi_id);
        }

        $mahasiswaList = $query->orderBy('nama')->get();

        if ($mahasiswaList->isEmpty()) {
            return response('Tidak ada mahasiswa yang memenuhi kriteria', 404);
        }

        try {
            $pdfService = new \App\Services\Pdfs\KartuUjianService();
            $filename = $pdfService->generateBatch($mahasiswaList, $tahunAkademik, $request->input('jenis', 'uts'));

            $path = storage_path('app/public/surat/' . $filename);

            return response()->file($path, ['Content-Type' => 'application/pdf'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::error("Error generating batch kartu ujian", [
                'semester' => $tahunAkademik->id,
                'error' => $e->getMessage(),
            ]);
            return response('Error generating PDF: ' . $e->getMessage(), 500);
        }
    }
}
