<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useStatusBadge } from '@/composables/useStatusBadge';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Eye, FileDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import ComboboxFilter from '@/components/ui/datatable/ComboboxFilter.vue';

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
    angkatanList: string[];
    filters: Record<string, any>;
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mahasiswa', href: '/admin/mahasiswa' },
]);

const columns: any[] = [
    { key: 'nim', label: 'NIM', sortable: true },
    { key: 'nama', label: 'Nama Lengkap', sortable: true },
    { key: 'program_studi', label: 'Program Studi', sortable: true },
    { key: 'angkatan', label: 'Angkatan', sortable: true, align: 'center' },
    { key: 'ipk', label: 'IPK', sortable: true, align: 'center', render: (row: any) => row.ipk?.toFixed(2) || '-' },
    { key: 'status', label: 'Status', sortable: true, align: 'center' },
    { key: 'aksi', label: 'Aksi', align: 'right' },
];

const selectedProdi = ref(props.filters.prodi ? String(props.filters.prodi) : 'all');
const selectedStatus = ref(props.filters.status || 'all');
const selectedAngkatan = ref(props.filters.angkatan ? String(props.filters.angkatan) : 'all');

const updateFilter = () => {
    // Treat null as clearing the filter, which will default to 'all' in our logic below.
    const prodiVal = selectedProdi.value || 'all';
    const statusVal = selectedStatus.value || 'all';
    const angkatanVal = selectedAngkatan.value || 'all';

    router.get('/admin/mahasiswa', {
        prodi: prodiVal === 'all' ? undefined : prodiVal,
        status: statusVal === 'all' ? undefined : statusVal,
        angkatan: angkatanVal === 'all' ? undefined : angkatanVal,
        search: props.filters.search,
        page: 1, // Reset to page 1 on filter change
    }, { preserveState: true, preserveScroll: true });
};

// --- Options for Combobox ---
import { computed } from 'vue';

const prodiOptions = computed(() => {
    return [
        { label: 'Semua Prodi', value: 'all' },
        ...props.prodi.map(p => ({ label: p.nama_prodi, value: String(p.id) }))
    ];
});

const angkatanOptions = computed(() => {
    return [
        { label: 'Semua Angkatan', value: 'all' },
        ...props.angkatanList.map(ang => ({ label: String(ang), value: String(ang) }))
    ];
});

const statusOptions = [
    { label: 'Semua Status', value: 'all' },
    { label: 'Aktif', value: 'Aktif' },
    { label: 'Lulus', value: 'Lulus' },
    { label: 'Cuti', value: 'Cuti' },
    { label: 'Non-Aktif', value: 'Non-Aktif' },
    { label: 'Keluar', value: 'Keluar' }
];

const { getStatusBadge } = useStatusBadge();

const handleExport = () => {
    const params = new URLSearchParams(window.location.search);
    window.location.href = `/admin/mahasiswa/export?${params.toString()}`;
};

const activeFilterChips = computed(() => {
    const chips = [];
    if (props.filters.prodi && props.filters.prodi !== 'all') {
        const prod = props.prodi.find(p => String(p.id) === String(props.filters.prodi));
        if (prod) chips.push({ key: 'prodi', label: 'Prodi', valueLabel: prod.nama_prodi });
    }
    if (props.filters.angkatan && props.filters.angkatan !== 'all') {
        chips.push({ key: 'angkatan', label: 'Angkatan', valueLabel: String(props.filters.angkatan) });
    }
    if (props.filters.status && props.filters.status !== 'all') {
        chips.push({ key: 'status', label: 'Status', valueLabel: props.filters.status });
    }
    return chips;
});
</script>

<template>
    <Head title="Data Mahasiswa" />

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
                :filters="{ prodi: filters.prodi, status: filters.status, angkatan: filters.angkatan }"
                :active-filters="activeFilterChips"
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
                    <div class="flex flex-col gap-4 w-full">
                        <!-- Prodi Filter -->
                        <div class="space-y-2">
                            <Label>Pilih Program Studi</Label>
                            <ComboboxFilter
                                v-model="selectedProdi"
                                :options="prodiOptions"
                                placeholder="Semua Prodi"
                                searchPlaceholder="Cari Prodi..."
                                widthClass="w-full h-10"
                                @update:modelValue="updateFilter"
                            />
                        </div>
                        
                        <!-- Angkatan Filter -->
                        <div class="space-y-2">
                            <Label>Pilih Angkatan</Label>
                            <ComboboxFilter
                                v-model="selectedAngkatan"
                                :options="angkatanOptions"
                                placeholder="Semua Angkatan"
                                searchPlaceholder="Cari Angkatan..."
                                widthClass="w-full h-10"
                                @update:modelValue="updateFilter"
                            />
                        </div>
                        
                         <!-- Status Filter -->
                        <div class="space-y-2">
                            <Label>Pilih Status Mahasiswa</Label>
                            <ComboboxFilter
                                v-model="selectedStatus"
                                :options="statusOptions"
                                placeholder="Semua Status"
                                searchPlaceholder="Cari Status..."
                                widthClass="w-full h-10"
                                @update:modelValue="updateFilter"
                            />
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
</template>
