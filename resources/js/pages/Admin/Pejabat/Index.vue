<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Pejabat {
    id: number;
    nama: string;
    nama_lengkap: string;
    nip: string | null;
    nidn: string | null;
    nik: string | null;
    jabatan: string;
    gelar_depan: string | null;
    gelar_belakang: string | null;
    pangkat_golongan: string | null;
    periode_awal: string | null;
    periode_akhir: string | null;
    tandatangan_path: string | null;
    is_active: boolean;
}

const props = defineProps<{
    pejabat: Pejabat[];
    jabatanOptions: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pejabat', href: '/admin/pejabat' },
];

const showModal = ref(false);
const editingPejabat = ref<Pejabat | null>(null);

const form = useForm({
    nama: '',
    nip: '',
    nidn: '',
    nik: '',
    jabatan: '',
    gelar_depan: '',
    gelar_belakang: '',
    pangkat_golongan: '',
    periode_awal: '',
    periode_akhir: '',
    tandatangan: null as File | null,
    is_active: true,
});

const openCreateModal = () => {
    editingPejabat.value = null;
    form.reset();
    form.is_active = true;
    showModal.value = true;
};

const openEditModal = (item: Pejabat) => {
    editingPejabat.value = item;
    form.nama = item.nama || '';
    form.nip = item.nip || '';
    form.nidn = item.nidn || '';
    form.nik = item.nik || '';
    form.jabatan = item.jabatan || '';
    form.gelar_depan = item.gelar_depan || '';
    form.gelar_belakang = item.gelar_belakang || '';
    form.pangkat_golongan = item.pangkat_golongan || '';
    form.periode_awal = item.periode_awal || '';
    form.periode_akhir = item.periode_akhir || '';
    form.tandatangan = null;
    form.is_active = item.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingPejabat.value = null;
    form.reset();
};

const submit = () => {
    if (editingPejabat.value) {
        form.post(`/admin/pejabat/${editingPejabat.value.id}`, {
            forceFormData: true,
            headers: { 'X-HTTP-Method-Override': 'PUT' },
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/pejabat', {
            onSuccess: () => closeModal(),
        });
    }
};

const deletePejabat = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus pejabat ini?')) {
        router.delete(`/admin/pejabat/${id}`);
    }
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.tandatangan = target.files[0];
    }
};
</script>

<template>
    <Head title="Data Pejabat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Data Pejabat</h1>
                    <p class="text-muted-foreground">Kelola data pejabat penandatangan surat</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Pejabat
                </button>
            </div>

            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Nama</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Jabatan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">NIP</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Periode</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="item in pejabat" :key="item.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium">{{ item.nama_lengkap }}</p>
                                        <p v-if="item.nidn" class="text-sm text-muted-foreground">NIDN: {{ item.nidn }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ item.jabatan }}</td>
                                <td class="px-6 py-4 text-sm">{{ item.nip || '-' }}</td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">
                                    <span v-if="item.periode_awal">
                                        {{ item.periode_awal }} - {{ item.periode_akhir || 'Sekarang' }}
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="item.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ item.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="openEditModal(item)"
                                            class="text-blue-600 hover:text-blue-800 text-sm"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="deletePejabat(item.id)"
                                            class="text-red-600 hover:text-red-800 text-sm"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pejabat.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada data pejabat
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b flex items-center justify-between">
                    <h2 class="text-xl font-bold">{{ editingPejabat ? 'Edit Pejabat' : 'Tambah Pejabat' }}</h2>
                    <button @click="closeModal" class="text-muted-foreground hover:text-foreground">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Nama <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.nama"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                required
                            />
                            <p v-if="form.errors.nama" class="text-red-500 text-sm mt-1">{{ form.errors.nama }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Gelar Depan</label>
                            <input
                                v-model="form.gelar_depan"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Dr."
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Gelar Belakang</label>
                            <input
                                v-model="form.gelar_belakang"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="S.Kep, M.Kep"
                            />
                        </div>

                        <div class="md:col-span-2">
                             <label class="block text-sm font-medium mb-1">Pangkat / Golongan</label>
                             <input
                                 v-model="form.pangkat_golongan"
                                 type="text"
                                 class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                 placeholder="Contoh: Kolonel Laut (K/W) Purn"
                             />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">NIP</label>
                            <input
                                v-model="form.nip"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">NIDN</label>
                            <input
                                v-model="form.nidn"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">NIK</label>
                            <input
                                v-model="form.nik"
                                type="text"
                                maxlength="16"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="16 digit NIK"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Jabatan <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.jabatan"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary bg-background"
                                required
                            >
                                <option value="" disabled>Pilih jabatan</option>
                                <option v-for="opt in jabatanOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <p v-if="form.errors.jabatan" class="text-red-500 text-sm mt-1">{{ form.errors.jabatan }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Periode Awal</label>
                            <input
                                v-model="form.periode_awal"
                                type="date"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Periode Akhir</label>
                            <input
                                v-model="form.periode_akhir"
                                type="date"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Tanda Tangan (PNG/JPG)</label>
                            <input
                                type="file"
                                accept="image/png,image/jpeg"
                                @change="handleFileChange"
                                class="w-full px-4 py-2 border rounded-lg"
                            />
                            <p v-if="editingPejabat?.tandatangan_path" class="text-sm text-muted-foreground mt-1">
                                File saat ini tersimpan
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="w-4 h-4 rounded"
                                />
                                <span class="text-sm font-medium">Aktif</span>
                            </label>
                        </div>
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
