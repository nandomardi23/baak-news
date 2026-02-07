<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Eye, Printer, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

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

const props = defineProps<{
    pengajuan: any;
    filters: Record<string, any>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Layanan Surat', href: '/admin/surat' },
];

const columns = [
    { key: 'created_at', label: 'Tanggal', sortable: true },
    { key: 'nomor_surat', label: 'Nomor Surat', sortable: true },
    { key: 'mahasiswa', label: 'Mahasiswa', sortable: false }, // Custom render
    { key: 'jenis_surat', label: 'Jenis Surat', sortable: true }, // Using value derived from row
    { key: 'status', label: 'Status', sortable: true, align: 'center' },
    { key: 'aksi', label: 'Aksi', align: 'right' },
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

const deleteSurat = (id: number) => {
    if (confirm('Hapus pengajuan surat ini?')) {
        router.delete(`/admin/surat/${id}`);
    }
};

const getBadgeClass = (badge: string) => {
    // Mapping backend badge classes to Tailwind if needed, or using directly
    // Assuming backend returns full tailwind classes string like 'bg-green-100 ...'
    return badge;
};
</script>

<template>
    <Head title="Layanan Surat" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
                :sort-field="filters.sort_field"
                :sort-direction="filters.sort_direction"
                title="Layanan Surat"
            >
                <template #filters>
                    <div class="flex gap-2 w-full sm:w-auto">
                         <!-- Status Filter -->
                         <div class="w-full sm:w-32">
                             <Select v-model="selectedStatus" @update:modelValue="updateFilter">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="pending">Pending</SelectItem>
                                    <SelectItem value="approved">Disetujui</SelectItem>
                                    <SelectItem value="printed">Dicetak</SelectItem>
                                    <SelectItem value="rejected">Ditolak</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <!-- Jenis Filter -->
                        <div class="w-full sm:w-40">
                             <Select v-model="selectedJenis" @update:modelValue="updateFilter">
                                <SelectTrigger class="h-9 w-full">
                                    <SelectValue placeholder="Jenis Surat" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Jenis</SelectItem>
                                    <SelectItem value="aktif_kuliah">Aktif Kuliah</SelectItem>
                                    <SelectItem value="krs">Kartu Rencana Studi</SelectItem>
                                    <SelectItem value="khs">Kartu Hasil Studi</SelectItem>
                                    <SelectItem value="transkrip">Transkrip Nilai</SelectItem>
                                    <SelectItem value="kartu_ujian">Kartu Ujian</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </template>

                <!-- Custom Cell: Mahasiswa -->
                <template #cell-mahasiswa="{ row }">
                    <div>
                        <div class="font-medium text-slate-900">{{ row.mahasiswa.nama }}</div>
                        <div class="text-xs text-slate-500">{{ row.mahasiswa.nim }}</div>
                        <div class="text-xs text-slate-400">{{ row.mahasiswa.prodi }}</div>
                    </div>
                </template>

                 <!-- Custom Cell: Jenis Surat -->
                <template #cell-jenis_surat="{ row }">
                     {{ row.jenis_surat_label }}
                </template>

                 <!-- Custom Cell: Status -->
                 <template #cell-status="{ row }">
                    <span 
                        :class="getBadgeClass(row.status_badge)" 
                        class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
                    >
                        {{ row.status_label }}
                    </span>
                </template>

                <!-- Custom Cell: Aksi -->
                <template #cell-aksi="{ row }">
                     <div class="flex items-center justify-end gap-1">
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
                        
                        <a 
                            v-if="row.status === 'approved' || row.status === 'printed'" 
                            :href="`/admin/surat/${row.id}/print`" 
                            target="_blank"
                        >
                            <Button
                                variant="ghost"
                                size="icon"
                                class="text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 h-8 w-8"
                                title="Cetak"
                            >
                                <Printer class="w-4 h-4" />
                            </Button>
                        </a>

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
    </AppLayout>
</template>
