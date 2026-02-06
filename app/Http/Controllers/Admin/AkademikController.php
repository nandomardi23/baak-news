<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AkademikController extends Controller
{
    /**
     * Mata Kuliah page
     */
    public function mataKuliah(Request $request): Response
    {
        $query = MataKuliah::with('programStudi');

        if ($request->filled('prodi')) {
            $query->where('id_prodi', $request->prodi);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_matkul', 'like', "%{$request->search}%")
                  ->orWhere('kode_matkul', 'like', "%{$request->search}%");
            });
        }

        $mataKuliah = $query->orderBy('kode_matkul')
            ->paginate(20)
            ->through(fn($mk) => [
                'id' => $mk->id,
                'kode_matkul' => $mk->kode_matkul,
                'nama_matkul' => $mk->nama_matkul,
                'sks_mata_kuliah' => $mk->sks_mata_kuliah,
                'sks_teori' => $mk->sks_teori,
                'sks_praktek' => $mk->sks_praktek,
                'prodi' => $mk->programStudi?->nama_prodi,
            ]);

        $prodiList = ProgramStudi::active()->orderBy('nama_prodi')->get(['id', 'id_prodi', 'nama_prodi']);

        return Inertia::render('Admin/Akademik/MataKuliah', [
            'mataKuliah' => $mataKuliah,
            'prodiList' => $prodiList,
            'filters' => $request->only(['prodi', 'search']),
        ]);
    }

    /**
     * Tahun Akademik (Semester) page
     * Only shows relevant semesters (from 2015 onwards to reduce list)
     */
    public function semester(): Response
    {
        // Only show semesters from 2015 onwards up to current year + 1
        // This filters out garbage data like 2035, 2034 etc.
        $currentYear = date('Y');
        $maxSemester = ($currentYear + 1) . '3';
        
        $semesters = TahunAkademik::where('id_semester', '>=', '20151')
            ->where('id_semester', '<=', $maxSemester)
            ->orderBy('id_semester', 'desc')
            ->get()
            ->map(fn($ta) => [
                'id' => $ta->id,
                'id_semester' => $ta->id_semester,
                'nama_semester' => $ta->nama_semester,
                'tahun' => $ta->tahun,
                'semester' => $ta->semester,
                'tanggal_mulai' => $ta->tanggal_mulai?->format('d M Y'),
                'tanggal_selesai' => $ta->tanggal_selesai?->format('d M Y'),
                'is_active' => $ta->is_active,
            ]);

        return Inertia::render('Admin/Akademik/Semester', [
            'semesters' => $semesters,
        ]);
    }

    /**
     * Program Studi page
     */
    public function prodi(): Response
    {
        $prodiList = ProgramStudi::orderBy('nama_prodi')
            ->get()
            ->map(fn($prodi) => [
                'id' => $prodi->id,
                'id_prodi' => $prodi->id_prodi,
                'kode_prodi' => $prodi->kode_prodi,
                'nama_prodi' => $prodi->nama_prodi,
                'jenjang' => $prodi->jenjang,
                'jenis_program' => $prodi->jenis_program,
                'akreditasi' => $prodi->akreditasi,
                'is_active' => $prodi->is_active,
            ]);

        return Inertia::render('Admin/Akademik/Prodi', [
            'prodiList' => $prodiList,
        ]);
    }
}
