<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Eye, FileDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
    program_studi: string;
    angkatan: string;
    status: string;
    ipk: number | null;
}

const props = defineProps<{
    mahasiswa: any;
    prodi: { id: number; nama_prodi: string }[];
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mahasiswa', href: '/admin/mahasiswa' },
];

const columns = [
    { key: 'nim', label: 'NIM', sortable: true },
    { key: 'nama', label: 'Nama Lengkap', sortable: true },
    { key: 'program_studi', label: 'Program Studi', sortable: true }, // Note: sorting by relation in Controller needs mapping if strict key matching
    { key: 'angkatan', label: 'Angkatan', sortable: true, align: 'center' },
    { key: 'ipk', label: 'IPK', sortable: true, align: 'center', render: (row: any) => row.ipk?.toFixed(2) || '-' },
    { key: 'status', label: 'Status', sortable: true, align: 'center' },
    { key: 'aksi', label: 'Aksi', align: 'right' },
];

const selectedProdi = ref(props.filters.prodi ? Number(props.filters.prodi) : 'all');
const selectedStatus = ref(props.filters.status || 'all');

const updateFilter = () => {
    router.get('/admin/mahasiswa', {
        prodi: selectedProdi.value === 'all' ? undefined : selectedProdi.value,
        status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
        search: props.filters.search,
    }, { preserveState: true, preserveScroll: true });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'Aktif': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'Lulus': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'Cuti': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'Non-Aktif': return 'bg-red-100 text-red-800 border-red-200';
        case 'Keluar': return 'bg-gray-100 text-gray-800 border-gray-200';
        default: return 'bg-slate-100 text-slate-800 border-slate-200';
    }
};

const handleExport = () => {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/admin/mahasiswa/export?${params.toString()}`;
};
</script>

<template>
    <Head title="Data Mahasiswa" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Data Mahasiswa</h1>
                    <p class="text-muted-foreground">Kelola data induk mahasiswa</p>
                </div>
            </div>

            <SmartTable
                :data="mahasiswa"
                :columns="columns"
                :search="filters.search"
                :filters="{ prodi: filters.prodi, status: filters.status }"
                :sort-field="filters.sort_field"
                :sort-direction="filters.sort_direction"
                title="Data Mahasiswa"
            >
                <template #actions>
                    <Button variant="outline" @click="handleExport">
                        <FileDown class="w-4 h-4 mr-2" />
                        Export Excel
                    </Button>
                </template>

                <template #filters>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <!-- Prodi Filter -->
                        <div class="w-full sm:w-48">
                            <Select v-model="selectedProdi" @update:modelValue="updateFilter">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Pilih Prodi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Prodi</SelectItem>
                                    <SelectItem v-for="p in prodi" :key="p.id" :value="p.id">
                                        {{ p.nama_prodi }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        
                         <!-- Status Filter -->
                        <div class="w-full sm:w-32">
                             <Select v-model="selectedStatus" @update:modelValue="updateFilter">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="A">Aktif</SelectItem> // Assuming 'A' is code, but display text used in badge. Check controller if strictly 'A' or 'Aktif'
                                    <SelectItem value="L">Lulus</SelectItem>
                                    <SelectItem value="C">Cuti</SelectItem>
                                    <SelectItem value="N">Non-Aktif</SelectItem>
                                    <SelectItem value="K">Keluar</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </template>

                <template #cell-status="{ value }">
                    <span 
                        :class="getStatusBadge(value)" 
                        class="px-2 py-0.5 rounded-full text-xs font-bold border"
                    >
                        {{ value }}
                    </span>
                </template>

                <template #cell-aksi="{ row }">
                     <div class="flex items-center justify-end">
                        <Link :href="`/admin/mahasiswa/${row.id}`">
                             <Button
                                variant="ghost"
                                size="icon"
                                class="text-slate-500 hover:text-blue-600 hover:bg-slate-50 h-8 w-8"
                                title="Detail"
                            >
                                <Eye class="w-4 h-4" />
                            </Button>
                        </Link>
                     </div>
                </template>
            </SmartTable>
        </div>
    </AppLayout>
</template>
