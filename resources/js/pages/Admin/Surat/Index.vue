<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useStatusBadge } from '@/composables/useStatusBadge';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Eye, Printer, Trash2, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import ComboboxFilter from '@/components/ui/datatable/ComboboxFilter.vue';
import Swal from 'sweetalert2';

interface Surat {
    id: number;
    nomor_surat: string;
    mahasiswa: { nim: string; nama: string; prodi: string };
    jenis_surat: string;
    jenis_surat_label: string;
    status: string;
    status_label: string;
    status_badge: string;
    created_at: string;
}

interface Pejabat {
    id: number;
    nama: string;
    jabatan: string;
    label: string;
}

const props = defineProps<{
    pengajuan: any;
    filters: Record<string, any>;
    pejabatList: Pejabat[];
}>();

const selectedSignerId = ref<string | number | null>('default');
const printModalSurat = ref<Surat | null>(null);

const pejabatOptions = computed(() => [
    { label: 'Default (Ketua)', value: 'default' },
    ...props.pejabatList.map(p => ({ label: p.label, value: p.id }))
]);

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Layanan Surat', href: '/admin/surat' },
]);

const columns = [
    { key: 'created_at', label: 'Tanggal', sortable: true },
    { key: 'nomor_surat', label: 'Nomor Surat', sortable: true },
    { key: 'mahasiswa', label: 'Mahasiswa', sortable: false }, // Custom render
    { key: 'jenis_surat', label: 'Jenis Surat', sortable: true }, // Using value derived from row
    { key: 'status', label: 'Status', sortable: true },
    { key: 'aksi', label: 'Aksi' },
];

const selectedStatus = ref(props.filters.status || 'all');
const selectedJenis = ref(props.filters.jenis || 'all');

const updateFilter = () => {
    router.get('/admin/surat', {
        status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
        jenis: selectedJenis.value === 'all' ? undefined : selectedJenis.value,
        search: props.filters.search,
    }, { preserveState: true, preserveScroll: true });
};

const statusOptions = [
    { label: 'Semua Status', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Disetujui', value: 'approved' },
    { label: 'Dicetak', value: 'printed' },
    { label: 'Ditolak', value: 'rejected' }
];

const jenisOptions = [
    { label: 'Semua Jenis', value: 'all' },
    { label: 'Aktif Kuliah', value: 'aktif_kuliah' },
    { label: 'Kartu Rencana Studi', value: 'krs' },
    { label: 'Kartu Hasil Studi', value: 'khs' },
    { label: 'Transkrip Nilai', value: 'transkrip' },
    { label: 'Kartu Ujian', value: 'kartu_ujian' }
];

import { useConfirmDelete } from '@/composables/useConfirmDelete';
const { confirmDelete } = useConfirmDelete();

const deleteSurat = (id: number) => {
    confirmDelete({
        url: `/admin/surat/${id}`,
        entityName: 'Pengajuan Surat',
        text: 'Hapus pengajuan surat ini? Tindakan ini tidak dapat dibatalkan.',
    });
};

const openPrintModal = (row: Surat) => {
    printModalSurat.value = row;
    selectedSignerId.value = 'default';
};

const closePrintModal = () => {
    printModalSurat.value = null;
    selectedSignerId.value = null;
};

const printUrl = computed(() => {
    if (!printModalSurat.value) return '#';
    const base = `/admin/surat/${printModalSurat.value.id}/print`;
    const signerId = selectedSignerId.value !== 'default' ? selectedSignerId.value : null;
    return signerId ? `${base}?signer_id=${signerId}` : base;
});

const { getBadgeClass } = useStatusBadge();

const activeFilterChips = computed(() => {
    const chips = [];
    if (props.filters.status && props.filters.status !== 'all') {
        const label = statusOptions.find(s => s.value === props.filters.status)?.label;
        if (label) chips.push({ key: 'status', label: 'Status', valueLabel: String(label) });
    }
    if (props.filters.jenis && props.filters.jenis !== 'all') {
        const label = jenisOptions.find(j => j.value === props.filters.jenis)?.label;
        if (label) chips.push({ key: 'jenis', label: 'Jenis', valueLabel: String(label) });
    }
    return chips;
});
</script>

<template>
    <Head title="Layanan Surat" />

    
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Layanan Surat</h1>
                    <p class="text-muted-foreground">Daftar pengajuan surat mahasiswa</p>
                </div>
            </div>

            <SmartTable
                :data="pengajuan"
                :columns="columns"
                :search="filters.search"
                :filters="{ status: filters.status, jenis: filters.jenis }"
                :active-filters="activeFilterChips"
                :sort-field="filters.sort_field"
                :sort-direction="filters.sort_direction"
                title="Layanan Surat"
            >
                <template #filters>
                    <div class="flex flex-col gap-4 w-full">
                         <!-- Status Filter -->
                        <div class="space-y-2">
                            <Label>Pilih Status</Label>
                            <ComboboxFilter
                                v-model="selectedStatus"
                                :options="statusOptions"
                                placeholder="Semua Status"
                                searchPlaceholder="Cari Status..."
                                widthClass="w-full h-10"
                                @update:modelValue="updateFilter"
                            />
                        </div>

                        <!-- Jenis Filter -->
                        <div class="space-y-2">
                            <Label>Pilih Jenis Surat</Label>
                            <ComboboxFilter
                                v-model="selectedJenis"
                                :options="jenisOptions"
                                placeholder="Semua Jenis"
                                searchPlaceholder="Cari Jenis Surat..."
                                widthClass="w-full h-10"
                                @update:modelValue="updateFilter"
                            />
                        </div>
                    </div>
                </template>

                <!-- Custom Cell: Nomor Surat -->
                <template #cell-nomor_surat="{ row }">
                    <div v-if="row.nomor_surat && !row.nomor_surat.startsWith('/')" class="inline-flex items-center px-2 py-1 rounded-md bg-slate-50 border border-slate-200 font-mono text-xs font-medium text-slate-700">
                        {{ row.nomor_surat }}
                    </div>
                    <span v-else class="text-xs text-slate-400 italic flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                        {{ row.nomor_surat || 'Belum digenerate' }}
                    </span>
                </template>

                <!-- Custom Cell: Mahasiswa -->
                <template #cell-mahasiswa="{ row }">
                    <div class="flex flex-col py-1">
                        <div class="font-semibold text-sm text-slate-900 mb-0.5">{{ row.mahasiswa.nama }}</div>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200 text-slate-600">{{ row.mahasiswa.nim }}</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-slate-500">{{ row.mahasiswa.prodi }}</span>
                        </div>
                    </div>
                </template>

                 <!-- Custom Cell: Jenis Surat -->
                <template #cell-jenis_surat="{ row }">
                     <span class="text-sm text-slate-700">{{ row.jenis_surat_label }}</span>
                </template>

                 <!-- Custom Cell: Status -->
                 <template #cell-status="{ row }">
                    <div class="flex justify-center">
                        <span 
                            :class="getBadgeClass(row.status_badge)" 
                            class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide border shadow-sm min-w-[80px]"
                        >
                            {{ row.status_label }}
                        </span>
                    </div>
                </template>

                <!-- Custom Cell: Aksi -->
                <template #cell-aksi="{ row }">
                     <div class="flex items-center justify-center gap-1">
                        <Link :href="`/admin/surat/${row.id}`">
                             <Button
                                variant="ghost"
                                size="icon"
                                class="text-slate-500 hover:text-blue-600 hover:bg-slate-50 h-8 w-8"
                                title="Detail"
                            >
                                <Eye class="w-4 h-4" />
                            </Button>
                        </Link>
                        
                        <!-- Print button: opens modal to pick signer -->
                        <Button
                            v-if="row.status === 'approved' || row.status === 'printed'" 
                            variant="ghost"
                            size="icon"
                            class="text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 h-8 w-8"
                            title="Cetak"
                            @click="openPrintModal(row)"
                        >
                            <Printer class="w-4 h-4" />
                        </Button>

                        <Button
                            variant="ghost"
                            size="icon"
                            @click="deleteSurat(row.id)"
                            class="text-slate-500 hover:text-red-600 hover:bg-red-50 h-8 w-8"
                            title="Hapus"
                        >
                            <Trash2 class="w-4 h-4" />
                        </Button>
                     </div>
                </template>
            </SmartTable>
        </div>

    <!-- Print Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="printModalSurat" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closePrintModal">
                <div class="bg-card rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b bg-muted/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <Printer class="w-4 h-4" />
                            </div>
                            <h3 class="text-base font-bold">Cetak Surat</h3>
                        </div>
                        <button @click="closePrintModal" class="p-1 rounded-lg hover:bg-muted transition">
                            <X class="w-4 h-4 text-muted-foreground" />
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <!-- Surat Info -->
                        <div class="p-3 bg-muted/50 rounded-xl space-y-1">
                            <p class="font-semibold text-sm">{{ printModalSurat.mahasiswa.nama }}</p>
                            <p class="text-xs text-muted-foreground font-mono">{{ printModalSurat.mahasiswa.nim }}</p>
                            <p class="text-xs text-muted-foreground">{{ printModalSurat.jenis_surat_label }}</p>
                        </div>

                        <!-- Pejabat Selection -->
                        <div>
                            <label class="text-xs text-muted-foreground font-medium mb-1.5 block">Pejabat Penandatangan</label>
                            <ComboboxFilter
                                v-model="selectedSignerId"
                                :options="pejabatOptions"
                                placeholder="Pilih Pejabat"
                                searchPlaceholder="Cari pejabat..."
                                widthClass="w-full h-11"
                            />
                        </div>

                        <!-- Print Button -->
                        <a
                            :href="printUrl"
                            target="_blank"
                            @click="closePrintModal"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all active:scale-[0.98]"
                        >
                            <Printer class="w-4 h-4" />
                            Cetak Surat
                        </a>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
