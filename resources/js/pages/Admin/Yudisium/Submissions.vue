<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { DataTable, TableHeader, TableRow, TableCell } from '@/components/ui/datatable';
import { Button } from '@/components/ui/button';
import { CheckCircle2, XCircle, Download, Check, X, FileText, Loader2 } from 'lucide-vue-next';
import { useStatusBadge } from '@/composables/useStatusBadge';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
const { getBadgeClass } = useStatusBadge();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Surat & Dokumen', href: '#' },
    { title: 'Checklist Yudisium', href: '/admin/yudisium/submissions' },
]);

const props = defineProps<{
    mahasiswa: any;
    filters: Record<string, any>;
}>();

const columns = [
    { key: 'mahasiswa', label: 'Mahasiswa', sortable: false },
    { key: 'progress', label: 'Progress', sortable: false },
    { key: 'status_keseluruhan', label: 'Status', sortable: false },
    { key: 'last_updated', label: 'Update Terakhir', sortable: false },
    { key: 'aksi', label: 'Aksi', align: 'center' as const },
];

const isReviewModalOpen = ref(false);
const isLoadingReview = ref(false);
const reviewData = ref<any>(null);

const openReview = async (mahasiswaId: number) => {
    isLoadingReview.value = true;
    isReviewModalOpen.value = true;
    reviewData.value = null;
    
    try {
        const response = await axios.get(`/admin/yudisium/submissions/${mahasiswaId}`);
        reviewData.value = response.data;
    } catch (e) {
        console.error("Gagal memuat detail mahasiswa", e);
    } finally {
        isLoadingReview.value = false;
    }
};

const closeReviewModal = () => {
    isReviewModalOpen.value = false;
    reviewData.value = null;
    router.reload({ only: ['mahasiswa'] }); // reload to update progress if any
};

// Modals inside Review
const approveModalOpen = ref(false);
const rejectModalOpen = ref(false);
const selectedChecklist = ref<any>(null);

const rejectForm = useForm({
    catatan: '',
});

const openApproveModal = (req: any) => {
    selectedChecklist.value = req;
    approveModalOpen.value = true;
};

const openRejectModal = (req: any) => {
    selectedChecklist.value = req;
    rejectForm.catatan = '';
    rejectForm.clearErrors();
    rejectModalOpen.value = true;
};

const closeActionModals = () => {
    approveModalOpen.value = false;
    rejectModalOpen.value = false;
    selectedChecklist.value = null;
    rejectForm.reset();
};

const isApproving = ref(false);
const isRejecting = ref(false);

const submitApprove = async () => {
    if (!selectedChecklist.value || !selectedChecklist.value.checklist_id) {
        alert("Data syarat tidak valid atau belum diupload oleh mahasiswa.");
        return;
    }
    
    isApproving.value = true;
    try {
        await axios.post(`/admin/yudisium/submissions/${selectedChecklist.value.checklist_id}/approve`);
        closeActionModals();
        openReview(reviewData.value.mahasiswa.id);
    } catch (e) {
        console.error(e);
        alert("Terjadi kesalahan saat menyetujui syarat.");
    } finally {
        isApproving.value = false;
    }
};

const submitReject = async () => {
    if (!selectedChecklist.value || !selectedChecklist.value.checklist_id) {
        alert("Data syarat tidak valid atau belum diupload oleh mahasiswa.");
        return;
    }
    
    rejectForm.clearErrors();
    if (!rejectForm.catatan) {
        rejectForm.setError('catatan', 'Alasan penolakan wajib diisi');
        return;
    }

    isRejecting.value = true;
    try {
        await axios.post(`/admin/yudisium/submissions/${selectedChecklist.value.checklist_id}/reject`, {
            catatan: rejectForm.catatan
        });
        closeActionModals();
        openReview(reviewData.value.mahasiswa.id);
    } catch (e: any) {
        console.error(e);
        if (e.response && e.response.status === 422) {
            const errors = e.response.data.errors;
            if (errors.catatan) rejectForm.setError('catatan', errors.catatan[0]);
        } else {
            alert("Terjadi kesalahan saat menolak syarat.");
        }
    } finally {
        isRejecting.value = false;
    }
};
</script>

<template>
    <Head title="Checklist Yudisium" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Checklist Yudisium</h1>
                <p class="text-muted-foreground">Daftar mahasiswa yang mengajukan proses yudisium</p>
            </div>
        </div>

        <SmartTable
            :data="mahasiswa"
            :columns="columns"
            :search="filters.search"
            :sort-field="filters.sort_field"
            :sort-direction="filters.sort_direction"
            title="Daftar Pengajuan"
        >
            <template #cell-mahasiswa="{ row }">
                <div class="flex flex-col py-1">
                    <div class="font-semibold text-sm text-slate-900 mb-0.5">{{ row.nama }}</div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-slate-600">{{ row.nim }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-slate-500">{{ row.prodi }}</span>
                    </div>
                </div>
            </template>

            <template #cell-progress="{ row }">
                <div class="flex flex-col gap-1 w-full max-w-50 py-1">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ row.approved_count }} / {{ row.total_requirements }} Disetujui</span>
                        <span class="font-bold" :class="row.progress === 100 ? 'text-emerald-600' : 'text-blue-600'">{{ row.progress }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div 
                            class="h-1.5 rounded-full transition-all" 
                            :class="row.progress === 100 ? 'bg-emerald-500' : 'bg-blue-500'"
                            :style="{ width: `${row.progress}%` }"
                        ></div>
                    </div>
                </div>
            </template>

            <template #cell-status_keseluruhan="{ row }">
                <span 
                    :class="[
                        'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border shadow-sm',
                        row.status_keseluruhan === 'Memenuhi Syarat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                        (row.status_keseluruhan === 'Ada Syarat Ditolak' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200')
                    ]"
                >
                    {{ row.status_keseluruhan }}
                </span>
            </template>
            
            <template #cell-last_updated="{ row }">
                <span class="text-sm text-slate-600">{{ row.last_updated || '-' }}</span>
            </template>

            <template #cell-aksi="{ row }">
                <div class="flex justify-center">
                    <Button variant="outline" size="sm" class="text-blue-600 border-blue-200 hover:bg-blue-50" @click="openReview(row.id)">
                        Review
                    </Button>
                </div>
            </template>
        </SmartTable>
    </div>

    <!-- Review Modal -->
    <div v-if="isReviewModalOpen" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-40 p-4 md:p-8">
        <div class="bg-slate-50 rounded-2xl shadow-2xl w-full max-w-5xl max-h-full overflow-hidden flex flex-col border border-slate-200">
            <!-- Modal Header -->
            <div class="p-4 md:p-6 bg-white border-b flex items-center justify-between shrink-0">
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-slate-900">Review Syarat Yudisium</h2>
                    <p class="text-sm text-slate-500" v-if="reviewData">Mahasiswa: <span class="font-semibold text-slate-700">{{ reviewData.mahasiswa.nama }}</span></p>
                </div>
                <button @click="closeReviewModal" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6">
                <div v-if="isLoadingReview" class="flex flex-col items-center justify-center py-20 text-slate-500">
                    <Loader2 class="w-10 h-10 animate-spin text-blue-500 mb-4" />
                    <p class="font-medium">Memuat detail pengajuan...</p>
                </div>
                
                <div v-else-if="reviewData" class="space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div>
                            <h2 class="font-bold text-lg text-slate-900 mb-1">{{ reviewData.mahasiswa.nama }}</h2>
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <span class="font-mono bg-slate-100 px-2 py-0.5 rounded border border-slate-200 text-slate-700">{{ reviewData.mahasiswa.nim }}</span>
                                <span class="text-slate-300">•</span>
                                <span>{{ reviewData.mahasiswa.prodi }}</span>
                            </div>
                        </div>
                        
                        <div class="text-left md:text-right flex flex-col md:items-end min-w-50">
                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Status Keseluruhan</p>
                            <span 
                                :class="[
                                    'px-3 py-1 text-sm font-semibold rounded-full border shadow-sm',
                                    reviewData.mahasiswa.overallStatus === 'Memenuhi Syarat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                                    (reviewData.mahasiswa.overallStatus === 'Ada Syarat Ditolak' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200')
                                ]"
                            >
                                {{ reviewData.mahasiswa.overallStatus }}
                            </span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-semibold text-slate-700">Progres Kelengkapan ({{ reviewData.mahasiswa.approved_count }} dari {{ reviewData.mahasiswa.total_requirements }} Disetujui)</span>
                            <span class="text-sm font-bold" :class="reviewData.mahasiswa.progress === 100 ? 'text-emerald-600' : 'text-blue-600'">{{ reviewData.mahasiswa.progress }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                            <div 
                                class="h-3 rounded-full transition-all duration-1000 ease-out relative" 
                                :class="reviewData.mahasiswa.progress === 100 ? 'bg-emerald-500' : 'bg-blue-500'"
                                :style="{ width: `${reviewData.mahasiswa.progress}%` }"
                            >
                                <div class="absolute inset-0 bg-white/20" style="background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent); background-size: 1rem 1rem;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                    <DataTable class="mt-6 border shadow-sm rounded-xl">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <TableHeader>Nama Syarat</TableHeader>
                                <TableHeader class="w-30 text-center">Wajib Upload</TableHeader>
                                <TableHeader class="w-37.5 text-center">Status</TableHeader>
                                <TableHeader class="w-45 text-center">Dokumen</TableHeader>
                                <TableHeader class="w-50 text-center">Aksi</TableHeader>
                            </tr>
                        </thead>
                        <tbody>
                            <TableRow v-for="req in reviewData.requirements" :key="req.requirement_id" class="hover:bg-slate-50/50">
                                <TableCell>
                                    <h3 class="font-bold text-slate-800 text-[13px]">{{ req.nama_syarat }}</h3>
                                    <p v-if="req.deskripsi" class="text-[11px] text-slate-500 mt-1 line-clamp-2" :title="req.deskripsi">{{ req.deskripsi }}</p>
                                    
                                    <div v-if="req.catatan && req.status === 'rejected'" class="mt-2 p-2 bg-red-50 text-red-700 text-[11px] rounded border border-red-100 flex gap-1.5 items-start">
                                        <XCircle class="w-3.5 h-3.5 shrink-0 mt-0.5" />
                                        <span><strong>Ditolak:</strong> {{ req.catatan }}</span>
                                    </div>
                                    <div v-if="req.processed_by" class="mt-1.5 text-[10px] text-slate-400">
                                        Diproses: {{ req.processed_by }} ({{ req.processed_at }})
                                    </div>
                                </TableCell>
                                
                                <TableCell class="text-center">
                                    <span v-if="req.is_upload_required" class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded border">Ya</span>
                                    <span v-else class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100">Opsional</span>
                                </TableCell>
                                
                                <TableCell class="text-center">
                                    <span 
                                        :class="getBadgeClass(req.status_badge)" 
                                        class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border shadow-sm whitespace-nowrap"
                                    >
                                        {{ req.status_label }}
                                    </span>
                                </TableCell>
                                
                                <TableCell class="text-center">
                                    <a v-if="req.file_url" :href="req.file_url" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 border text-slate-700 rounded-md text-[11px] font-semibold shadow-sm transition-colors w-full">
                                        <Download class="w-3.5 h-3.5 text-blue-600" /> Unduh
                                    </a>
                                    <div v-else class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-slate-50 border border-dashed text-slate-400 rounded-md text-[11px] italic w-full">
                                        -
                                    </div>
                                </TableCell>
                                
                                <TableCell class="text-center">
                                    <div v-if="req.status === 'pending'" class="flex items-center gap-1.5 justify-center">
                                        <Button
                                            variant="outline"
                                            class="h-7 px-2.5 text-emerald-600 border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 text-[11px]"
                                            @click="openApproveModal(req)"
                                        >
                                            <Check class="w-3.5 h-3.5 mr-1" /> Setuju
                                        </Button>
                                        <Button
                                            variant="outline"
                                            class="h-7 px-2.5 text-red-600 border-red-200 hover:bg-red-50 hover:text-red-700 text-[11px]"
                                            @click="openRejectModal(req)"
                                        >
                                            <X class="w-3.5 h-3.5 mr-1" /> Tolak
                                        </Button>
                                    </div>
                                    <div v-else-if="req.status === 'approved'" class="flex items-center justify-center text-emerald-600 gap-1 font-semibold text-[11px]">
                                        <CheckCircle2 class="w-4 h-4" /> Selesai
                                    </div>
                                    <div v-else-if="req.status === 'rejected'" class="flex justify-center">
                                        <Button
                                            variant="outline"
                                            class="h-7 px-2.5 text-emerald-600 border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 text-[11px]"
                                            @click="openApproveModal(req)"
                                        >
                                            Ubah Setuju
                                        </Button>
                                    </div>
                                    <div v-else class="text-[11px] text-slate-400 italic">
                                        Menunggu
                                    </div>
                                </TableCell>
                            </TableRow>
                        </tbody>
                    </DataTable>
                        
                        <div v-if="reviewData.requirements.length === 0" class="text-center py-12 bg-slate-50 rounded-xl border border-dashed">
                            <FileText class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                            <h3 class="text-lg font-medium text-slate-900 mb-1">Belum Ada Syarat</h3>
                            <p class="text-slate-500">Master syarat yudisium belum diatur.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t flex justify-end shrink-0 rounded-b-2xl">
                <Button variant="outline" @click="closeReviewModal">Tutup</Button>
            </div>
        </div>
    </div>

    <!-- Approve Modal (Action) -->
    <div v-if="approveModalOpen && selectedChecklist" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
            <div class="p-5 border-b flex items-center gap-3 bg-emerald-50/50 text-emerald-700">
                <div class="p-2 bg-emerald-100 text-emerald-600 rounded-full">
                    <Check class="w-5 h-5" />
                </div>
                <h2 class="text-lg font-semibold">Konfirmasi Persetujuan</h2>
            </div>
            <div class="p-6 space-y-4 text-sm text-slate-600">
                <p>Anda yakin ingin menyetujui syarat <strong>{{ selectedChecklist.nama_syarat }}</strong> untuk mahasiswa <strong>{{ reviewData?.mahasiswa?.nama }}</strong>?</p>
                <div class="flex justify-end gap-2 pt-4 border-t mt-6">
                    <Button variant="outline" @click="closeActionModals" :disabled="isApproving">Batal</Button>
                    <Button class="bg-emerald-600 hover:bg-emerald-700 text-white" @click="submitApprove" :disabled="isApproving">
                        <Loader2 v-if="isApproving" class="w-4 h-4 mr-2 animate-spin" />
                        {{ isApproving ? 'Memproses...' : 'Setujui Syarat' }}
                    </Button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal (Action) -->
    <div v-if="rejectModalOpen && selectedChecklist" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
            <div class="p-5 border-b flex items-center justify-between bg-red-50/50 text-red-700">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 text-red-600 rounded-full">
                        <X class="w-5 h-5" />
                    </div>
                    <h2 class="text-lg font-semibold">Tolak Persyaratan</h2>
                </div>
                <button @click="closeActionModals" class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-1.5 rounded-lg transition"><X class="w-5 h-5" /></button>
            </div>
            <form @submit.prevent="submitReject" class="p-6 space-y-4">
                <p class="text-sm text-slate-600 mb-4">Anda akan menolak pengajuan <strong>{{ selectedChecklist.nama_syarat }}</strong> untuk mahasiswa <strong>{{ reviewData?.mahasiswa?.nama }}</strong>.</p>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea
                        v-model="rejectForm.catatan"
                        rows="3"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition resize-none text-sm"
                        placeholder="Berikan alasan spesifik (misal: File buram, bukan bukti SPP)..."
                        required
                    ></textarea>
                    <p v-if="rejectForm.errors.catatan" class="text-red-500 text-sm mt-1">{{ rejectForm.errors.catatan }}</p>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t mt-6">
                    <Button type="button" variant="outline" @click="closeActionModals" :disabled="isRejecting">Batal</Button>
                    <Button type="submit" variant="destructive" :disabled="isRejecting">
                        <Loader2 v-if="isRejecting" class="w-4 h-4 mr-2 animate-spin" />
                        {{ isRejecting ? 'Memproses...' : 'Tolak Syarat' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
