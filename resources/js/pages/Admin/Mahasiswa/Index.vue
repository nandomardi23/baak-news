<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
    program_studi: string | null;
    angkatan: string;
    status: string;
    ipk: number | null;
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Pagination {
    data: Mahasiswa[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    mahasiswa: Pagination;
    prodi: Prodi[];
    filters: {
        search?: string;
        prodi?: string;
        status?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mahasiswa', href: '/admin/mahasiswa' },
];

const search = ref(props.filters.search || '');
const selectedProdi = ref(props.filters.prodi || '');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/admin/mahasiswa', {
        search: search.value || undefined,
        prodi: selectedProdi.value || undefined,
        status: selectedStatus.value || undefined,
    }, { preserveState: true });
};

const clearFilters = () => {
    search.value = '';
    selectedProdi.value = '';
    selectedStatus.value = '';
    router.get('/admin/mahasiswa');
};

const exportExcel = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (selectedProdi.value) params.append('prodi', selectedProdi.value);
    window.location.href = '/admin/mahasiswa/export?' + params.toString();
};
</script>

<template>
    <Head title="Data Mahasiswa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Data Mahasiswa</h1>
                    <p class="text-muted-foreground">Total: {{ mahasiswa.total }} mahasiswa</p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="exportExcel"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Excel
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium mb-1">Cari</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Nama atau NIM..."
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="w-48">
                    <label class="block text-sm font-medium mb-1">Program Studi</label>
                    <select
                        v-model="selectedProdi"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">Semua</option>
                        <option v-for="p in prodi" :key="p.id" :value="p.id">{{ p.nama_prodi }}</option>
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select
                        v-model="selectedStatus"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">Semua</option>
                        <option value="A">Aktif</option>
                        <option value="C">Cuti</option>
                        <option value="L">Lulus</option>
                        <option value="N">Non-Aktif</option>
                        <option value="D">Drop Out</option>
                        <option value="K">Keluar</option>
                    </select>
                </div>
                <button
                    @click="applyFilters"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90"
                >
                    Filter
                </button>
                <button
                    @click="clearFilters"
                    class="px-4 py-2 border rounded-lg hover:bg-muted"
                >
                    Reset
                </button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">NIM</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Nama</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Program Studi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Angkatan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">IPK</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="mhs in mahasiswa.data" :key="mhs.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 font-mono text-sm">{{ mhs.nim }}</td>
                                <td class="px-6 py-4 font-medium">{{ mhs.nama }}</td>
                                <td class="px-6 py-4 text-sm">{{ mhs.program_studi || '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ mhs.angkatan }}</td>
                                <td class="px-6 py-4 text-sm">{{ mhs.ipk !== null ? Number(mhs.ipk).toFixed(2) : '-' }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="mhs.status === 'Aktif' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-800'"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ mhs.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <Link
                                        :href="`/admin/mahasiswa/${mhs.id}`"
                                        class="text-primary hover:underline text-sm"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="mahasiswa.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-muted-foreground">
                                    Tidak ada data mahasiswa
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="mahasiswa.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman {{ mahasiswa.current_page }} dari {{ mahasiswa.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in mahasiswa.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded text-sm',
                                link.active ? 'bg-primary text-primary-foreground' : 'border hover:bg-muted',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
