<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Pencil, Trash2, Plus, Download } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

defineOptions({ layout: AppLayout });

const { setBreadcrumbs } = useBreadcrumbs();
setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Template Dokumen', href: '/admin/dokumen-template' },
]);

interface DokumenTemplate {
    id: number;
    nama: string;
    deskripsi: string | null;
    kategori: string;
    file_path: string;
    file_type: string;
    ukuran_format: string;
    file_url: string;
    created_at: string;
}

const props = defineProps<{
    templates: any;
}>();

const columns = [
    { key: 'nama', label: 'Nama Template', sortable: true },
    { key: 'kategori', label: 'Kategori', sortable: true },
    { key: 'file_type', label: 'Tipe File', sortable: true, align: 'center' as const, class: 'uppercase font-mono text-xs' },
    { key: 'ukuran_format', label: 'Ukuran', sortable: true, align: 'right' as const },
    { key: 'actions', label: 'Aksi', align: 'center' as const },
];

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteOpen = ref(false);
const selectedItem = ref<DokumenTemplate | null>(null);

const form = useForm({
    nama: '',
    deskripsi: '',
    kategori: 'Lainnya',
    file: null as File | null,
    _method: 'POST',
});

const kategoris = ['Skripsi', 'Tugas', 'Absen Praktek', 'Laporan', 'Administrasi', 'Lainnya'];

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
    }
};

const resetForm = () => {
    form.clearErrors();
    form.nama = '';
    form.deskripsi = '';
    form.kategori = 'Lainnya';
    form.file = null;
    form._method = 'POST';
};

const openCreate = () => {
    resetForm();
    isCreateOpen.value = true;
};

const openEdit = (item: DokumenTemplate) => {
    selectedItem.value = item;
    form.clearErrors();
    form.nama = item.nama;
    form.deskripsi = item.deskripsi || '';
    form.kategori = item.kategori;
    form.file = null;
    form._method = 'PUT'; // Inertia needs _method for PUT requests with FormData (file uploads)
    isEditOpen.value = true;
};

const openDelete = (item: DokumenTemplate) => {
    selectedItem.value = item;
    isDeleteOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.dokumen-template.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreateOpen.value = false;
            toast.success('Berhasil', { description: 'Template dokumen berhasil diupload.' });
        },
    });
};

const submitEdit = () => {
    if (!selectedItem.value) return;
    form.post(route('admin.dokumen-template.update', selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditOpen.value = false;
            toast.success('Berhasil', { description: 'Template dokumen berhasil diperbarui.' });
        },
    });
};

const submitDelete = () => {
    if (!selectedItem.value) return;
    router.delete(route('admin.dokumen-template.destroy', selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteOpen.value = false;
            toast.success('Berhasil', { description: 'Template dokumen berhasil dihapus.' });
        },
    });
};
</script>

<template>
    <Head title="Template Dokumen" />

    <div class="flex h-full flex-col gap-8 p-6 lg:p-10 w-full">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Template Dokumen</h1>
                <p class="text-slate-500 mt-1">Kelola template dokumen yang dapat diunduh mahasiswa.</p>
            </div>
            <div class="flex items-center gap-2">
                <Button @click="openCreate" class="gap-2">
                    <Plus class="w-4 h-4" />
                    Upload Template
                </Button>
            </div>
        </div>

        <SmartTable :data="templates" :columns="columns" search-placeholder="Cari Template..." title="Daftar Template">
            <template #cell-nama="{ row }">
                <div>
                    <div class="font-medium text-slate-900">{{ row.nama }}</div>
                    <div class="text-xs text-slate-500 mt-0.5" v-if="row.deskripsi">
                        {{ row.deskripsi.length > 60 ? row.deskripsi.substring(0, 60) + '...' : row.deskripsi }}
                    </div>
                </div>
            </template>
            
            <template #cell-actions="{ row }">
                <div class="flex items-center justify-center gap-2">
                    <a :href="`/dokumen-template/${row.id}/download`" target="_blank" class="h-8 w-8 inline-flex items-center justify-center rounded-md text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 transition-colors" title="Download">
                        <Download class="w-4 h-4" />
                    </a>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50" @click="openEdit(row)">
                        <Pencil class="w-4 h-4" />
                    </Button>
                    <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50" @click="openDelete(row)">
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>
            </template>
        </SmartTable>

        <!-- Create/Edit Modal -->
        <Dialog :open="isCreateOpen || isEditOpen" @update:open="(val) => { if(!val) { isCreateOpen = false; isEditOpen = false; } }">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditOpen ? 'Edit Template' : 'Upload Template Dokumen' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditOpen ? 'Perbarui informasi dan file template dokumen.' : 'Pilih file dan lengkapi informasi template.' }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="space-y-2">
                        <Label>Nama Dokumen / Judul <span class="text-red-500">*</span></Label>
                        <Input v-model="form.nama" placeholder="Contoh: Template Laporan PKL" />
                        <p class="text-[10px] text-red-500" v-if="form.errors.nama">{{ form.errors.nama }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label>Kategori <span class="text-red-500">*</span></Label>
                        <Select v-model="form.kategori">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="kat in kategoris" :key="kat" :value="kat">
                                    {{ kat }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p class="text-[10px] text-red-500" v-if="form.errors.kategori">{{ form.errors.kategori }}</p>
                    </div>
                    
                    <div class="space-y-2">
                        <Label>File Dokumen <span class="text-red-500" v-if="!isEditOpen">*</span></Label>
                        
                        <div class="relative flex items-center justify-center w-full mt-1">
                            <label for="template-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50/50 hover:bg-slate-50 hover:border-blue-400 transition-colors group">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-3 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    
                                    <p class="mb-1 text-sm text-slate-500" v-if="!form.file">
                                        <span class="font-semibold text-blue-600">Klik untuk upload</span> dokumen
                                    </p>
                                    <div class="flex flex-col items-center" v-else>
                                        <p class="mb-1 text-sm font-semibold text-blue-700 truncate max-w-[280px]">
                                            {{ form.file.name }}
                                        </p>
                                        <p class="text-xs text-emerald-600 font-medium whitespace-nowrap">File berhasil dipilih</p>
                                    </div>
                                    
                                    <p class="text-[11px] text-slate-400 mt-1" v-if="!form.file">Format terdukung: DOC, PDF, XLS, PPT (Maks 20MB)</p>
                                </div>
                                <input id="template-file" type="file" ref="fileInput" @change="handleFileChange" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="hidden" />
                            </label>
                        </div>
                        
                        <p class="text-xs flex items-center gap-1 mt-1 text-amber-600 font-medium" v-if="isEditOpen && !form.file">
                            * Kosongkan field ini jika tidak ingin mengubah file template yang lama.
                        </p>
                        
                        <p class="text-[10px] text-red-500" v-if="form.errors.file">{{ form.errors.file }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label>Deskripsi Singkat (Opsional)</Label>
                        <Textarea v-model="form.deskripsi" placeholder="Tuliskan keterangan sinkat agar mudah dipahami mahasiswa..." class="resize-none" rows="3" />
                        <p class="text-[10px] text-red-500" v-if="form.errors.deskripsi">{{ form.errors.deskripsi }}</p>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isCreateOpen = false; isEditOpen = false">Batal</Button>
                    <Button @click="isEditOpen ? submitEdit() : submitCreate()" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : (isEditOpen ? 'Simpan' : 'Upload') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <AlertDialog :open="isDeleteOpen" @update:open="isDeleteOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Hapus Template Dokumen?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Apakah Anda yakin ingin menghapus template <strong>{{ selectedItem?.nama }}</strong>? 
                        File yang sudah diunggah juga akan dihapus dari server secara permanen.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction class="bg-red-600 hover:bg-red-700" @click="submitDelete">
                        Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </div>
</template>
