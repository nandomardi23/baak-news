<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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
    { id: 'surat', name: 'Surat Keterangan', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { id: 'krs', name: 'KRS', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { id: 'khs', name: 'KHS', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { id: 'transkrip', name: 'Transkrip', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
];

const activeTab = ref('surat');

const filteredTemplates = computed(() => {
    return props.templates.filter(t => t.type === activeTab.value);
});

const showUploadModal = ref(false);
const isDragging = ref(false);
const selectedFile = ref<File | null>(null);

const form = useForm({
    name: '',
    type: 'surat',
    template_file: null as File | null,
});

const deleteTemplate = (id: number) => {
    if (confirm('Hapus template ini?')) {
        router.delete(`/admin/templates/${id}`);
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    const file = e.dataTransfer?.files[0];
    if (file && file.type === 'application/pdf') {
        selectedFile.value = file;
        form.template_file = file;
        if (!form.name) form.name = file.name.replace('.pdf', '');
    }
};

const handleFileSelect = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        selectedFile.value = file;
        form.template_file = file;
        if (!form.name) form.name = file.name.replace('.pdf', '');
    }
};

const submitUpload = () => {
    if (!form.template_file) return;
    
    form.post('/admin/templates/pdf-upload', {
        forceFormData: true,
        onSuccess: () => {
            showUploadModal.value = false;
            selectedFile.value = null;
            form.reset();
        },
    });
};

const closeModal = () => {
    showUploadModal.value = false;
    selectedFile.value = null;
    form.reset();
};
</script>

<template>
    <Head title="Template Designer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Template Designer</h1>
                    <p class="text-muted-foreground">Kelola template PDF untuk surat dan dokumen akademik</p>
                </div>
                <button
                    @click="showUploadModal = true"
                    class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Template Baru
                </button>
            </div>

            <!-- Tabs Selection -->
            <div class="flex border-b border-slate-200 dark:border-slate-800">
                <button
                    v-for="cat in categories"
                    :key="cat.id"
                    @click="activeTab = cat.id"
                    :class="[
                        'flex items-center gap-2 px-6 py-3 text-sm font-medium transition-colors border-b-2',
                        activeTab === cat.id 
                            ? 'border-primary text-primary' 
                            : 'border-transparent text-muted-foreground hover:text-foreground'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="cat.icon" />
                    </svg>
                    {{ cat.name }}
                </button>
            </div>

            <!-- Templates Table -->
            <div v-if="filteredTemplates.length > 0" class="rounded-xl border bg-card overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-muted/50 border-b">
                            <th class="px-6 py-4 text-sm font-semibold">Nama Template</th>
                            <th class="px-6 py-4 text-sm font-semibold">Tipe</th>
                            <th class="px-6 py-4 text-sm font-semibold">Ukuran</th>
                            <th class="px-6 py-4 text-sm font-semibold">Update Terakhir</th>
                            <th class="px-6 py-4 text-sm font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="template in filteredTemplates" :key="template.id" class="hover:bg-muted/20 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium">{{ template.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600">
                                    {{ template.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-muted-foreground">
                                {{ template.page_size }} • {{ template.orientation }}
                            </td>
                            <td class="px-6 py-4 text-sm text-muted-foreground">
                                {{ new Date(template.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    @click="deleteTemplate(template.id)"
                                    class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                    title="Hapus"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Empty state -->
            <div v-else class="rounded-xl border bg-card p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="categories.find(c => c.id === activeTab)?.icon" />
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-semibold">Belum ada template {{ categories.find(c => c.id === activeTab)?.name }}</h3>
                <p class="mt-2 text-muted-foreground text-sm">Upload template PDF untuk memulai</p>
                <button
                    @click="showUploadModal = true"
                    class="inline-flex items-center mt-6 px-6 py-3 bg-primary text-primary-foreground font-semibold rounded-xl hover:bg-primary/90 transition shadow-lg"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload PDF Template
                </button>
            </div>
        </div>

        <!-- Upload Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showUploadModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div class="bg-card rounded-2xl p-6 w-full max-w-lg shadow-2xl border">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold">Upload Template Baru</h3>
                            <button @click="closeModal" class="p-2 hover:bg-muted rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Name & Type -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium">Nama Template</label>
                                    <input 
                                        v-model="form.name"
                                        type="text" 
                                        placeholder="Contoh: KRS Semester Ganjil"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition"
                                    />
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-sm font-medium">Tipe Dokumen</label>
                                    <select 
                                        v-model="form.type"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition bg-white dark:bg-slate-900"
                                    >
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                            {{ cat.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Drop Zone -->
                            <div
                                @dragover.prevent="isDragging = true"
                                @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop"
                                :class="[isDragging ? 'border-primary bg-primary/5' : 'border-dashed border-slate-300 dark:border-slate-600']"
                                class="border-2 rounded-xl p-8 text-center transition-colors"
                            >
                                <div v-if="selectedFile" class="space-y-3">
                                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="font-medium">{{ selectedFile.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ (selectedFile.size / 1024).toFixed(1) }} KB</p>
                                    <button @click="selectedFile = null; form.template_file = null" class="text-sm text-red-600 hover:underline">
                                        Hapus & ganti file
                                    </button>
                                </div>
                                <div v-else class="space-y-3">
                                    <svg class="w-10 h-10 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-muted-foreground text-sm">Drag & drop file PDF atau</p>
                                    <label class="inline-flex items-center px-4 py-2 bg-slate-100 dark:bg-slate-800 text-sm font-medium rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer transition">
                                        Pilih File
                                        <input type="file" accept=".pdf" @change="handleFileSelect" class="hidden" />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 mt-8">
                            <button @click="closeModal" class="px-4 py-2 border rounded-lg hover:bg-muted transition font-medium">
                                Batal
                            </button>
                            <button
                                @click="submitUpload"
                                :disabled="!selectedFile || !form.name || form.processing"
                                class="px-6 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition font-medium disabled:opacity-50"
                            >
                                <span v-if="form.processing">Mengevaluasi...</span>
                                <span v-else>Simpan Template</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
