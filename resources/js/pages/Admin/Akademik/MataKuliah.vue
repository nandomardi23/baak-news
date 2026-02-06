<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface MataKuliah {
    id: number;
    kode_matkul: string;
    nama_matkul: string;
    sks_mata_kuliah: number;
    sks_teori: number | null;
    sks_praktek: number | null;
    prodi: string | null;
}

interface Prodi {
    id: number;
    id_prodi: string;
    nama_prodi: string;
}

interface Pagination {
    data: MataKuliah[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    mataKuliah: Pagination;
    prodiList: Prodi[];
    filters: {
        prodi?: string;
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mata Kuliah', href: '/admin/akademik/matakuliah' },
];

const search = ref(props.filters.search || '');
const selectedProdi = ref(props.filters.prodi || '');

const applyFilters = () => {
    router.get('/admin/akademik/matakuliah', {
        search: search.value || undefined,
        prodi: selectedProdi.value || undefined,
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Mata Kuliah" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Mata Kuliah</h1>
                    <p class="text-muted-foreground">Total: {{ mataKuliah.total }} mata kuliah</p>
                </div>
                <div class="px-4 py-2 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-sm">
                    Data dari Neo Feeder
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium mb-1">Cari</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Kode atau nama mata kuliah..."
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="w-60">
                    <label class="block text-sm font-medium mb-1">Program Studi</label>
                    <select
                        v-model="selectedProdi"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">Semua</option>
                        <option v-for="prodi in prodiList" :key="prodi.id" :value="prodi.id_prodi">
                            {{ prodi.nama_prodi }}
                        </option>
                    </select>
                </div>
                <button
                    @click="applyFilters"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90"
                >
                    Filter
                </button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Kode</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Nama Mata Kuliah</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-muted-foreground">SKS</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-muted-foreground">Teori</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-muted-foreground">Praktek</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Prodi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="mk in mataKuliah.data" :key="mk.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 font-mono text-sm">{{ mk.kode_matkul }}</td>
                                <td class="px-6 py-4">{{ mk.nama_matkul }}</td>
                                <td class="px-6 py-4 text-center font-medium">{{ mk.sks_mata_kuliah }}</td>
                                <td class="px-6 py-4 text-center text-muted-foreground">{{ mk.sks_teori ?? '-' }}</td>
                                <td class="px-6 py-4 text-center text-muted-foreground">{{ mk.sks_praktek ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ mk.prodi || '-' }}</td>
                            </tr>
                            <tr v-if="mataKuliah.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada data mata kuliah. Silakan sync dari Neo Feeder.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="mataKuliah.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman {{ mataKuliah.current_page }} dari {{ mataKuliah.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in mataKuliah.links"
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
