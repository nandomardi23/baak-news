<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FileText, Upload, CheckCircle2, XCircle, ArrowLeft, Loader2, Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import LandingLayout from '@/layouts/LandingLayout.vue';

defineOptions({ layout: LandingLayout });

interface Requirement {
    id: number;
    nama_syarat: string;
    deskripsi: string | null;
    is_upload_required: boolean;
    status: string;
    status_label: string;
    status_badge: string;
    catatan: string | null;
    file_url: string | null;
}

const props = defineProps<{
    mahasiswa: { id: number; nim: string; nama: string };
    requirements: Requirement[];
    overallStatus: string;
}>();

const uploadForm = useForm({
    requirement_id: '',
    file: null as File | null,
});

const fileInputs = ref<Record<number, HTMLInputElement | null>>({});

const triggerUpload = (reqId: number) => {
    if (fileInputs.value[reqId]) {
        fileInputs.value[reqId]?.click();
    }
};

const handleFileSelect = (reqId: number, event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        uploadForm.requirement_id = String(reqId);
        uploadForm.file = target.files[0];
        
        uploadForm.post(`/yudisium/${props.mahasiswa.id}/submit`, {
            preserveScroll: true,
            onSuccess: () => {
                uploadForm.reset();
                if (fileInputs.value[reqId]) {
                    fileInputs.value[reqId]!.value = ''; // Reset input
                }
            }
        });
    }
};

const submitWithoutFile = (reqId: number) => {
    uploadForm.requirement_id = String(reqId);
    uploadForm.file = null;
    
    uploadForm.post(`/yudisium/${props.mahasiswa.id}/submit`, {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.reset();
        }
    });
};

const progress = computed(() => {
    if (props.requirements.length === 0) return 0;
    const completed = props.requirements.filter(r => r.status === 'approved').length;
    return Math.round((completed / props.requirements.length) * 100);
});
</script>

<template>
    <Head title="Checklist Yudisium" />

    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Link :href="`/dokumen/${mahasiswa.id}`" class="p-2 hover:bg-slate-100 rounded-lg transition-colors">
                <ArrowLeft class="w-5 h-5 text-slate-600" />
            </Link>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Checklist Yudisium</h1>
                <p class="text-slate-500 text-sm mt-1">Lengkapi persyaratan yudisium Anda</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <div class="flex items-center justify-between border-b pb-4 mb-4">
                <div>
                    <h2 class="font-semibold text-lg">{{ mahasiswa.nama }}</h2>
                    <p class="text-sm text-slate-500 font-mono">{{ mahasiswa.nim }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500 mb-1">Status Keseluruhan</p>
                    <span 
                        :class="[
                            'px-3 py-1 text-sm font-semibold rounded-full border',
                            overallStatus === 'Memenuhi Syarat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 
                            (overallStatus === 'Ada Syarat Ditolak' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200')
                        ]"
                    >
                        {{ overallStatus }}
                    </span>
                </div>
            </div>

            <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-slate-700">Progres Kelengkapan</span>
                    <span class="text-sm font-bold" :class="progress === 100 ? 'text-emerald-600' : 'text-blue-600'">{{ progress }}%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div 
                        class="h-2.5 rounded-full transition-all duration-1000 ease-out relative" 
                        :class="progress === 100 ? 'bg-emerald-500' : 'bg-blue-500'"
                        :style="{ width: `${progress}%` }"
                    >
                        <div class="absolute inset-0 bg-white/20" style="background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent); background-size: 1rem 1rem;"></div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-2 text-right">{{ requirements.filter(r => r.status === 'approved').length }} dari {{ requirements.length }} Syarat Disetujui</p>
            </div>

            <div class="space-y-4">
                <div v-for="req in requirements" :key="req.id" class="p-4 border rounded-xl hover:border-slate-300 transition-colors bg-slate-50/50">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-slate-800">{{ req.nama_syarat }}</h3>
                                <span 
                                    :class="[
                                        'px-2 py-0.5 text-xs font-bold uppercase tracking-wider rounded border',
                                        req.status === 'approved' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' :
                                        (req.status === 'rejected' ? 'bg-red-100 text-red-800 border-red-200' : 
                                        (req.status === 'pending' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-slate-200 text-slate-700 border-slate-300'))
                                    ]"
                                >
                                    {{ req.status_label }}
                                </span>
                            </div>
                            <p v-if="req.deskripsi" class="text-sm text-slate-600">{{ req.deskripsi }}</p>
                            
                            <div v-if="req.catatan && req.status === 'rejected'" class="mt-2 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-100 flex gap-2">
                                <XCircle class="w-4 h-4 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-semibold">Alasan Penolakan:</p>
                                    <p>{{ req.catatan }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 flex flex-col gap-2 sm:items-end mt-2 sm:mt-0">
                            <div v-if="req.status === 'approved'" class="flex items-center text-emerald-600 gap-1.5 font-medium">
                                <CheckCircle2 class="w-5 h-5" />
                                <span>Disetujui</span>
                            </div>
                            
                            <template v-else>
                                <input 
                                    type="file" 
                                    :ref="el => { if (el) fileInputs[req.id] = el as HTMLInputElement }" 
                                    class="hidden" 
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    @change="(e) => handleFileSelect(req.id, e)"
                                />
                                
                                <Button 
                                    v-if="req.is_upload_required || req.status === 'rejected'" 
                                    variant="outline" 
                                    size="sm"
                                    @click="triggerUpload(req.id)"
                                    :disabled="uploadForm.processing && uploadForm.requirement_id === String(req.id)"
                                    class="w-full sm:w-auto"
                                >
                                    <Loader2 v-if="uploadForm.processing && uploadForm.requirement_id === String(req.id)" class="w-4 h-4 mr-2 animate-spin" />
                                    <Upload v-else class="w-4 h-4 mr-2" />
                                    {{ req.status === 'rejected' ? 'Upload Ulang' : (req.file_url ? 'Ganti File' : 'Upload File') }}
                                </Button>

                                <!-- If upload is not required but still pending -->
                                <div v-else-if="!req.is_upload_required" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="submitWithoutFile(req.id)"
                                        :disabled="uploadForm.processing && uploadForm.requirement_id === String(req.id)"
                                        class="w-full sm:w-auto text-blue-600 border-blue-200 hover:bg-blue-50"
                                    >
                                        <Loader2 v-if="uploadForm.processing && uploadForm.requirement_id === String(req.id)" class="w-4 h-4 mr-2 animate-spin" />
                                        <CheckCircle2 v-else class="w-4 h-4 mr-2" />
                                        Ajukan Verifikasi
                                    </Button>
                                    
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="triggerUpload(req.id)"
                                        class="w-full sm:w-auto text-slate-500 hover:text-slate-700"
                                        title="Jika Anda memiliki file pendukung, silakan upload"
                                    >
                                        <Upload class="w-4 h-4 mr-2 sm:hidden" />
                                        <span class="sm:hidden">Upload Tambahan</span>
                                        <Upload class="w-4 h-4 hidden sm:block" />
                                    </Button>
                                </div>
                            </template>

                            <a v-if="req.file_url" :href="req.file_url" target="_blank" class="text-xs text-blue-600 hover:underline flex items-center justify-center sm:justify-end gap-1 mt-1">
                                <Download class="w-3 h-3" /> Lihat File Anda
                            </a>
                        </div>
                    </div>
                </div>
                
                <div v-if="requirements.length === 0" class="text-center py-12 bg-slate-50 rounded-xl border border-dashed">
                    <FileText class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <h3 class="text-lg font-medium text-slate-900 mb-1">Belum Ada Syarat</h3>
                    <p class="text-slate-500">Master syarat yudisium belum diatur oleh admin.</p>
                </div>
            </div>
        </div>
    </div>
</template>
