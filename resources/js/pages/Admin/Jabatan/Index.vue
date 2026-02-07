<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SmartTable from '@/components/ui/datatable/SmartTable.vue'; // Updated import
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Jabatan {
    id: number;
    nama_jabatan: string;
    kode_jabatan: string | null;
    is_active: boolean;
}

const props = defineProps<{
    jabatan: any; // Pagination object
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Master Data', href: '#' },
    { title: 'Jabatan', href: '/admin/jabatan' },
];

// Table Columns Configuration
const columns = [
    { key: 'nama_jabatan', label: 'Nama Jabatan', sortable: true },
    { key: 'kode_jabatan', label: 'Kode', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' },
    { key: 'aksi', label: 'Aksi', align: 'right' },
];

const showModal = ref(false);
const editingJabatan = ref<Jabatan | null>(null);

const form = useForm({
    nama_jabatan: '',
    kode_jabatan: '',
    is_active: true,
});

const openCreateModal = () => {
    editingJabatan.value = null;
    form.reset();
    form.is_active = true;
    showModal.value = true;
};

const openEditModal = (item: Jabatan) => {
    editingJabatan.value = item;
    form.nama_jabatan = item.nama_jabatan;
    form.kode_jabatan = item.kode_jabatan || '';
    form.is_active = item.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingJabatan.value = null;
    form.reset();
};

const submit = () => {
    if (editingJabatan.value) {
        form.put(`/admin/jabatan/${editingJabatan.value.id}`, {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/jabatan', {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteJabatan = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus jabatan ini?')) {
        router.delete(`/admin/jabatan/${id}`);
    }
};
</script>

<template>
    <Head title="Master Jabatan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Master Jabatan</h1>
                    <p class="text-muted-foreground">Kelola data referensi jabatan pejabat</p>
                </div>
            </div>

            <!-- Smart Table Implementation -->
            <SmartTable
                :data="jabatan"
                :columns="columns"
                :search="filters.search"
                :sort-field="filters.sort_field"
                :sort-direction="filters.sort_direction"
                title="Master Jabatan"
            >
                <!-- Actions Toolbar Slot -->
                <template #actions>
                    <Button @click="openCreateModal" class="bg-primary text-primary-foreground hover:bg-primary/90">
                        <Plus class="w-4 h-4 mr-2" />
                        Tambah Jabatan
                    </Button>
                </template>

                <!-- Custom Cell: Status -->
                <template #cell-is_active="{ value }">
                    <span
                        :class="value ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-gray-100 text-gray-800 border-gray-200'"
                        class="px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize"
                    >
                        {{ value ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </template>

                <!-- Custom Cell: Aksi -->
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
                            @click="deleteJabatan(row.id)"
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
                    <h2 class="text-lg font-bold text-slate-900">{{ editingJabatan ? 'Edit Jabatan' : 'Tambah Jabatan' }}</h2>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700">Nama Jabatan <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.nama_jabatan"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                            placeholder="Contoh: Wakil Ketua 1"
                            required
                        />
                        <p v-if="form.errors.nama_jabatan" class="text-red-500 text-sm mt-1">{{ form.errors.nama_jabatan }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-700">Kode (Opsional)</label>
                        <input
                            v-model="form.kode_jabatan"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition"
                            placeholder="Contoh: WAKET1"
                        />
                        <p class="text-xs text-slate-500 mt-1">Kode unik untuk referensi internal sistem (jika diperlukan).</p>
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
    </AppLayout>
</template>
