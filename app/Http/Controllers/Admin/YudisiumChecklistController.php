<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MahasiswaYudisiumChecklist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class YudisiumChecklistController extends Controller
{
    use \App\Traits\HasDataTable;

    public function index(Request $request): Response
    {
        $allRequirements = \App\Models\YudisiumRequirement::active()->get();
        $globalReqCount = $allRequirements->whereNull('program_studi_id')->count();
        $prodiReqCount = $allRequirements->whereNotNull('program_studi_id')->groupBy('program_studi_id')->map->count();

        // Query Mahasiswa who have at least one checklist
        $query = \App\Models\Mahasiswa::with(['programStudi', 'yudisiumChecklists'])
            ->whereHas('yudisiumChecklists')
            ->orderBy(
                \App\Models\MahasiswaYudisiumChecklist::select('updated_at')
                    ->whereColumn('mahasiswa_id', 'mahasiswa.id')
                    ->latest()
                    ->take(1),
                'desc'
            ); // Order by latest checklist update

        // Apply standardized Search and Sort
        $mahasiswa = $this->applyDataTable($query, $request, ['nama', 'nim'], 20);

        $mahasiswa->through(function ($mhs) use ($globalReqCount, $prodiReqCount) {
            $totalRequirements = $globalReqCount + ($prodiReqCount->get($mhs->program_studi_id) ?? 0);
            
            $checklists = $mhs->yudisiumChecklists;
            $approvedCount = $checklists->where('status', 'approved')->count();
            
            $progress = $totalRequirements > 0 
                ? round(($approvedCount / $totalRequirements) * 100) 
                : 0;

            $anyRejected = $checklists->contains('status', 'rejected');
            $overallStatus = 'Menunggu Validasi';
            if ($anyRejected) {
                $overallStatus = 'Ada Syarat Ditolak';
            } elseif ($approvedCount === $totalRequirements && $totalRequirements > 0) {
                $overallStatus = 'Memenuhi Syarat';
            }

            return [
                'id' => $mhs->id,
                'nim' => $mhs->nim,
                'nama' => $mhs->nama,
                'prodi' => $mhs->programStudi?->nama_prodi,
                'progress' => $progress,
                'approved_count' => $approvedCount,
                'total_requirements' => $totalRequirements,
                'status_keseluruhan' => $overallStatus,
                'last_updated' => $checklists->max('updated_at')?->format('d M Y H:i'),
            ];
        });

        return Inertia::render('Admin/Yudisium/Submissions', [
            'mahasiswa' => $mahasiswa,
            'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
        ]);
    }

    public function show(\App\Models\Mahasiswa $mahasiswa)
    {
        $mahasiswa->load('programStudi');
        $requirements = \App\Models\YudisiumRequirement::active()
            ->where(function ($q) use ($mahasiswa) {
                $q->whereNull('program_studi_id')
                  ->orWhere('program_studi_id', $mahasiswa->program_studi_id);
            })->get();
        $checklists = $mahasiswa->yudisiumChecklists()->with('processedBy')->get()->keyBy('yudisium_requirement_id');

        $data = $requirements->map(function ($req) use ($checklists) {
            $checklist = $checklists->get($req->id);
            return [
                'checklist_id' => $checklist?->id,
                'requirement_id' => $req->id,
                'nama_syarat' => $req->nama_syarat,
                'deskripsi' => $req->deskripsi,
                'is_upload_required' => $req->is_upload_required,
                'status' => $checklist ? $checklist->status : 'belum_ada',
                'status_label' => $checklist ? $checklist->status_label : 'Belum Ada',
                'status_badge' => $checklist ? $checklist->status_badge : 'belum_ada',
                'catatan' => $checklist ? $checklist->catatan : null,
                'file_url' => $checklist && $checklist->file_path ? asset('storage/' . $checklist->file_path) : null,
                'processed_by' => $checklist?->processedBy?->name,
                'processed_at' => $checklist?->processed_at?->format('d M Y H:i'),
                'updated_at' => $checklist?->updated_at?->format('d M Y H:i'),
            ];
        });

        $totalRequirements = $requirements->count();
        $approvedCount = $data->where('status', 'approved')->count();
        $progress = $totalRequirements > 0 ? round(($approvedCount / $totalRequirements) * 100) : 0;
        
        $anyRejected = $data->contains('status', 'rejected');
        $overallStatus = $approvedCount === $totalRequirements && $totalRequirements > 0 
            ? 'Memenuhi Syarat' 
            : ($anyRejected ? 'Ada Syarat Ditolak' : 'Belum Memenuhi Syarat');

        return response()->json([
            'mahasiswa' => [
                'id' => $mahasiswa->id,
                'nim' => $mahasiswa->nim,
                'nama' => $mahasiswa->nama,
                'prodi' => $mahasiswa->programStudi?->nama_prodi,
                'progress' => $progress,
                'approved_count' => $approvedCount,
                'total_requirements' => $totalRequirements,
                'overallStatus' => $overallStatus,
            ],
            'requirements' => $data,
        ]);
    }

    public function approve(MahasiswaYudisiumChecklist $checklist): RedirectResponse
    {
        $checklist->update([
            'status' => 'approved',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        ActivityLog::log('approved', "Menyetujui syarat yudisium '{$checklist->requirement->nama_syarat}' untuk {$checklist->mahasiswa->nama}", $checklist);

        return back()->with('success', 'Syarat berhasil disetujui');
    }

    public function reject(Request $request, MahasiswaYudisiumChecklist $checklist): RedirectResponse
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        $checklist->update([
            'status' => 'rejected',
            'catatan' => $request->catatan,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        ActivityLog::log('rejected', "Menolak syarat yudisium '{$checklist->requirement->nama_syarat}' untuk {$checklist->mahasiswa->nama}", $checklist);

        return back()->with('success', 'Syarat ditolak');
    }
}
