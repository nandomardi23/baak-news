<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Jabatan {
    id: number;
    nama_jabatan: string;
    kode_jabatan: string | null;
    is_active: boolean;
}

const props = defineProps<{
    jabatan: Jabatan[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Master Data', href: '#' },
    { title: 'Jabatan', href: '/admin/jabatan' },
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
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Jabatan
                </button>
            </div>

            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Nama Jabatan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Kode (Opsional)</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="item in jabatan" :key="item.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 font-medium">{{ item.nama_jabatan }}</td>
                                <td class="px-6 py-4 text-sm">{{ item.kode_jabatan || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="item.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ item.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <button
                                            @click="openEditModal(item)"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium"
                                            title="Edit jabatan"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button
                                            @click="deleteJabatan(item.id)"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800 text-sm font-medium"
                                            title="Hapus jabatan"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="jabatan.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada data jabatan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-xl shadow-xl w-full max-w-md">
                <div class="p-6 border-b flex items-center justify-between">
                    <h2 class="text-xl font-bold">{{ editingJabatan ? 'Edit Jabatan' : 'Tambah Jabatan' }}</h2>
                    <button @click="closeModal" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Jabatan <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.nama_jabatan"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Contoh: Wakil Ketua 1"
                            required
                        />
                        <p v-if="form.errors.nama_jabatan" class="text-red-500 text-sm mt-1">{{ form.errors.nama_jabatan }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Kode (Opsional)</label>
                        <input
                            v-model="form.kode_jabatan"
                            type="text"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            placeholder="Contoh: WAKET1"
                        />
                         <p class="text-xs text-muted-foreground mt-1">Kode unik untuk referensi internal sistem (jika diperlukan).</p>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="w-4 h-4 rounded"
                            />
                            <span class="text-sm font-medium">Aktif</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 border rounded-lg hover:bg-muted"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
