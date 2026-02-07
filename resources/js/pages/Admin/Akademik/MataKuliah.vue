<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    DataTable,
    TableHeader,
    TableRow,
    TableCell,
    Pagination
} from '@/components/ui/datatable';
import { Filter, X, Search, Check, FileText, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';
import { watchDebounced } from '@vueuse/core';

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

interface PaginationData {
    data: MataKuliah[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

const props = defineProps<{
    mataKuliah: PaginationData;
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
const selectedProdi = ref(props.filters.prodi || 'all');
const sortField = ref('kode_matkul');
const sortDirection = ref<'asc' | 'desc'>('asc');

const applyFilters = () => {
    router.get('/admin/akademik/matakuliah', {
        search: search.value || undefined,
        prodi: selectedProdi.value === 'all' ? undefined : selectedProdi.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, { preserveState: true, preserveScroll: true });
};

watchDebounced(
    search,
    () => { applyFilters(); },
    { debounce: 500, maxWait: 1000 }
);

const clearFilters = () => {
    search.value = '';
    selectedProdi.value = 'all';
    sortField.value = 'kode_matkul';
    sortDirection.value = 'asc';
    applyFilters();
};

const sort = (field: string) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    applyFilters();
};
</script>

<template>
    <Head title="Mata Kuliah" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Mata Kuliah</h1>
                    <p class="text-slate-500 mt-1">Data mata kuliah dari Neo Feeder. Total: {{ mataKuliah.total }}</p>
                </div>
                <!-- Optional: Sync button or Neo Feeder badge -->
                <div class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-xs font-medium flex items-center gap-1">
                    <Check class="w-3 h-3" />
                    Data Terintegrasi Neo Feeder
                </div>
            </div>

            <!-- Standardized DataTable -->
            <DataTable>
                <!-- Toolbar Slot -->
                <template #toolbar>
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <Filter class="w-4 h-4" />
                        </div>
                        <h3 class="text-base font-bold text-slate-700">Filtering</h3>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 items-center w-full sm:w-auto">
                        
                        <!-- Filter Button / Clear -->
                        <Button 
                            v-if="search || selectedProdi !== 'all'"
                            variant="ghost" 
                            size="sm" 
                            @click="clearFilters"
                            class="text-red-600 hover:text-red-700 hover:bg-red-50 h-9 gap-1"
                        >
                            <X class="w-4 h-4" />
                            Clear Filters
                        </Button>

                         <div v-if="search || selectedProdi !== 'all'" class="h-6 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                        <!-- Prodi Filter -->
                        <div class="w-full sm:w-60">
                            <Select v-model="selectedProdi" @update:modelValue="applyFilters">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Pilih Prodi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Program Studi</SelectItem>
                                    <SelectItem v-for="prodi in prodiList" :key="prodi.id" :value="prodi.id_prodi">
                                        {{ prodi.nama_prodi }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full sm:w-64">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Cari Kode / Nama Matkul..."
                                class="pl-9 h-9 w-full focus-visible:ring-1"
                            />
                        </div>
                    </div>
                </template>

                <!-- Table Header -->
                <thead class="bg-slate-50/50">
                    <tr>
                        <TableHeader @click="sort('kode_matkul')" class="cursor-pointer hover:bg-slate-100">
                            Kode
                            <ArrowUp v-if="sortField === 'kode_matkul' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'kode_matkul' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('nama_matkul')" class="cursor-pointer hover:bg-slate-100">
                            Nama Mata Kuliah
                            <ArrowUp v-if="sortField === 'nama_matkul' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'nama_matkul' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('sks_mata_kuliah')" class="text-center cursor-pointer hover:bg-slate-100">
                            SKS
                            <ArrowUp v-if="sortField === 'sks_mata_kuliah' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'sks_mata_kuliah' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('sks_teori')" class="text-center cursor-pointer hover:bg-slate-100">
                            Teori
                            <ArrowUp v-if="sortField === 'sks_teori' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'sks_teori' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('sks_praktek')" class="text-center cursor-pointer hover:bg-slate-100">
                            Praktek
                            <ArrowUp v-if="sortField === 'sks_praktek' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'sks_praktek' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader>Program Studi</TableHeader>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <TableRow v-for="mk in mataKuliah.data" :key="mk.id">
                        <TableCell class="font-mono text-slate-600 font-medium">{{ mk.kode_matkul }}</TableCell>
                        <TableCell>
                            <span class="font-medium text-slate-800">{{ mk.nama_matkul }}</span>
                        </TableCell>
                        <TableCell class="text-center font-bold text-slate-700">{{ mk.sks_mata_kuliah }}</TableCell>
                        <TableCell class="text-center text-slate-500">{{ mk.sks_teori ?? '-' }}</TableCell>
                        <TableCell class="text-center text-slate-500">{{ mk.sks_praktek ?? '-' }}</TableCell>
                        <TableCell class="text-sm text-slate-600">{{ mk.prodi || '-' }}</TableCell>
                    </TableRow>
                    
                    <!-- Empty State -->
                    <TableRow v-if="mataKuliah.data.length === 0">
                        <TableCell colspan="6" class="h-64 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-500">
                                <div class="bg-slate-100 p-4 rounded-full mb-3">
                                    <Search class="h-6 w-6 text-slate-400" />
                                </div>
                                <p class="font-medium text-slate-900">Tidak ada data ditemukan</p>
                                <p class="text-sm">Silakan sync dari Neo Feeder atau ubah filter.</p>
                                <Button 
                                    variant="link" 
                                    class="mt-2 text-blue-600" 
                                    @click="clearFilters"
                                >
                                    Reset Filter
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </tbody>

                <!-- Pagination Slot -->
                <template #pagination>
                    <Pagination :pagination="mataKuliah" />
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
