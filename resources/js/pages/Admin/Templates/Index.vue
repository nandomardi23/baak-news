<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { FileText, Award, GraduationCap, FileCheck, Upload, Trash2, Clock, AlertCircle } from 'lucide-vue-next';

interface Template {
    id: number;
    name: string;
    slug: string;
    type: string;
    description: string | null;
    page_size: string;
    orientation: string;
    is_active: boolean;
    updated_at: string;
}

const props = defineProps<{
    templates: Template[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Template Designer', href: '/admin/templates' },
];

const categories = [
    { id: 'surat', name: 'Surat Keterangan Aktif', description: 'Template untuk surat keterangan mahasiswa aktif', icon: FileText },
    { id: 'krs', name: 'Kartu Rencana Studi (KRS)', description: 'Template untuk cetak KRS mahasiswa', icon: FileCheck },
    { id: 'kartu_ujian', name: 'Kartu Ujian', description: 'Template untuk cetak kartu ujian mahasiswa', icon: FileText },
    { id: 'khs', name: 'Kartu Hasil Studi (KHS)', description: 'Template untuk cetak KHS per semester', icon: Award },
    { id: 'transkrip', name: 'Transkrip Nilai', description: 'Template untuk cetak transkrip akademik', icon: GraduationCap },
];

const getTemplateByType = (type: string) => {
    return props.templates.find(t => t.type === type);
};

const showUploadModal = ref(false);
const isDragging = ref(false);
const selectedFile = ref<File | null>(null);

const form = useForm({
    name: '',
    type: 'surat',
    template_file: null as File | null,
});

const openUploadModal = (type: string) => {
    form.type = type;
    // Auto-generate name based on type
    const category = categories.find(c => c.id === type);
    form.name = category ? category.name : '';
    showUploadModal.value = true;
};

const deleteTemplate = (id: number) => {
    if (confirm('Hapus template ini? Dokumen tidak akan bisa dicetak sampai template baru diupload.')) {
        router.delete(`/admin/templates/${id}`);
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    const file = e.dataTransfer?.files[0];
    if (file && file.type === 'application/pdf') {
        selectedFile.value = file;
        form.template_file = file;
    }
};

const handleFileSelect = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        selectedFile.value = file;
        form.template_file = file;
    }
};

const submitUpload = () => {
    if (!form.template_file) return;
    
    form.post('/admin/templates/pdf-upload', {
        forceFormData: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const closeModal = () => {
    showUploadModal.value = false;
    selectedFile.value = null;
    form.reset();
};

const triggerFileInput = () => {
    document.getElementById('fileInput')?.click();
};
</script>

<template>
    <Head title="Template Designer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 lg:p-10 max-w-7xl mx-auto w-full">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Template Dokumen</h1>
                <p class="text-slate-500">Kelola template PDF untuk berbagai dokumen akademik. Upload file PDF kosong sebagai background.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-6 mt-4">
                <div v-for="cat in categories" :key="cat.id" 
                    class="group relative flex flex-col justify-between rounded-xl border bg-card p-6 shadow-sm transition-all hover:shadow-md"
                    :class="getTemplateByType(cat.id) ? 'border-slate-200' : 'border-dashed border-slate-300 bg-slate-50/50'"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-lg" :class="getTemplateByType(cat.id) ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400'">
                                <component :is="cat.icon" class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900" :class="{'text-slate-500': !getTemplateByType(cat.id)}">
                                    {{ cat.name }}
                                </h3>
                                <p class="text-xs text-slate-500 mt-1 max-w-[200px]">{{ cat.description }}</p>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <div v-if="getTemplateByType(cat.id)" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase tracking-wide">
                            Digunakan
                        </div>
                        <div v-else class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-100 uppercase tracking-wide">
                            Belum Ada
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="mt-6">
                        <div v-if="getTemplateByType(cat.id)" class="bg-slate-50 rounded-lg p-4 border border-slate-100">
                            <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                                <span class="flex items-center gap-1.5">
                                    <Clock class="w-3.5 h-3.5" />
                                    Updated: {{ new Date(getTemplateByType(cat.id)!.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                </span>
                                <span>{{ getTemplateByType(cat.id)!.page_size }} • {{ getTemplateByType(cat.id)!.orientation }}</span>
                            </div>
                            <div class="text-sm font-medium text-slate-700 truncate">
                                {{ getTemplateByType(cat.id)!.name }}
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-6 text-center text-slate-400 border-2 border-dashed border-slate-200 rounded-lg">
                            <Upload class="w-8 h-8 mb-2 opacity-50" />
                            <span class="text-xs font-medium">Belum ada template yang diupload</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <Button 
                            v-if="getTemplateByType(cat.id)"
                            variant="ghost" 
                            size="sm"
                            class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 text-xs"
                            @click="deleteTemplate(getTemplateByType(cat.id)!.id)"
                        >
                            <Trash2 class="w-3.5 h-3.5 mr-1.5" />
                            Hapus
                        </Button>
                        
                        <Button 
                            :variant="getTemplateByType(cat.id) ? 'outline' : 'default'"
                            size="sm"
                            @click="openUploadModal(cat.id)"
                            class="h-8 text-xs font-medium"
                            :class="!getTemplateByType(cat.id) ? 'bg-primary hover:bg-primary/90' : ''"
                        >
                            <Upload class="w-3.5 h-3.5 mr-1.5" />
                            {{ getTemplateByType(cat.id) ? 'Ganti File' : 'Upload Template' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div v-if="showUploadModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-xl w-full max-w-md border border-slate-200 dark:border-slate-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <h3 class="font-semibold text-lg">Upload Template {{ categories.find(c => c.id === form.type)?.name }}</h3>
                            <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                                <span class="sr-only">Close</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium text-slate-700">Nama Template</label>
                                    <input 
                                        v-model="form.name"
                                        type="text" 
                                        class="w-full px-3 py-2 text-sm border rounded-md focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition"
                                    />
                                </div>

                                <!-- Drop Zone -->
                                <div
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    :class="[
                                        isDragging ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-slate-300 hover:border-slate-400',
                                        selectedFile ? 'bg-emerald-50 border-emerald-200' : ''
                                    ]"
                                    class="border-2 border-dashed rounded-xl p-8 text-center transition-all cursor-pointer relative"
                                    @click="triggerFileInput"
                                >
                                    <input id="fileInput" type="file" accept=".pdf" @change="handleFileSelect" class="hidden" />
                                    
                                    <div v-if="selectedFile" class="flex flex-col items-center">
                                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-2">
                                            <FileCheck class="w-5 h-5" />
                                        </div>
                                        <p class="font-medium text-sm text-emerald-900">{{ selectedFile.name }}</p>
                                        <p class="text-xs text-emerald-600 mt-1">{{ (selectedFile.size / 1024).toFixed(0) }} KB</p>
                                        <p class="text-xs text-emerald-600 mt-3 font-medium">Klik untuk ganti file</p>
                                    </div>
                                    <div v-else class="flex flex-col items-center">
                                        <div class="w-10 h-10 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-2 group-hover:bg-blue-50 group-hover:text-blue-500 transition-colors">
                                            <Upload class="w-5 h-5" />
                                        </div>
                                        <p class="font-medium text-sm text-slate-700">Klik untuk upload file PDF</p>
                                        <p class="text-xs text-slate-400 mt-1">atau drag & drop file ke sini</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 flex items-center justify-end gap-3 border-t border-slate-100">
                            <Button variant="ghost" @click="closeModal" type="button">Batal</Button>
                            <Button 
                                :disabled="!selectedFile || form.processing" 
                                @click="submitUpload"
                                class="bg-primary text-primary-foreground hover:bg-primary/90"
                            >
                                <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                                {{ form.processing ? 'Uploading...' : 'Simpan Template' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
