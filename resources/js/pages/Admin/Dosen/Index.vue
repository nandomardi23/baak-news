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
import { Filter, X, Search, Eye, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';
import { watchDebounced } from '@vueuse/core';

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

interface PaginationData {
    data: Dosen[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

const props = defineProps<{
    dosen: PaginationData;
    prodiList: Record<string, string>;
    filters: {
        search?: string;
        prodi?: string;
        status?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Dosen', href: '/admin/dosen' },
];

const search = ref(props.filters.search || '');
const selectedProdi = ref(props.filters.prodi || 'all');
const selectedStatus = ref(props.filters.status || 'all');
const sortField = ref('nama');
const sortDirection = ref<'asc' | 'desc'>('asc');

const applyFilters = () => {
    router.get('/admin/dosen', {
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

const getStatusBadge = (status: string | null) => {
    if (status === 'Aktif') return 'bg-emerald-100 text-emerald-700 border-emerald-200';
    return 'bg-gray-100 text-gray-700 border-gray-200';
};
</script>

<template>
    <Head title="Data Dosen" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Dosen</h1>
                    <p class="text-slate-500 mt-1">Kelola data dosen, jabatan fungsional, dan status aktif.</p>
                </div>
                <div class="flex gap-2">
                    <!-- Optional: Add Buttons here if needed -->
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
                                    <SelectItem v-for="(nama, id) in prodiList" :key="id" :value="String(id)">
                                        {{ nama }}
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
                                    <SelectItem value="aktif">Aktif</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full sm:w-64">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Cari Nama / NIDN / NIP..."
                                class="pl-9 h-9 w-full focus-visible:ring-1"
                            />
                        </div>
                    </div>
                </template>

                <!-- Table Header -->
                <thead class="bg-slate-50/50">
                    <tr>
                        <TableHeader @click="sort('nama')" class="cursor-pointer hover:bg-slate-100">
                            Nama Dosen
                            <ArrowUp v-if="sortField === 'nama' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'nama' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('nidn')" class="cursor-pointer hover:bg-slate-100">
                            NIDN
                            <ArrowUp v-if="sortField === 'nidn' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'nidn' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('nip')" class="cursor-pointer hover:bg-slate-100">
                            NIP
                            <ArrowUp v-if="sortField === 'nip' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'nip' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('jabatan_fungsional')" class="cursor-pointer hover:bg-slate-100">
                            Jabatan Fungsional
                            <ArrowUp v-if="sortField === 'jabatan_fungsional' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'jabatan_fungsional' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader>Program Studi</TableHeader>
                        <TableHeader @click="sort('status_aktif')" class="cursor-pointer hover:bg-slate-100 text-center">
                            Status
                            <ArrowUp v-if="sortField === 'status_aktif' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'status_aktif' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <!-- Aksi column temporarily removed or kept if needed. The wireframe has no explicit actions but standard requires it. Assuming 'Detail' view exists or will exist. The user didn't ask for Edit/Delete on Dosen yet, but Detail is usually safe. -->
                         <!-- Wait, Dosen controller doesn't seem to have a show method in the view_file output, but web.php shows it uses Resource controller? No, it uses [DosenController::class, 'index']. Let me check web.php again. -->
                         <!-- Route::get('dosen', [\App\Http\Controllers\Admin\DosenController::class, 'index'])->name('dosen.index'); -->
                         <!-- Only index is defined! So no show/edit/delete routes for Dosen. I should omit the Aksi column for now or keep it empty/future-proof. -->
                         <!-- Actually, standardizing implied adding features. But if the route doesn't exist, the link will break. -->
                         <!-- I will OMIT the Action column for now to avoid broken links, or added it but disable the button. -->
                         <!-- Let's check if I can add a Detail route later. For now, I will NOT include the Aksi column to be safe, as it wasn't requested explicitly and no route exists. -->
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    <TableRow v-for="item in dosen.data" :key="item.id">
                        <TableCell>
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ item.nama_lengkap }}</span>
                                <span class="text-xs text-slate-500">{{ item.jenis_kelamin === 'L' ? 'Laki-laki' : item.jenis_kelamin === 'P' ? 'Perempuan' : '-' }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="font-mono text-slate-600 text-sm">{{ item.nidn || '-' }}</TableCell>
                        <TableCell class="font-mono text-slate-600 text-sm">{{ item.nip || '-' }}</TableCell>
                        <TableCell>{{ item.jabatan_fungsional || '-' }}</TableCell>
                        <TableCell>{{ item.prodi || '-' }}</TableCell>
                        <TableCell class="text-center">
                            <span
                                :class="getStatusBadge(item.status_aktif)"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize"
                            >
                                {{ item.status_aktif || 'N/A' }}
                            </span>
                        </TableCell>
                    </TableRow>
                    
                    <!-- Empty State -->
                    <TableRow v-if="dosen.data.length === 0">
                        <TableCell colspan="6" class="h-64 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-500">
                                <div class="bg-slate-100 p-4 rounded-full mb-3">
                                    <Search class="h-6 w-6 text-slate-400" />
                                </div>
                                <p class="font-medium text-slate-900">Tidak ada data dosen ditemukan</p>
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
                    <Pagination :pagination="dosen" />
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
