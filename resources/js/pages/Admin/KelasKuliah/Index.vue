<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Trash2, Eye, Users } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface KelasKuliah {
    id: number;
    id_kelas_kuliah: string;
    nama_kelas_kuliah: string | null;
    kode_mata_kuliah: string | null;
    nama_mata_kuliah: string | null;
    sks: number | null;
    kapasitas: number | null;
    prodi: string | null;
    semester: string | null;
    program_studi_id?: number;
    tahun_akademik_id?: number;
}

const props = defineProps<{
    kelasKuliah: any;
    prodiList: Record<string, string>;
    semesterList: Record<string, string>;
    filters: {
        search?: string;
        prodi?: string;
        semester?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kelas Kuliah', href: '/admin/kelas-kuliah' },
];

const columns = [
    { key: 'nama_kelas_kuliah', label: 'Nama Kelas', sortable: true },
    { key: 'kode_mata_kuliah', label: 'Kode MK', sortable: true },
    { key: 'nama_mata_kuliah', label: 'Mata Kuliah', sortable: true },
    { key: 'sks', label: 'SKS', sortable: true, align: 'center' as const },
    { key: 'kapasitas', label: 'Kapasitas', sortable: false, align: 'center' as const },
    { key: 'prodi', label: 'Program Studi', sortable: false },
    { key: 'semester', label: 'Semester', sortable: false },
    { key: 'actions', label: 'Aksi', align: 'center' as const },
];

// -- Dialogs --
const isDeleteOpen = ref(false);
const selectedItem = ref<KelasKuliah | null>(null);

const openDelete = (item: KelasKuliah) => {
    selectedItem.value = item;
    isDeleteOpen.value = true;
};

const submitDelete = () => {
    if (!selectedItem.value) return;
    router.delete(`/admin/kelas-kuliah/${selectedItem.value.id}`, {
        onSuccess: () => {
            isDeleteOpen.value = false;
            toast.success('Berhasil', { description: 'Data kelas kuliah berhasil dihapus' });
        },
    });
};
</script>

<template>
    <Head title="Kelas Kuliah" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
                title="Filter Data Kelas Kuliah"
            >
                <template #filters>
                    <!-- Prodi Filter -->
                    <div class="w-full sm:w-48">
                        <Select 
                            :model-value="filters.prodi || 'all'" 
                            @update:model-value="(val) => router.get('/admin/kelas-kuliah', { ...filters, prodi: val === 'all' ? null : String(val) }, { preserveState: true })"
                        >
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

                    <!-- Semester Filter -->
                    <div class="w-full sm:w-48">
                        <Select
                            :model-value="filters.semester || 'all'"
                            @update:model-value="(val) => router.get('/admin/kelas-kuliah', { ...filters, semester: val === 'all' ? null : String(val) }, { preserveState: true })"
                        >
                            <SelectTrigger class="h-9 w-full">
                                <SelectValue placeholder="Pilih Semester" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Semester</SelectItem>
                                <SelectItem v-for="(nama, id) in semesterList" :key="id" :value="String(id)">
                                    {{ nama }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>
                
                <template #cell-sks="{ value }">
                    <span class="font-medium">{{ value || '-' }}</span>
                </template>

                <template #cell-kapasitas="{ value }">
                    <span class="text-slate-600">{{ value || '-' }}</span>
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

        <!-- Delete Confirmation -->
        <AlertDialog :open="isDeleteOpen" @update:open="isDeleteOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Apakah anda yakin?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini tidak dapat dibatalkan. Data kelas kuliah "{{ selectedItem?.nama_kelas_kuliah }}" akan dihapus permanen dari sistem.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Batal</AlertDialogCancel>
                    <AlertDialogAction class="bg-red-600 hover:bg-red-700" @click="submitDelete">
                        Hapus
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AppLayout>
</template>
