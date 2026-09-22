<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TemplateDesignerController extends Controller
{
    /**
     * List all templates
     */
    public function index(): Response
    {
        $templates = LetterTemplate::latest()->get();

        return Inertia::render('Admin/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Delete template
     */
    public function destroy(LetterTemplate $template): RedirectResponse
    {
        try {
            // Delete file
            if ($template->file_path) {
                Storage::disk('public')->delete($template->file_path);
            }

            $template->delete();

            return redirect()->route('admin.templates.index')
                ->with('success', 'Template berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.templates.index')->with('error', 'Data tidak bisa dihapus karena sedang berelasi dengan data lain.');
            }
            return redirect()->route('admin.templates.index')->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    /**
     * Handle PDF template upload
     */
    public function uploadPdf(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:surat,krs,khs,transkrip,kartu_ujian',
            'template_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $path = $this->storeTemplateFile($request->file('template_file'), $validated['type']);
        $this->createTemplateRecord($validated['name'], $validated['type'], $path);

        return back()->with('success', 'Template ' . strtoupper($validated['type']) . ' berhasil diupload');
    }

    private function storeTemplateFile($file, string $type): string
    {
        if (!file_exists(storage_path('app/public/template-surat'))) {
            mkdir(storage_path('app/public/template-surat'), 0755, true);
        }

        $filename = $type . '_' . time() . '.pdf';
        return $file->storeAs('template-surat', $filename, 'public');
    }

    private function createTemplateRecord(string $name, string $type, string $path): void
    {
        LetterTemplate::create([
            'name' => $name,
            'slug' => \Str::slug($name) . '-' . time(),
            'type' => $type,
            'file_path' => $path,
            'page_size' => 'A4',
            'orientation' => 'portrait',
            'is_active' => true,
        ]);
    }
}
