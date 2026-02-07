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
import { Filter, X, FileSpreadsheet, Plus, Search, Eye, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';
import { watchDebounced } from '@vueuse/core';

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

interface PaginationData {
    data: Mahasiswa[];
    links: any[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

const props = defineProps<{
    mahasiswa: PaginationData;
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
const selectedProdi = ref(props.filters.prodi || 'all');
const selectedStatus = ref(props.filters.status || 'all');
const sortField = ref('nama');
const sortDirection = ref<'asc' | 'desc'>('asc');

const applyFilters = () => {
    router.get('/admin/mahasiswa', {
        search: search.value || undefined,
        prodi: selectedProdi.value === 'all' ? undefined : selectedProdi.value,
        status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
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
    selectedStatus.value = 'all';
    sortField.value = 'nama';
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

const exportExcel = () => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (selectedProdi.value && selectedProdi.value !== 'all') params.append('prodi', selectedProdi.value);
    window.location.href = '/admin/mahasiswa/export?' + params.toString();
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'Aktif': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
        case 'Cuti': return 'bg-amber-100 text-amber-700 border-amber-200';
        case 'Lulus': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'Non-Aktif': return 'bg-slate-100 text-slate-700 border-slate-200';
        case 'Drop Out': return 'bg-red-100 text-red-700 border-red-200';
        case 'Keluar': return 'bg-orange-100 text-orange-700 border-orange-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
};
</script>

<template>
    <Head title="Data Mahasiswa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Mahasiswa</h1>
                    <p class="text-slate-500 mt-1">Kelola data mahasiswa, status akademik, dan informasi studi.</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="exportExcel" class="gap-2">
                        <FileSpreadsheet class="w-4 h-4" />
                        Export Excel
                    </Button>
                    <!-- Optional: Add Create Button Here if needed -->
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
                            v-if="search || selectedProdi !== 'all' || selectedStatus !== 'all'"
                            variant="ghost" 
                            size="sm" 
                            @click="clearFilters"
                            class="text-red-600 hover:text-red-700 hover:bg-red-50 h-9 gap-1"
                        >
                            <X class="w-4 h-4" />
                            Clear Filters
                        </Button>

                        <div v-if="search || selectedProdi !== 'all' || selectedStatus !== 'all'" class="h-6 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                        <!-- Prodi Filter -->
                        <div class="w-full sm:w-48">
                            <Select v-model="selectedProdi" @update:modelValue="applyFilters">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Pilih Prodi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Prodi</SelectItem>
                                    <SelectItem v-for="p in prodi" :key="p.id" :value="String(p.id)">
                                        {{ p.nama_prodi }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Status Filter -->
                        <div class="w-full sm:w-36">
                            <Select v-model="selectedStatus" @update:modelValue="applyFilters">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Pilih Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="Aktif">Aktif</SelectItem>
                                    <SelectItem value="Cuti">Cuti</SelectItem>
                                    <SelectItem value="Lulus">Lulus</SelectItem>
                                    <SelectItem value="Non-Aktif">Non-Aktif</SelectItem>
                                    <SelectItem value="Drop Out">Drop Out</SelectItem>
                                    <SelectItem value="Keluar">Keluar</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full sm:w-64">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Cari Nama / NIM..."
                                class="pl-9 h-9 w-full focus-visible:ring-1"
                            />
                        </div>
                    </div>
                </template>

                <!-- Table Header -->
                <thead class="bg-slate-50/50">
                    <tr>
                        <TableHeader @click="sort('nim')" class="cursor-pointer hover:bg-slate-100">
                            NIM
                            <ArrowUp v-if="sortField === 'nim' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'nim' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('nama')" class="cursor-pointer hover:bg-slate-100">
                            Nama Mahasiswa
                            <ArrowUp v-if="sortField === 'nama' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'nama' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('angkatan')" class="cursor-pointer hover:bg-slate-100">
                            Angkatan
                            <ArrowUp v-if="sortField === 'angkatan' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'angkatan' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader>Program Studi</TableHeader>
                        <TableHeader @click="sort('ipk')" class="cursor-pointer hover:bg-slate-100">
                            IPK
                            <ArrowUp v-if="sortField === 'ipk' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'ipk' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('status_mahasiswa')" class="cursor-pointer hover:bg-slate-100 text-center">
                            Status
                            <ArrowUp v-if="sortField === 'status_mahasiswa' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'status_mahasiswa' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader class="text-right">Aksi</TableHeader>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <TableRow v-for="mhs in mahasiswa.data" :key="mhs.id">
                        <TableCell class="font-mono text-slate-600">{{ mhs.nim }}</TableCell>
                        <TableCell>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 capitalize">{{ mhs.nama.toLowerCase() }}</span>
                            </div>
                        </TableCell>
                        <TableCell>{{ mhs.angkatan }}</TableCell>
                        <TableCell>{{ mhs.program_studi || '-' }}</TableCell>
                        <TableCell>
                            <span :class="Number(mhs.ipk) >= 3.0 ? 'text-emerald-600 font-bold' : 'text-slate-600'">
                                {{ mhs.ipk !== null ? Number(mhs.ipk).toFixed(2) : '-' }}
                            </span>
                        </TableCell>
                        <TableCell class="text-center">
                            <span
                                :class="getStatusBadge(mhs.status)"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize"
                            >
                                {{ mhs.status.toLowerCase() }}
                            </span>
                        </TableCell>
                        <TableCell class="text-right">
                            <Link
                                :href="`/admin/mahasiswa/${mhs.id}`"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition-colors"
                                title="Detail"
                            >
                                <component :is="Eye" class="h-4 w-4" />
                            </Link>
                        </TableCell>
                    </TableRow>
                    
                    <!-- Empty State -->
                    <TableRow v-if="mahasiswa.data.length === 0">
                        <TableCell colspan="7" class="h-64 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-500">
                                <div class="bg-slate-100 p-4 rounded-full mb-3">
                                    <Search class="h-6 w-6 text-slate-400" />
                                </div>
                                <p class="font-medium text-slate-900">Tidak ada data ditemukan</p>
                                <p class="text-sm">Coba ubah filter pencarian Anda.</p>
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
                    <Pagination :pagination="mahasiswa" />
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
