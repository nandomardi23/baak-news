<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DokumenTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DokumenTemplateController extends Controller
{
    public function index()
    {
        $templates = DokumenTemplate::orderBy('created_at', 'desc')->paginate(15);
        return Inertia::render('Admin/DokumenTemplate/Index', [
            'templates' => $templates
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:20480', // max 20MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('dokumen_templates', 'public');

            DokumenTemplate::create([
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'kategori' => $request->kategori,
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->back()->with('success', 'Template dokumen berhasil diunggah.');
    }

    public function update(Request $request, DokumenTemplate $dokumen_template)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:20480',
        ]);

        $data = [
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($dokumen_template->file_path && Storage::disk('public')->exists($dokumen_template->file_path)) {
                Storage::disk('public')->delete($dokumen_template->file_path);
            }

            $file = $request->file('file');
            $data['file_path'] = $file->store('dokumen_templates', 'public');
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $dokumen_template->update($data);

        return redirect()->back()->with('success', 'Template dokumen berhasil diperbarui.');
    }

    public function destroy(DokumenTemplate $dokumen_template)
    {
        if ($dokumen_template->file_path && Storage::disk('public')->exists($dokumen_template->file_path)) {
            Storage::disk('public')->delete($dokumen_template->file_path);
        }

        $dokumen_template->delete();

        return redirect()->back()->with('success', 'Template dokumen berhasil dihapus.');
    }
}
