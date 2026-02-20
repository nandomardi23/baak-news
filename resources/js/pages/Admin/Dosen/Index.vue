<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, computed } from 'vue';
import { useStatusBadge } from '@/composables/useStatusBadge';
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
import { Pencil, Trash2, Plus, Eye } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

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
    program_studi_id?: number; 
}

const props = defineProps<{
    dosen: any;
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

const columns = [
    { key: 'nama_lengkap', label: 'Nama Dosen', sortable: true },
    { key: 'nidn', label: 'NIDN', sortable: true },
    { key: 'nip', label: 'NIP', sortable: true },
    { key: 'jabatan_fungsional', label: 'Jabatan', sortable: true },
    { key: 'prodi', label: 'Program Studi', sortable: false },
    { key: 'status_aktif', label: 'Status', sortable: true, align: 'center' as const },
    { key: 'actions', label: 'Aksi', align: 'center' as const },
];

// -- Forms & Dialogs --

const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteOpen = ref(false);
const selectedItem = ref<Dosen | null>(null);

const form = useForm({
    nidn: '',
    nip: '',
    nama_dosen: '',
    nama_lengkap: '',
    jenis_kelamin: 'L',
    jabatan_fungsional: '',
    program_studi_id: null as string | null,
    status_aktif: 'Aktif',
});

const openCreate = () => {
    form.reset();
    isCreateOpen.value = true;
};

const openEdit = (item: Dosen) => {
    selectedItem.value = item;
    form.nidn = item.nidn || '';
    form.nip = item.nip || '';
    form.nama_dosen = item.nama; // 'nama' is what's used in index mapping for 'nama'
    form.nama_lengkap = item.nama_lengkap;
    form.jenis_kelamin = item.jenis_kelamin || 'L';
    form.jabatan_fungsional = item.jabatan_fungsional || '';
    // We need program_studi_id. Since the controller currently only provides 'prodi' name, 
    // we might have an issue here. I'll rely on the assumption I'll fix the controller shortly.
    // If not fixed, this will be null and the select won't show the current value.
    form.program_studi_id = item.program_studi_id ? String(item.program_studi_id) : null;
    form.status_aktif = item.status_aktif || 'Aktif';
    isEditOpen.value = true;
};

const openDelete = (item: Dosen) => {
    selectedItem.value = item;
    isDeleteOpen.value = true;
};

const submitCreate = () => {
    form.post(route('admin.dosen.store'), {
        onSuccess: () => {
            isCreateOpen.value = false;
            toast.success('Berhasil', { description: 'Data dosen berhasil ditambahkan' });
        },
    });
};

const submitEdit = () => {
    if (!selectedItem.value) return;
    form.put(route('admin.dosen.update', selectedItem.value.id), {
        onSuccess: () => {
            isEditOpen.value = false;
            toast.success('Berhasil', { description: 'Data dosen berhasil diperbarui' });
        },
    });
};

const submitDelete = () => {
    if (!selectedItem.value) return;
    router.delete(route('admin.dosen.destroy', selectedItem.value.id), {
        onSuccess: () => {
            isDeleteOpen.value = false;
            toast.success('Berhasil', { description: 'Data dosen berhasil dihapus' });
        },
    });
};

const { getStatusBadge } = useStatusBadge();
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
                    <Button @click="openCreate" class="gap-2">
                        <Plus class="w-4 h-4" />
                        Tambah Dosen
                    </Button>
                </div>
            </div>

            <SmartTable
                :data="dosen"
                :columns="columns"
                :search="filters.search"
                :filters="{ prodi: filters.prodi, status: filters.status }"
                :sort-field="filters.prodi" 
                title="Filter Data Dosen"
            >
                <template #filters>
                     <!-- Prodi Filter -->
                    <div class="w-full sm:w-48">
                         <!-- SmartTable handles generic filters via slots if manual implementation needed, 
                              but actually SmartTable implementation expects us to handle filters OUTSIDE via its slots. 
                              Wait, looking at SmartTable.vue, it renders slots named 'filters'. 
                              And emits update:filters. But here we usually bind v-model to props or router. 
                              The 'SmartTable' doesn't auto-generate Selects. We must provide them. 
                              The previous implementation had them manual. I will re-implement them here. -->
                        <Select 
                            :model-value="filters.prodi || 'all'" 
                            @update:model-value="(val) => router.get('/admin/dosen', { ...filters, prodi: val === 'all' ? null : String(val) }, { preserveState: true })"
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

                    <!-- Status Filter -->
                    <div class="w-full sm:w-36">
                        <Select
                            :model-value="filters.status || 'all'"
                            @update:model-value="(val) => router.get('/admin/dosen', { ...filters, status: val === 'all' ? null : String(val) }, { preserveState: true })"
                        >
                            <SelectTrigger class="h-9 w-full">
                                <SelectValue placeholder="Pilih Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Status</SelectItem>
                                <SelectItem value="aktif">Aktif</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>
                
                <template #cell-status_aktif="{ value }">
                    <span
                        :class="getStatusBadge(value)"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize"
                    >
                        {{ value || 'N/A' }}
                    </span>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-center gap-2">
                        <!-- Action Buttons -->
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
                    <DialogTitle>{{ isEditOpen ? 'Edit Dosen' : 'Tambah Dosen' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditOpen ? 'Perbarui data dosen di sini.' : 'Tambahkan data dosen baru ke sistem.' }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Nama Dosen (Tanpa Gelar)</Label>
                            <Input v-model="form.nama_dosen" placeholder="Contoh: Budi Santoso" />
                        </div>
                        <div class="space-y-2">
                             <Label>Nama Lengkap (Dengan Gelar)</Label>
                            <Input v-model="form.nama_lengkap" placeholder="Contoh: Dr. Budi Santoso, M.Kom" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>NIDN</Label>
                            <Input v-model="form.nidn" placeholder="Nomor Induk Dosen Nasional" />
                        </div>
                        <div class="space-y-2">
                             <Label>NIP</Label>
                            <Input v-model="form.nip" placeholder="Nomor Induk Pegawai" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                         <div class="space-y-2">
                            <Label>Jenis Kelamin</Label>
                            <Select v-model="form.jenis_kelamin">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih JK" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="L">Laki-laki</SelectItem>
                                    <SelectItem value="P">Perempuan</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                         <div class="space-y-2">
                            <Label>Program Studi</Label>
                             <Select v-model="form.program_studi_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Prodi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="(nama, id) in prodiList" :key="id" :value="String(id)">
                                        {{ nama }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                     <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Jabatan Fungsional</Label>
                             <Input v-model="form.jabatan_fungsional" placeholder="Contoh: Lektor" />
                        </div>
                         <div class="space-y-2">
                            <Label>Status Aktif</Label>
                            <Select v-model="form.status_aktif">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Aktif">Aktif</SelectItem>
                                    <SelectItem value="Tidak Aktif">Tidak Aktif</SelectItem>
                                    <SelectItem value="Cuti">Cuti</SelectItem>
                                    <SelectItem value="Keluar">Keluar</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isCreateOpen = false; isEditOpen = false">Batal</Button>
                    <Button @click="isEditOpen ? submitEdit() : submitCreate()">
                        {{ isEditOpen ? 'Simpan Perubahan' : 'Tambah Dosen' }}
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
                        Tindakan ini tidak dapat dibatalkan. Data dosen ini akan dihapus permanen dari sistem.
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
