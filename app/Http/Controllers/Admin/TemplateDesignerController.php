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
        $templates = LetterTemplate::orderBy('name')->get();

        return Inertia::render('Admin/Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Delete template
     */
    public function destroy(LetterTemplate $template): RedirectResponse
    {
        // Delete file
        if ($template->file_path) {
            Storage::disk('public')->delete($template->file_path);
        }

        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template berhasil dihapus');
    }

    /**
     * Handle PDF template upload
     */
    public function uploadPdf(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:surat,krs,khs,transkrip,kartu_ujian',
            'template_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('template_file');
        $type = $request->input('type');
        $name = $request->input('name');
        
        // Ensure directory exists
        if (!file_exists(storage_path('app/public/template-surat'))) {
            mkdir(storage_path('app/public/template-surat'), 0755, true);
        }

        $filename = $type . '_' . time() . '.pdf';
        $path = $file->storeAs('template-surat', $filename, 'public');

        // Create or update template record
        // For KRS, KHS, and Transkrip, we might only want one active template at a time
        // But for "surat", we can have many.
        
        LetterTemplate::create([
            'name' => $name,
            'slug' => \Str::slug($name) . '-' . time(),
            'type' => $type,
            'file_path' => $path,
            'page_size' => 'A4',
            'orientation' => 'portrait',
            'is_active' => true,
        ]);

        return back()->with('success', 'Template ' . strtoupper($type) . ' berhasil diupload');
    }
}
