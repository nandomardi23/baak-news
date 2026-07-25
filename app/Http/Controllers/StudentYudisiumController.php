<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentYudisiumController extends Controller
{
    public function yudisium(Mahasiswa $mahasiswa): Response
    {
        $requirements = \App\Models\YudisiumRequirement::active()->get();
        $checklists = $mahasiswa->yudisiumChecklists()->get()->keyBy('yudisium_requirement_id');

        $data = $requirements->map(function ($req) use ($checklists) {
            $checklist = $checklists->get($req->id);
            return [
                'id' => $req->id,
                'nama_syarat' => $req->nama_syarat,
                'deskripsi' => $req->deskripsi,
                'is_upload_required' => $req->is_upload_required,
                'status' => $checklist ? $checklist->status : 'pending',
                'status_label' => $checklist ? $checklist->status_label : 'Belum Ada',
                'status_badge' => $checklist ? $checklist->status_badge : 'pending',
                'catatan' => $checklist ? $checklist->catatan : null,
                'file_url' => $checklist && $checklist->file_path ? asset('storage/' . $checklist->file_path) : null,
            ];
        });

        // Determine general status
        // - approved if all required are approved
        // - pending if any is pending
        // - rejected if any is rejected
        $allApproved = $data->every(fn($item) => $item['status'] === 'approved');
        $anyRejected = $data->contains(fn($item) => $item['status'] === 'rejected');
        $overallStatus = $allApproved ? 'Memenuhi Syarat' : ($anyRejected ? 'Ada Syarat Ditolak' : 'Belum Memenuhi Syarat');

        return Inertia::render('Landing/Yudisium', [
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
            ],
            'requirements' => $data,
            'overallStatus' => $overallStatus,
        ]);
    }

    public function submitYudisium(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $request->validate([
            'requirement_id' => 'required|exists:yudisium_requirements,id',
            'file' => 'nullable|file|max:2048|mimes:pdf,jpg,jpeg,png',
        ]);

        $requirement = \App\Models\YudisiumRequirement::findOrFail($request->requirement_id);
        if ($requirement->is_upload_required && !$request->hasFile('file')) {
            return back()->withErrors(['file' => 'File wajib diunggah untuk syarat ini.']);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('yudisium', 'public');
        }

        $checklist = $mahasiswa->yudisiumChecklists()->updateOrCreate(
            ['yudisium_requirement_id' => $requirement->id],
            [
                'file_path' => $filePath,
                'status' => 'pending', // resets to pending if re-uploaded
                'catatan' => null,     // clear old notes
            ]
        );

        return back()->with('success', 'Persyaratan yudisium berhasil diunggah dan sedang menunggu validasi.');
    }
}
