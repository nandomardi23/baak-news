<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

interface Dosen {
    id: number;
    id_dosen: string;
    nidn: string | null;
    nip: string | null;
    nama: string;
    nama_lengkap: string;
    jenis_kelamin: string | null;
    jabatan_fungsional: string | null;
    status_aktif: string | null;
    prodi: string | null;
}

interface Filters {
    search: string | null;
    prodi: string | null;
    status: string | null;
}

const props = defineProps<{
    dosen: {
        data: Dosen[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
    };
    prodiList: Record<string, string>;
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Dosen', href: '/admin/dosen' },
];

const search = ref(props.filters.search || '');
const selectedProdi = ref(props.filters.prodi || '');
const selectedStatus = ref(props.filters.status || '');

const applyFilters = () => {
    router.get('/admin/dosen', {
        search: search.value || undefined,
        prodi: selectedProdi.value || undefined,
        status: selectedStatus.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Debounce search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
});

watch([selectedProdi, selectedStatus], applyFilters);

const resetFilters = () => {
    search.value = '';
    selectedProdi.value = '';
    selectedStatus.value = '';
    router.get('/admin/dosen');
};
</script>

<template>
    <Head title="Data Dosen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Data Dosen</h1>
                    <p class="text-muted-foreground">
                        Data dosen dari Neo Feeder (Total: {{ dosen.total }} dosen)
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border bg-card shadow-sm p-4">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium mb-1">Cari</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama, NIDN, atau NIP..."
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                    </div>
                    <div class="min-w-[200px]">
                        <label class="block text-sm font-medium mb-1">Program Studi</label>
                        <select
                            v-model="selectedProdi"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary bg-background"
                        >
                            <option value="">Semua Prodi</option>
                            <option v-for="(nama, id) in prodiList" :key="id" :value="id">{{ nama }}</option>
                        </select>
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-sm font-medium mb-1">Status</label>
                        <select
                            v-model="selectedStatus"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary bg-background"
                        >
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                        </select>
                    </div>
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 border rounded-lg hover:bg-muted"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Nama</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">NIDN</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">NIP</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Jabatan Fungsional</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Program Studi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="item in dosen.data" :key="item.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium">{{ item.nama_lengkap }}</p>
                                        <p class="text-sm text-muted-foreground">
                                            {{ item.jenis_kelamin === 'L' ? 'Laki-laki' : item.jenis_kelamin === 'P' ? 'Perempuan' : '-' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ item.nidn || '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ item.nip || '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ item.jabatan_fungsional || '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ item.prodi || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="item.status_aktif === 'Aktif' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ item.status_aktif || 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="dosen.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    Tidak ada data dosen. Silakan sync dari Neo Feeder terlebih dahulu.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="dosen.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman {{ dosen.current_page }} dari {{ dosen.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <template v-for="link in dosen.links" :key="link.label">
                            <button
                                v-if="link.url"
                                @click="router.get(link.url)"
                                :class="[
                                    'px-3 py-1 rounded border text-sm',
                                    link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1 text-sm text-muted-foreground"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
