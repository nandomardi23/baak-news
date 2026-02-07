<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { Pencil, Trash2, Plus, Check } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface MataKuliah {
    id: number;
    kode_matkul: string;
    nama_matkul: string;
    sks_mata_kuliah: number;
    sks_teori: number | null;
    sks_praktek: number | null;
    prodi: string | null;
    id_prodi: string | null;
}

interface Prodi {
    id: number;
    id_prodi: string;
    nama_prodi: string;
}

const props = defineProps<{
    mataKuliah: any;
    prodiList: Prodi[];
    filters: {
        prodi?: string;
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mata Kuliah', href: '/admin/akademik/matakuliah' },
];

const columns = [
    { key: 'kode_matkul', label: 'Kode', sortable: true, class: 'font-mono' },
    { key: 'nama_matkul', label: 'Nama Mata Kuliah', sortable: true },
    { key: 'sks_mata_kuliah', label: 'SKS', sortable: true, align: 'center' as const },
    { key: 'sks_teori', label: 'Teori', sortable: true, align: 'center' as const },
    { key: 'sks_praktek', label: 'Praktek', sortable: true, align: 'center' as const },
    { key: 'prodi', label: 'Program Studi', sortable: false },
    { key: 'actions', label: 'Aksi', align: 'center' as const },
];

// -- Forms & Dialogs --

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteOpen = ref(false);
const selectedItem = ref<MataKuliah | null>(null);

const form = useForm({
    kode_matkul: '',
    nama_matkul: '',
    sks_mata_kuliah: 0,
    sks_teori: 0,
    sks_praktek: 0,
    id_prodi: null as string | number | null,
});

const openCreate = () => {
    form.reset();
    isCreateOpen.value = true;
};

const openEdit = (item: MataKuliah) => {
    selectedItem.value = item;
    form.kode_matkul = item.kode_matkul;
    form.nama_matkul = item.nama_matkul;
    form.sks_mata_kuliah = item.sks_mata_kuliah;
    form.sks_teori = item.sks_teori || 0;
    form.sks_praktek = item.sks_praktek || 0;
    form.id_prodi = item.id_prodi || null;
    isEditOpen.value = true;
};

const openDelete = (item: MataKuliah) => {
    selectedItem.value = item;
    isDeleteOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.akademik.matakuliah.store'), {
        onSuccess: () => {
            isCreateOpen.value = false;
            toast.success('Berhasil', { description: 'Mata Kuliah berhasil ditambahkan' });
        },
    });
};

const submitEdit = () => {
    if (!selectedItem.value) return;
    form.put(route('admin.akademik.matakuliah.update', selectedItem.value.id), {
        onSuccess: () => {
            isEditOpen.value = false;
            toast.success('Berhasil', { description: 'Mata Kuliah berhasil diperbarui' });
        },
    });
};

const submitDelete = () => {
    if (!selectedItem.value) return;
    router.delete(route('admin.akademik.matakuliah.destroy', selectedItem.value.id), {
        onSuccess: () => {
            isDeleteOpen.value = false;
            toast.success('Berhasil', { description: 'Mata Kuliah berhasil dihapus' });
        },
    });
};
</script>

<template>
    <Head title="Mata Kuliah" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Mata Kuliah</h1>
                    <p class="text-slate-500 mt-1">Kelola data mata kuliah, SKS, dan program studi.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-xs font-medium flex items-center gap-1 justify-center sm:justify-start">
                        <Check class="w-3 h-3" />
                        Data Terintegrasi Neo Feeder
                    </div>
                    <Button @click="openCreate" class="gap-2">
                        <Plus class="w-4 h-4" />
                        Tambah Matkul
                    </Button>
                </div>
            </div>

            <SmartTable
                :data="mataKuliah"
                :columns="columns"
                :search="filters.search"
                :filters="{ prodi: filters.prodi }"
                :sort-field="filters.prodi"
                title="Filter Mata Kuliah"
            >
                <template #filters>
                    <!-- Prodi Filter -->
                    <div class="w-full sm:w-60">
                        <Select 
                            :model-value="filters.prodi || 'all'" 
                            @update:model-value="(val) => router.get('/admin/akademik/matakuliah', { ...filters, prodi: val === 'all' ? null : String(val) }, { preserveState: true })"
                        >
                            <SelectTrigger class="h-9 w-full">
                                <SelectValue placeholder="Pilih Prodi" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Program Studi</SelectItem>
                                <SelectItem v-for="prodi in prodiList" :key="prodi.id" :value="prodi.id_prodi">
                                    {{ prodi.nama_prodi }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>
                
                <template #cell-actions="{ row }">
                     <div class="flex items-center justify-center gap-2">
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 hover:text-blue-700 hover:bg-blue-50" @click="openEdit(row)">
                            <Pencil class="w-4 h-4" />
                        </Button>
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 hover:text-red-700 hover:bg-red-50" @click="openDelete(row)">
                            <Trash2 class="w-4 h-4" />
                        </Button>
                    </div>
                </template>
            </SmartTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog :open="isCreateOpen || isEditOpen" @update:open="(val) => { if(!val) { isCreateOpen = false; isEditOpen = false; } }">
            <DialogContent class="sm:max-w-[600px]">
                <DialogHeader>
                    <DialogTitle>{{ isEditOpen ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditOpen ? 'Perbarui data mata kuliah di sini.' : 'Tambahkan data mata kuliah baru.' }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1 space-y-2">
                            <Label>Kode Matkul</Label>
                            <Input v-model="form.kode_matkul" placeholder="Contoh: IF1234" />
                        </div>
                        <div class="col-span-2 space-y-2">
                            <Label>Nama Mata Kuliah</Label>
                            <Input v-model="form.nama_matkul" placeholder="Contoh: Pemrograman Web" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <Label>SKS Total</Label>
                            <Input type="number" v-model="form.sks_mata_kuliah" min="0" />
                        </div>
                        <div class="space-y-2">
                             <Label>SKS Teori</Label>
                            <Input type="number" v-model="form.sks_teori" min="0" />
                        </div>
                        <div class="space-y-2">
                             <Label>SKS Praktek</Label>
                            <Input type="number" v-model="form.sks_praktek" min="0" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label>Program Studi</Label>
                        <Select v-model="form.id_prodi">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Program Studi" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="prodi in prodiList" :key="prodi.id" :value="prodi.id_prodi">
                                    {{ prodi.nama_prodi }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isCreateOpen = false; isEditOpen = false">Batal</Button>
                    <Button @click="isEditOpen ? submitEdit() : submitCreate()">
                        {{ isEditOpen ? 'Simpan Perubahan' : 'Tambah Matkul' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation -->
        <AlertDialog :open="isDeleteOpen" @update:open="isDeleteOpen = $event">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Apakah anda yakin?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Tindakan ini tidak dapat dibatalkan. Data mata kuliah ini akan dihapus permanen.
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
