<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();

import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import ComboboxFilter from '@/components/ui/datatable/ComboboxFilter.vue';
import { computed } from 'vue';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Trash2, Eye, Users } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import Swal from 'sweetalert2';

interface KelasKuliah {
    id: number;
    id_kelas_kuliah: string;
    nama_kelas_kuliah: string | null;
    kode_mata_kuliah: string | null;
    nama_mata_kuliah: string | null;
    sks: number | null;
    kapasitas: number | null;
    peserta: number;
    prodi: string | null;
    semester: string | null;
    program_studi_id?: number;
    tahun_akademik_id?: number;
    dosen_pengajar?: any[];
}

const props = defineProps<{
    kelasKuliah: any;
    prodiList: Record<string, string>;
    semesterList: { id: string | number; nama_semester: string }[];
    filters: {
        search?: string;
        prodi?: string;
        semester?: string;
    };
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kelas Kuliah', href: '/admin/kelas-kuliah' },
]);

const prodiOptions = computed(() => {
    return [
        { label: 'Semua Prodi', value: 'all' },
        ...Object.entries(props.prodiList).map(([id, nama]) => ({ label: String(nama), value: String(id) }))
    ];
});

const semesterOptions = computed(() => {
    return [
        { label: 'Semua Semester', value: 'all' },
        ...props.semesterList.map(sem => ({ label: sem.nama_semester, value: String(sem.id) }))
    ];
});

const columns = [
    { key: 'nama_kelas_kuliah', label: 'Nama Kelas', sortable: true },
    { key: 'kode_mata_kuliah', label: 'Kode MK', sortable: true },
    { key: 'nama_mata_kuliah', label: 'Mata Kuliah', sortable: true },
    { key: 'dosen_pengajar', label: 'Dosen Pengajar', sortable: false },
    { key: 'sks', label: 'SKS', sortable: true, align: 'center' as const },
    { key: 'peserta_kapasitas', label: 'Peserta / Kapasitas', sortable: false, align: 'center' as const },
    { key: 'prodi', label: 'Program Studi', sortable: false },
    { key: 'semester', label: 'Semester', sortable: false },
    { key: 'actions', label: 'Aksi', align: 'center' as const },
];

// -- Dialogs --
import { useConfirmDelete } from '@/composables/useConfirmDelete';
const { confirmDelete } = useConfirmDelete();

const openDelete = (item: KelasKuliah) => {
    confirmDelete({
        url: `/admin/kelas-kuliah/${item.id}`,
        entityName: 'Kelas Kuliah',
        text: `Tindakan ini tidak dapat dibatalkan. Data kelas kuliah "${item.nama_kelas_kuliah}" akan dihapus permanen dari sistem.`,
        isRestricted: (item.peserta ?? 0) > 0 || (item.dosen_pengajar?.length ?? 0) > 0,
        restrictedMessage: `Kelas ini memiliki ${item.peserta || 0} Peserta Mahasiswa dan ${item.dosen_pengajar?.length || 0} Dosen Pengajar. Data tidak dapat dihapus karena berelasi dengan data lain.`
    });
};

const activeFilterChips = computed(() => {
    const chips = [];
    if (props.filters.prodi && props.filters.prodi !== 'all') {
        const label = props.prodiList[props.filters.prodi];
        if (label) chips.push({ key: 'prodi', label: 'Prodi', valueLabel: String(label) });
    }
    if (props.filters.semester && props.filters.semester !== 'all') {
        const label = props.semesterList.find(s => String(s.id) === String(props.filters.semester))?.nama_semester;
        if (label) chips.push({ key: 'semester', label: 'Semester', valueLabel: label });
    }
    return chips;
});
</script>

<template>
    <Head title="Kelas Kuliah" />

    
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kelas Kuliah</h1>
                    <p class="text-slate-500 mt-1">Data kelas kuliah yang disinkronisasi dari Neo Feeder.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <Users class="w-4 h-4" />
                        <span>{{ kelasKuliah.total }} kelas</span>
                    </div>
                </div>
            </div>

            <SmartTable
                :data="kelasKuliah"
                :columns="columns"
                :search="filters.search"
                :filters="{ prodi: filters.prodi, semester: filters.semester }"
                :active-filters="activeFilterChips"
                title="Filter Data Kelas Kuliah"
            >
                <template #filters>
                    <div class="flex flex-col gap-4 w-full">
                        <!-- Prodi Filter -->
                        <div class="space-y-2">
                            <Label>Pilih Program Studi</Label>
                            <ComboboxFilter
                                :model-value="filters.prodi || 'all'"
                                :options="prodiOptions"
                                placeholder="Semua Prodi"
                                searchPlaceholder="Cari Prodi..."
                                widthClass="w-full h-10"
                                @update:model-value="(val) => router.get('/admin/kelas-kuliah', { ...filters, prodi: val === 'all' ? null : String(val) }, { preserveState: true })"
                            />
                        </div>

                        <!-- Semester Filter -->
                        <div class="space-y-2">
                            <Label>Pilih Semester</Label>
                            <ComboboxFilter
                                :model-value="filters.semester || 'all'"
                                :options="semesterOptions"
                                placeholder="Semua Semester"
                                searchPlaceholder="Cari Semester..."
                                widthClass="w-full h-10"
                                @update:model-value="(val) => router.get('/admin/kelas-kuliah', { ...filters, semester: val === 'all' ? null : String(val) }, { preserveState: true })"
                            />
                        </div>
                    </div>
                </template>
                
                <template #cell-dosen_pengajar="{ value }">
                    <div v-if="value && value.length > 0" class="flex flex-col gap-1">
                        <span v-for="dosen in value" :key="dosen.id" class="text-sm">
                            {{ dosen.nama }}
                        </span>
                    </div>
                    <span v-else class="text-slate-400 italic">Belum ada dosen</span>
                </template>

                <template #cell-sks="{ value }">
                    <span class="font-medium">{{ value || '-' }}</span>
                </template>

                <template #cell-peserta_kapasitas="{ row }">
                    <span class="text-slate-600">
                        <span class="font-medium text-slate-800">{{ row.peserta || 0 }}</span>
                        <span class="mx-1 text-slate-400">/</span>
                        <span>{{ row.kapasitas || '-' }}</span>
                    </span>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-center gap-2">
                        <Link :href="`/admin/kelas-kuliah/${row.id}`">
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50">
                                <Eye class="w-4 h-4" />
                            </Button>
                        </Link>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50" @click="openDelete(row)">
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </template>
            </SmartTable>
        </div>



    
</template>
