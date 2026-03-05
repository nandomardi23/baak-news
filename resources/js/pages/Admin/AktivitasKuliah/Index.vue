<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { BarChart3, TrendingUp, BookOpen, GraduationCap } from 'lucide-vue-next';

interface AktivitasKuliahItem {
    id: number;
    nim: string;
    nama_mahasiswa: string;
    semester: string | null;
    id_semester: string;
    prodi: string | null;
    status: string;
    id_status_mahasiswa: string | null;
    ips: number | null;
    ipk: number | null;
    sks_semester: number | null;
    sks_total: number | null;
}

const props = defineProps<{
    aktivitasKuliah: any;
    prodi: { id: number; nama_prodi: string }[];
    semesters: { id_semester: string; nama_semester: string }[];
    stats: { total: number; rata_ipk: number; rata_ips: number };
    filters: {
        search?: string;
        prodi?: string;
        semester?: string;
        status?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Aktivitas Kuliah', href: '/admin/akademik/aktivitas-kuliah' },
];

const columns = [
    { key: 'nim', label: 'NIM', sortable: true },
    { key: 'nama_mahasiswa', label: 'Nama Mahasiswa', sortable: true },
    { key: 'semester', label: 'Semester', sortable: false },
    { key: 'prodi', label: 'Program Studi', sortable: false },
    { key: 'ips', label: 'IPS', sortable: true, align: 'center' as const },
    { key: 'ipk', label: 'IPK', sortable: true, align: 'center' as const },
    { key: 'sks_semester', label: 'SKS Smt', sortable: true, align: 'center' as const },
    { key: 'sks_total', label: 'SKS Total', sortable: true, align: 'center' as const },
    { key: 'status', label: 'Status', sortable: false, align: 'center' as const },
];

const basePath = '/admin/akademik/aktivitas-kuliah';

const applyFilter = (key: string, val: string | null) => {
    router.get(basePath, { ...props.filters, [key]: val }, { preserveState: true });
};

const statusOptions = [
    { value: 'A', label: 'Aktif' },
    { value: 'C', label: 'Cuti' },
    { value: 'D', label: 'Drop Out' },
    { value: 'K', label: 'Keluar' },
    { value: 'L', label: 'Lulus' },
    { value: 'N', label: 'Non-Aktif' },
];
</script>

<template>
    <Head title="Aktivitas Kuliah" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Aktivitas Kuliah</h1>
                    <p class="text-slate-500 mt-1">Data aktivitas perkuliahan mahasiswa (IPS, IPK, SKS) dari Neo Feeder.</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Record</p>
                        <p class="text-xl font-bold text-slate-900">{{ stats.total.toLocaleString('id-ID') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <TrendingUp class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Rata-rata IPK</p>
                        <p class="text-xl font-bold text-slate-900">{{ stats.rata_ipk }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <BarChart3 class="h-5 w-5" />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Rata-rata IPS</p>
                        <p class="text-xl font-bold text-slate-900">{{ stats.rata_ips }}</p>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <SmartTable
                :data="aktivitasKuliah"
                :columns="columns"
                :search="filters.search"
                :filters="{ prodi: filters.prodi, semester: filters.semester, status: filters.status }"
                :base-url="basePath"
                title="Filter Data Aktivitas Kuliah"
            >
                <template #filters>
                    <!-- Semester Filter -->
                    <div class="w-full sm:w-48">
                        <Select
                            :model-value="filters.semester || 'all'"
                            @update:model-value="(val) => applyFilter('semester', val === 'all' ? null : String(val))"
                        >
                            <SelectTrigger class="h-9 w-full">
                                <SelectValue placeholder="Pilih Semester" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Semester</SelectItem>
                                <SelectItem v-for="sem in semesters" :key="sem.id_semester" :value="String(sem.id_semester)">
                                    {{ sem.nama_semester }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Prodi Filter -->
                    <div class="w-full sm:w-48">
                        <Select
                            :model-value="filters.prodi || 'all'"
                            @update:model-value="(val) => applyFilter('prodi', val === 'all' ? null : String(val))"
                        >
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
                    <div class="w-full sm:w-40">
                        <Select
                            :model-value="filters.status || 'all'"
                            @update:model-value="(val) => applyFilter('status', val === 'all' ? null : String(val))"
                        >
                            <SelectTrigger class="h-9 w-full">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Status</SelectItem>
                                <SelectItem v-for="s in statusOptions" :key="s.value" :value="s.value">
                                    {{ s.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <!-- IPS cell -->
                <template #cell-ips="{ value }">
                    <span class="font-medium" :class="value !== null ? 'text-slate-900' : 'text-slate-400'">
                        {{ value !== null ? value.toFixed(2) : '-' }}
                    </span>
                </template>

                <!-- IPK cell -->
                <template #cell-ipk="{ value }">
                    <span class="font-semibold" :class="{
                        'text-emerald-600': value !== null && value >= 3.0,
                        'text-amber-600': value !== null && value >= 2.0 && value < 3.0,
                        'text-red-600': value !== null && value < 2.0,
                        'text-slate-400': value === null,
                    }">
                        {{ value !== null ? value.toFixed(2) : '-' }}
                    </span>
                </template>

                <!-- SKS Semester cell -->
                <template #cell-sks_semester="{ value }">
                    <span class="font-medium text-slate-700">{{ value ?? '-' }}</span>
                </template>

                <!-- SKS Total cell -->
                <template #cell-sks_total="{ value }">
                    <span class="font-medium text-slate-700">{{ value ?? '-' }}</span>
                </template>

                <!-- Status cell -->
                <template #cell-status="{ value, row }">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="{
                        'bg-emerald-50 text-emerald-700': row.id_status_mahasiswa === 'A',
                        'bg-amber-50 text-amber-700': row.id_status_mahasiswa === 'C',
                        'bg-red-50 text-red-700': row.id_status_mahasiswa === 'D' || row.id_status_mahasiswa === 'K',
                        'bg-blue-50 text-blue-700': row.id_status_mahasiswa === 'L',
                        'bg-slate-100 text-slate-600': !['A', 'C', 'D', 'K', 'L'].includes(row.id_status_mahasiswa),
                    }">
                        {{ value }}
                    </span>
                </template>
            </SmartTable>
        </div>
    </AppLayout>
</template>
