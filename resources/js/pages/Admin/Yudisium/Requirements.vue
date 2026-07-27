<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useConfirmDelete } from '@/composables/useConfirmDelete';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Requirement {
    id: number;
    nama_syarat: string;
    deskripsi: string | null;
    is_upload_required: boolean;
    is_active: boolean;
    program_studi_id: number | null;
    prodi: string;
}

const props = defineProps<{
    requirements: any;
    filters: Record<string, any>;
    prodiList: Record<string, string>;
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Surat & Dokumen', href: '#' },
    { title: 'Master Syarat Yudisium', href: '/admin/yudisium/requirements' },
]);

// Table Columns Configuration
const columns = [
    { key: 'nama_syarat', label: 'Nama Syarat', sortable: true },
    { key: 'prodi', label: 'Berlaku Untuk', sortable: true },
    { key: 'is_upload_required', label: 'Wajib Upload', sortable: true, align: 'center' as const },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' as const },
    { key: 'aksi', label: 'Aksi', align: 'right' as const },
];

const showModal = ref(false);
const editingItem = ref<Requirement | null>(null);

const form = useForm({
    program_studi_id: null as number | null,
    nama_syarat: '',
    deskripsi: '',
    is_upload_required: false,
    is_active: true,
});

const resetForm = () => {
    form.clearErrors();
    form.defaults({
        program_studi_id: null,
        nama_syarat: '',
        deskripsi: '',
        is_upload_required: false,
        is_active: true,
    });
    form.reset();
    form.program_studi_id = null;
    form.nama_syarat = '';
    form.deskripsi = '';
    form.is_upload_required = false;
    form.is_active = true;
};

const openCreateModal = () => {
    editingItem.value = null;
    resetForm();
    showModal.value = true;
};

const openEditModal = (item: Requirement) => {
    editingItem.value = item;
    form.clearErrors();
    form.program_studi_id = item.program_studi_id;
    form.nama_syarat = item.nama_syarat;
    form.deskripsi = item.deskripsi || '';
    form.is_upload_required = item.is_upload_required;
    form.is_active = item.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
    form.reset();
};

const submit = () => {
    if (editingItem.value) {
        form.put(`/admin/yudisium/requirements/${editingItem.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/yudisium/requirements', {
            onSuccess: () => closeModal(),
        });
    }
};

const { confirmDelete } = useConfirmDelete();

const deleteItem = (id: number) => {
    confirmDelete({
        url: `/admin/yudisium/requirements/${id}`,
        entityName: 'Syarat Yudisium',
        text: 'Tindakan ini tidak dapat dibatalkan. Syarat ini akan dihapus permanen.',
    });
};
</script>

<template>
    <Head title="Master Syarat Yudisium" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Master Syarat Yudisium</h1>
                <p class="text-muted-foreground">Kelola persyaratan yang dibutuhkan mahasiswa untuk yudisium</p>
            </div>
        </div>

        <SmartTable
            :data="requirements"
            :columns="columns"
            :search="filters.search"
            :sort-field="filters.sort_field"
            :sort-direction="filters.sort_direction"
            title="Daftar Syarat Yudisium"
        >
            <template #actions>
                <Button @click="openCreateModal" class="bg-primary text-primary-foreground hover:bg-primary/90">
                    <Plus class="w-4 h-4 mr-2" />
                    Tambah Syarat
                </Button>
            </template>

            <template #cell-prodi="{ value }">
                <span class="font-medium text-slate-700">
                    {{ value }}
                </span>
            </template>

            <template #cell-is_upload_required="{ value }">
                <span
                    :class="value ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-slate-100 text-slate-800 border-slate-200'"
                    class="px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize"
                >
                    {{ value ? 'Ya' : 'Tidak' }}
                </span>
            </template>

            <template #cell-is_active="{ value }">
                <span
                    :class="value ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-gray-100 text-gray-800 border-gray-200'"
                    class="px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize"
                >
                    {{ value ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </template>

            <template #cell-aksi="{ row }">
                <div class="flex items-center justify-end gap-2">
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="openEditModal(row)"
                        class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-8 w-8"
                        title="Edit"
                    >
                        <Pencil class="w-4 h-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        @click="deleteItem(row.id)"
                        class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 w-8"
                        title="Hapus"
                    >
                        <Trash2 class="w-4 h-4" />
                    </Button>
                </div>
            </template>
        </SmartTable>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b flex items-center justify-between bg-slate-50">
                <h2 class="text-lg font-bold text-slate-900">{{ editingItem ? 'Edit Syarat' : 'Tambah Syarat' }}</h2>
                <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">Berlaku Untuk</label>
                    <Select 
                        :model-value="form.program_studi_id ? String(form.program_studi_id) : 'all'" 
                        @update:model-value="(v) => form.program_studi_id = v === 'all' ? null : Number(v)"
                    >
                        <SelectTrigger class="w-full bg-white border-slate-200">
                            <SelectValue placeholder="Semua Prodi (Global)" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua Prodi (Global)</SelectItem>
                            <SelectItem v-for="(nama, id) in prodiList" :key="id" :value="String(id)">
                                {{ nama }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">Nama Syarat <span class="text-red-500">*</span></label>
                    <input
                        v-model="form.nama_syarat"
                        type="text"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                        placeholder="Contoh: Bebas Perpustakaan"
                        required
                    />
                    <p v-if="form.errors.nama_syarat" class="text-red-500 text-sm mt-1">{{ form.errors.nama_syarat }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">Deskripsi (Opsional)</label>
                    <textarea
                        v-model="form.deskripsi"
                        rows="2"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                        placeholder="Keterangan tambahan untuk mahasiswa"
                    ></textarea>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            v-model="form.is_upload_required"
                            type="checkbox"
                            class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300"
                        />
                        <span class="text-sm font-medium text-slate-700">Wajib Upload Bukti (File)</span>
                    </label>
                </div>

                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            v-model="form.is_active"
                            type="checkbox"
                            class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300"
                        />
                        <span class="text-sm font-medium text-slate-700">Aktif</span>
                    </label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t mt-6">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 border rounded-lg hover:bg-slate-50 font-medium text-slate-700 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 font-medium transition disabled:opacity-50 flex items-center"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
