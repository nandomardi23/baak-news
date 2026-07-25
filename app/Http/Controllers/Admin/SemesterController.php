<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Inertia\Inertia;
use Inertia\Response;

class SemesterController extends Controller
{
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
}
