<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Traits\GeneratesPdf;
use Illuminate\Http\Request;

class StudentDocumentController extends Controller
{
    use GeneratesPdf;

    public function printKrs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('krs', $mahasiswa, $tahunAkademik);
    }

    public function printKhs(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('khs', $mahasiswa, $tahunAkademik);
    }

    public function printTranskrip(Mahasiswa $mahasiswa, string $jenis = 'reguler')
    {
        return $this->pdfInlineResponse('transkrip', $mahasiswa, null, $jenis);
    }

    public function printKartuUjian(Request $request, Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik)
    {
        return $this->pdfInlineResponse('kartu_ujian', $mahasiswa, $tahunAkademik, $request->get('jenis', 'uts'));
    }
}
