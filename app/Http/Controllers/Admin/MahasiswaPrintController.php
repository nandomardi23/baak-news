<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MahasiswaPrintController extends Controller
{
    use \App\Traits\GeneratesPdf;

    public function printKrs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('krs', $mahasiswa, $tahunAkademik);
    }

    public function printKhs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('khs', $mahasiswa, $tahunAkademik);
    }

    public function printKartuUjian(Request $request, Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('kartu_ujian', $mahasiswa, $tahunAkademik, $request->input('jenis', 'uts'));
    }

    public function printTranskrip(Mahasiswa $mahasiswa, Request $request): BinaryFileResponse|\Illuminate\Http\Response
    {
        return $this->pdfDownloadResponse('transkrip', $mahasiswa, null, $request->input('jenis', 'reguler'));
    }
}
