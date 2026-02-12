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
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Pencil, Trash2, Eye, Check } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import Swal from 'sweetalert2';

interface Prodi {
    id: number;
    id_prodi: string;
    kode_prodi: string;
    nama_prodi: string;
    jenjang: string;
    jenis_program: string;
    akreditasi: string | null;
    is_active: boolean;
    created_at?: string;
}

const props = defineProps<{
    prodiList: any;
    filters: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Program Studi', href: '/admin/akademik/prodi' },
];

const columns = [
    { key: 'kode_prodi', label: 'Kode', sortable: true, class: 'font-mono' },
    { key: 'nama_prodi', label: 'Nama Program Studi', sortable: true },
    { key: 'jenjang', label: 'Jenjang', sortable: true, align: 'center' as const },
    { key: 'jenis_program', label: 'Jenis', sortable: true, align: 'center' as const },
    { key: 'akreditasi', label: 'Akreditasi', sortable: true, align: 'center' as const },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center' as const },
    { key: 'actions', label: 'Aksi', align: 'center' as const },
];

const isDetailOpen = ref(false);
const isEditOpen = ref(false);
const selectedItem = ref<Prodi | null>(null);

const form = useForm({
    kode_prodi: '',
    nama_prodi: '',
    jenjang: '',
    jenis_program: 'reguler',
    akreditasi: null as string | null,
    is_active: true,
});

const openDetail = (item: Prodi) => {
    selectedItem.value = item;
    isDetailOpen.value = true;
};

const openEdit = (item: Prodi) => {
    selectedItem.value = item;
    form.kode_prodi = item.kode_prodi;
    form.nama_prodi = item.nama_prodi;
    form.jenjang = item.jenjang;
    form.jenis_program = item.jenis_program;
    form.akreditasi = item.akreditasi;
    form.is_active = !!item.is_active;
    isEditOpen.value = true;
};

const openDelete = (item: Prodi) => {
    Swal.fire({
        title: 'Apakah anda yakin?',
        text: 'Tindakan ini tidak dapat dibatalkan. Data program studi ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('admin.akademik.prodi.destroy', item.id), {
                onSuccess: () => {
                    toast.success('Berhasil', { description: 'Program Studi berhasil dihapus' });
                },
            });
        }
    });
};

const submitEdit = () => {
    if (!selectedItem.value) return;
    form.put(route('admin.akademik.prodi.update', selectedItem.value.id), {
        onSuccess: () => {
            isEditOpen.value = false;
            toast.success('Berhasil', { description: 'Program Studi berhasil diperbarui' });
        },
    });
};

// Deletion handled by openDelete via SweetAlert
</script>

<template>
    <Head title="Program Studi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full min-w-0">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Program Studi</h1>
                    <p class="text-slate-500 mt-1">Kelola data program studi dan akreditasi.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-full text-xs font-medium flex items-center gap-1 justify-center sm:justify-start">
                        <Check class="w-3 h-3" />
                        Data Terintegrasi Neo Feeder
                    </div>
                </div>
            </div>

            <SmartTable
                :data="prodiList"
                :columns="columns"
                :search="filters.search"
                title="Daftar Program Studi"
            >
                <template #cell-jenjang="{ row }">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded text-xs font-medium uppercase">
                        {{ row.jenjang }}
                    </span>
                </template>

                <template #cell-jenis_program="{ row }">
                    <span class="capitalize text-sm">{{ row.jenis_program }}</span>
                </template>

                <template #cell-akreditasi="{ row }">
                    <span v-if="row.akreditasi" class="px-2 py-1 bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 rounded text-xs font-medium">
                        {{ row.akreditasi }}
                    </span>
                    <span v-else class="text-muted-foreground">-</span>
                </template>

                <template #cell-is_active="{ row }">
                    <span
                        :class="row.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-800'"
                        class="px-2 py-1 rounded-full text-xs font-medium"
                    >
                        {{ row.is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </template>

                <template #cell-actions="{ row }">
                     <div class="flex items-center justify-center gap-2">
                        <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-600 hover:text-indigo-600 hover:bg-slate-100" @click="openDetail(row)">
                            <Eye class="w-4 h-4" />
                        </Button>
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

        <!-- Detail Modal -->
        <Dialog :open="isDetailOpen" @update:open="isDetailOpen = $event">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Detail Program Studi</DialogTitle>
                    <DialogDescription>Informasi lengkap program studi.</DialogDescription>
                </DialogHeader>
                <div v-if="selectedItem" class="space-y-4 py-2 text-sm">
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50">
                        <span class="text-slate-500">ID Feeder</span>
                        <span class="col-span-2 font-mono text-xs">{{ selectedItem.id_prodi }}</span>
                    </div>
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50">
                        <span class="text-slate-500">Kode Prodi</span>
                        <span class="col-span-2 font-mono font-bold">{{ selectedItem.kode_prodi }}</span>
                    </div>
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50">
                        <span class="text-slate-500">Nama Prodi</span>
                        <span class="col-span-2 font-bold">{{ selectedItem.nama_prodi }}</span>
                    </div>
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50">
                        <span class="text-slate-500">Jenjang</span>
                        <span class="col-span-2 uppercase">{{ selectedItem.jenjang }}</span>
                    </div>
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50">
                        <span class="text-slate-500">Jenis</span>
                        <span class="col-span-2 capitalize">{{ selectedItem.jenis_program }}</span>
                    </div>
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50">
                        <span class="text-slate-500">Akreditasi</span>
                        <span class="col-span-2">{{ selectedItem.akreditasi || '-' }}</span>
                    </div>
                    <div class="grid grid-cols-3 py-2 border-b border-slate-50">
                        <span class="text-slate-500">Status</span>
                        <span class="col-span-2">
                             <span :class="selectedItem.is_active ? 'text-emerald-600' : 'text-slate-400'" class="font-bold">
                                {{ selectedItem.is_active ? 'Aktif' : 'Tidak Aktif' }}
                             </span>
                        </span>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="isDetailOpen = false">Tutup</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog :open="isEditOpen" @update:open="isEditOpen = $event">
            <DialogContent class="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Edit Program Studi</DialogTitle>
                    <DialogDescription>Perbarui data program studi.</DialogDescription>
                </DialogHeader>

                <div class="grid gap-4 py-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Kode Prodi</Label>
                            <Input v-model="form.kode_prodi" />
                        </div>
                        <div class="space-y-2">
                            <Label>Jenjang</Label>
                            <Input v-model="form.jenjang" placeholder="Contoh: S1" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Nama Program Studi</Label>
                        <Input v-model="form.nama_prodi" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Jenis Program</Label>
                             <Select v-model="form.jenis_program">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Jenis" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="reguler">Reguler</SelectItem>
                                    <SelectItem value="rpl">RPL</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label>Akreditasi</Label>
                            <Input :model-value="form.akreditasi || ''" @input="form.akreditasi = ($event.target as HTMLInputElement).value" placeholder="A/B/C" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4 border rounded-lg p-3">
                         <Label class="flex-1">Status Keaktifan</Label>
                         <Select :model-value="String(form.is_active)" @update:model-value="(val) => form.is_active = val === 'true'">
                            <SelectTrigger class="w-32">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="true">Aktif</SelectItem>
                                <SelectItem value="false">Tidak Aktif</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="isEditOpen = false">Batal</Button>
                    <Button @click="submitEdit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation is now handled by SweetAlert in JS -->

    </AppLayout>
</template>

