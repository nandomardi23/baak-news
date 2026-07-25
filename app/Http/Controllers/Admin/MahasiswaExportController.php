<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\MahasiswaExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MahasiswaExportController extends Controller
{
    /**
     * Export mahasiswa to Excel
     */
    public function export(Request $request): BinaryFileResponse
    {
        $prodiId = $request->input('prodi') ? (int) $request->input('prodi') : null;
        $search = $request->input('search');

        $filename = 'mahasiswa_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new MahasiswaExport($prodiId, $search),
            $filename
        );
    }
}
