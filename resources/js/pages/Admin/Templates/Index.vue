<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { FileText, Award, GraduationCap, FileCheck, Upload, Trash2, Clock, Replace, Loader2 } from 'lucide-vue-next';

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

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Template Designer', href: '/admin/templates' },
]);

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

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Template Dokumen" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Template Dokumen</h1>
            <p class="text-slate-500">Kelola template PDF untuk berbagai dokumen akademik. Upload file PDF kosong sebagai background.</p>
        </div>

        <!-- Table -->
        <div class="rounded-xl border bg-card shadow-sm">
            <Table>
                <TableHeader>
                    <TableRow class="bg-slate-50/50">
                        <TableHead class="w-12 text-center">#</TableHead>
                        <TableHead>Nama Template</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Detail</TableHead>
                        <TableHead>Terakhir Update</TableHead>
                        <TableHead class="text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="(cat, index) in categories" :key="cat.id" class="hover:bg-slate-50/50">
                        <TableCell class="text-center text-muted-foreground font-medium">
                            {{ index + 1 }}
                        </TableCell>
                        <TableCell>
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg" :class="getTemplateByType(cat.id) ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400'">
                                    <component :is="cat.icon" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-800">{{ cat.name }}</div>
                                    <div class="text-xs text-slate-500">{{ cat.description }}</div>
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            <Badge
                                v-if="getTemplateByType(cat.id)"
                                class="bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-50 font-semibold text-[10px] uppercase"
                            >
                                Aktif
                            </Badge>
                            <Badge
                                v-else
                                variant="outline"
                                class="bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-50 font-semibold text-[10px] uppercase"
                            >
                                Belum Ada
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <template v-if="getTemplateByType(cat.id)">
                                <span class="text-xs text-slate-600 font-medium">
                                    {{ getTemplateByType(cat.id)!.page_size }} • {{ getTemplateByType(cat.id)!.orientation }}
                                </span>
                            </template>
                            <span v-else class="text-xs text-slate-400 italic">—</span>
                        </TableCell>
                        <TableCell>
                            <template v-if="getTemplateByType(cat.id)">
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    <Clock class="w-3.5 h-3.5" />
                                    {{ formatDate(getTemplateByType(cat.id)!.updated_at) }}
                                </div>
                            </template>
                            <span v-else class="text-xs text-slate-400 italic">—</span>
                        </TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button
                                    v-if="getTemplateByType(cat.id)"
                                    variant="ghost"
                                    size="icon"
                                    class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8"
                                    title="Hapus Template"
                                    @click="deleteTemplate(getTemplateByType(cat.id)!.id)"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 text-xs font-medium"
                                    :class="getTemplateByType(cat.id) ? 'text-slate-600 hover:bg-slate-100' : 'text-blue-600 hover:bg-blue-50'"
                                    @click="openUploadModal(cat.id)"
                                >
                                    <component :is="getTemplateByType(cat.id) ? Replace : Upload" class="w-3.5 h-3.5 mr-1.5" />
                                    {{ getTemplateByType(cat.id) ? 'Ganti' : 'Upload' }}
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
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
                                    <div class="w-10 h-10 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mb-2">
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
</template>
