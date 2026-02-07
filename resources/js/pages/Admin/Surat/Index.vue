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
import { Filter, X, Search, Printer, Trash2, Eye, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { watchDebounced } from '@vueuse/core';

interface Mahasiswa {
    nim: string;
    nama: string;
    prodi: string | null;
}

interface Pejabat {
    id: number;
    nama: string;
    jabatan: string;
}

interface Pengajuan {
    id: number;
    nomor_surat: string | null;
    mahasiswa: Mahasiswa;
    pejabat: Pejabat | null;
    jenis_surat: string;
    jenis_surat_label: string;
    keperluan: string | null;
    status: string;
    status_label: string;
    status_badge: string;
    processed_by: string | null;
    processed_at: string | null;
    created_at: string;
}

interface PaginationData {
    data: Pengajuan[];
    links: any[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

const props = defineProps<{
    pengajuan: PaginationData;
    filters: {
        status?: string;
        jenis?: string;
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pengajuan Surat', href: '/admin/surat' },
];

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');
const selectedJenis = ref(props.filters.jenis || 'all');
const sortField = ref('created_at');
const sortDirection = ref<'asc' | 'desc'>('desc');

const applyFilters = () => {
    router.get('/admin/surat', {
        search: search.value || undefined,
        status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
        jenis: selectedJenis.value === 'all' ? undefined : selectedJenis.value,
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
    selectedStatus.value = 'all';
    selectedJenis.value = 'all';
    sortField.value = 'created_at';
    sortDirection.value = 'desc';
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

const getBadgeClass = (badge: string) => {
    const classes: Record<string, string> = {
        warning: 'bg-amber-100 text-amber-800 border-amber-200',
        success: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        danger: 'bg-red-100 text-red-800 border-red-200',
        info: 'bg-blue-100 text-blue-800 border-blue-200',
    };
    return classes[badge] || 'bg-gray-100 text-gray-800';
};

const deleteSurat = (id: number) => {
    Swal.fire({
        title: 'Apakah anda yakin?',
        text: "Data pengajuan surat akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'rounded-lg px-4 py-2',
            cancelButton: 'rounded-lg px-4 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/surat/${id}`, {
                onSuccess: () => {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Pengajuan surat berhasil dihapus.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-xl' }
                    });
                }
            });
        }
    });
};
</script>

<template>
    <Head title="Pengajuan Surat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pengajuan Surat</h1>
                    <p class="text-slate-500 mt-1">Kelola permohonan surat akademik mahasiswa.</p>
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
                            v-if="search || selectedJenis !== 'all' || selectedStatus !== 'all'"
                            variant="ghost" 
                            size="sm" 
                            @click="clearFilters"
                            class="text-red-600 hover:text-red-700 hover:bg-red-50 h-9 gap-1"
                        >
                            <X class="w-4 h-4" />
                            Clear Filters
                        </Button>

                         <div v-if="search || selectedJenis !== 'all' || selectedStatus !== 'all'" class="h-6 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                        <!-- Jenis Filter -->
                        <div class="w-full sm:w-48">
                            <Select v-model="selectedJenis" @update:modelValue="applyFilters">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Pilih Jenis" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Jenis</SelectItem>
                                    <SelectItem value="aktif_kuliah">Surat Aktif Kuliah</SelectItem>
                                    <SelectItem value="krs">KRS</SelectItem>
                                    <SelectItem value="khs">KHS</SelectItem>
                                    <SelectItem value="transkrip">Transkrip</SelectItem>
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
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="approved">Approved</SelectItem>
                                    <SelectItem value="rejected">Rejected</SelectItem>
                                    <SelectItem value="printed">Printed</SelectItem>
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
                        <TableHeader @click="sort('nomor_surat')" class="cursor-pointer hover:bg-slate-100">
                            Nomor Surat
                            <ArrowUp v-if="sortField === 'nomor_surat' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'nomor_surat' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('created_at')" class="cursor-pointer hover:bg-slate-100 text-left">
                            Mahasiswa
                            <ArrowUp v-if="sortField === 'created_at' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'created_at' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('jenis_surat')" class="cursor-pointer hover:bg-slate-100">
                            Jenis Surat
                            <ArrowUp v-if="sortField === 'jenis_surat' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'jenis_surat' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader>Pejabat Penanda Tangan</TableHeader>
                        <TableHeader @click="sort('status')" class="cursor-pointer hover:bg-slate-100">
                            Status
                            <ArrowUp v-if="sortField === 'status' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'status' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader @click="sort('created_at')" class="cursor-pointer hover:bg-slate-100">
                            Tanggal
                            <ArrowUp v-if="sortField === 'created_at' && sortDirection === 'asc'" class="ml-2 h-3 w-3" />
                            <ArrowDown v-else-if="sortField === 'created_at' && sortDirection === 'desc'" class="ml-2 h-3 w-3" />
                            <ArrowUpDown v-else class="ml-2 h-3 w-3 opacity-50" />
                        </TableHeader>
                        <TableHeader class="text-right">Aksi</TableHeader>
                    </tr>
                </thead>

                 <!-- Table Body -->
                <tbody>
                    <TableRow v-for="item in pengajuan.data" :key="item.id">
                        <TableCell class="font-mono text-slate-600">{{ item.nomor_surat || '-' }}</TableCell>
                        <TableCell>
                             <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ item.mahasiswa.nama }}</span>
                                <span class="text-xs text-slate-500">{{ item.mahasiswa.nim }}</span>
                            </div>
                        </TableCell>
                        <TableCell>{{ item.jenis_surat_label }}</TableCell>
                         <TableCell>
                            <div v-if="item.pejabat" class="flex flex-col">
                                <span class="font-medium text-slate-800">{{ item.pejabat.nama }}</span>
                                <span class="text-xs text-slate-500">{{ item.pejabat.jabatan }}</span>
                            </div>
                            <span v-else class="text-slate-400">-</span>
                        </TableCell>
                        <TableCell>
                            <span :class="getBadgeClass(item.status_badge)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border">
                                {{ item.status_label }}
                            </span>
                        </TableCell>
                        <TableCell class="text-slate-500">{{ item.created_at }}</TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <!-- Detail -->
                                <Link
                                    :href="`/admin/surat/${item.id}`"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600 transition-colors"
                                    title="Detail"
                                >
                                    <component :is="Eye" class="h-4 w-4" />
                                </Link>
                                <!-- Print (only if approved/printed) -->
                                <a
                                    v-if="item.status === 'approved' || item.status === 'printed'"
                                    :href="`/admin/surat/${item.id}/print`"
                                    target="_blank"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 transition-colors"
                                    title="Cetak"
                                >
                                    <component :is="Printer" class="h-4 w-4" />
                                </a>
                                <!-- Delete -->
                                <button
                                    @click="deleteSurat(item.id)"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                                    title="Hapus"
                                >
                                    <component :is="Trash2" class="h-4 w-4" />
                                </button>
                            </div>
                        </TableCell>
                    </TableRow>

                    <!-- Empty State -->
                     <TableRow v-if="pengajuan.data.length === 0">
                        <TableCell colspan="7" class="h-64 text-center">
                             <div class="flex flex-col items-center justify-center text-slate-500">
                                <div class="bg-slate-100 p-4 rounded-full mb-3">
                                    <Search class="h-6 w-6 text-slate-400" />
                                </div>
                                <p class="font-medium text-slate-900">Tidak ada pengajuan ditemukan</p>
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
                     <Pagination :pagination="pengajuan" />
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>
