<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DataTable,
    TableHeader,
    TableRow,
    TableCell,
} from '@/components/ui/datatable';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { 
    Search, 
    Plus, 
    MoreHorizontal, 
    Pencil, 
    Trash2, 
    UserCircle,
    CheckCircle2
} from 'lucide-vue-next';

interface Pejabat {
    id: number;
    nama: string;
    nama_lengkap: string;
    nip: string | null;
    nidn: string | null;
    nik: string | null;
    jabatan: string;
    gelar_depan: string | null;
    gelar_belakang: string | null;
    pangkat_golongan: string | null;
    periode_awal: string | null;
    periode_akhir: string | null;
    tandatangan_path: string | null;
    is_active: boolean;
    dosen_id: number | null;
}

interface Dosen {
    id: number;
    nama: string;
    nama_lengkap: string;
    nip: string | null;
    nidn: string | null;
    gelar_depan: string | null;
    gelar_belakang: string | null;
}

const props = defineProps<{
    pejabat: Pejabat[];
    jabatanOptions: string[];
    dosenOptions: Dosen[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Data Pejabat', href: '/admin/pejabat' },
];

const searchQuery = ref('');
const showDialog = ref(false);
const editingPejabat = ref<Pejabat | null>(null);

const form = useForm({
    nama: '',
    nip: '',
    nidn: '',
    nik: '',
    jabatan: '',
    gelar_depan: '',
    gelar_belakang: '',
    pangkat_golongan: '',
    periode_awal: '',
    periode_akhir: '',
    tandatangan: null as File | null,
    is_active: true,
    dosen_id: null as number | null,
});

// Watch for Dosen selection to auto-fill data
watch(() => form.dosen_id, (newVal) => {
    if (newVal) {
        const selectedDosen = props.dosenOptions.find(d => d.id === newVal);
        if (selectedDosen) {
            form.nama = selectedDosen.nama;
            form.nip = selectedDosen.nip || form.nip;
            form.nidn = selectedDosen.nidn || form.nidn;
            form.gelar_depan = selectedDosen.gelar_depan || form.gelar_depan;
            form.gelar_belakang = selectedDosen.gelar_belakang || form.gelar_belakang;
        }
    }
});

const filteredPejabat = computed(() => {
    if (!searchQuery.value) return props.pejabat;
    const query = searchQuery.value.toLowerCase();
    return props.pejabat.filter(item => 
        item.nama_lengkap.toLowerCase().includes(query) ||
        item.jabatan.toLowerCase().includes(query) ||
        (item.nip && item.nip.includes(query)) ||
        (item.nidn && item.nidn.includes(query))
    );
});

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

const openCreateDialog = () => {
    editingPejabat.value = null;
    form.reset();
    form.is_active = true;
    form.dosen_id = null;
    showDialog.value = true;
};

const openEditDialog = (item: Pejabat) => {
    editingPejabat.value = item;
    form.nama = item.nama || '';
    form.nip = item.nip || '';
    form.nidn = item.nidn || '';
    form.nik = item.nik || '';
    form.jabatan = item.jabatan || '';
    form.gelar_depan = item.gelar_depan || '';
    form.gelar_belakang = item.gelar_belakang || '';
    form.pangkat_golongan = item.pangkat_golongan || '';
    form.periode_awal = item.periode_awal || '';
    form.periode_akhir = item.periode_akhir || '';
    form.tandatangan = null;
    form.is_active = Boolean(item.is_active);
    form.dosen_id = item.dosen_id || null;
    showDialog.value = true;
};

const closeDialog = () => {
    showDialog.value = false;
    setTimeout(() => {
        editingPejabat.value = null;
        form.reset();
    }, 300);
};

const submit = () => {
    if (editingPejabat.value) {
        form.post(`/admin/pejabat/${editingPejabat.value.id}`, {
            forceFormData: true,
            headers: { 'X-HTTP-Method-Override': 'PUT' },
            onSuccess: () => closeDialog(),
        });
    } else {
        form.post('/admin/pejabat', {
            forceFormData: true,
            onSuccess: () => closeDialog(),
        });
    }
};

const deletePejabat = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus data pejabat ini?')) {
        router.delete(`/admin/pejabat/${id}`);
    }
};

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        form.tandatangan = target.files[0];
    }
};
</script>

<template>
    <Head title="Data Pejabat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 lg:p-10 w-full">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Data Pejabat</h1>
                    <p class="text-slate-500">Kelola data pejabat penandatangan dokumen akademik</p>
                </div>
                <Button @click="openCreateDialog" class="bg-primary hover:bg-primary/90">
                    <Plus class="w-4 h-4 mr-2" />
                    Tambah Pejabat
                </Button>
            </div>

            <div class="space-y-4">
                <!-- Main Table -->
                <DataTable>
                    <template #toolbar>
                        <div class="relative w-full max-w-sm">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari nama, NIP, atau jabatan..."
                                class="pl-9 h-9 bg-white w-full"
                            />
                        </div>
                    </template>

                    <thead class="bg-slate-50/80">
                        <TableRow class="hover:bg-transparent">
                            <TableHeader class="w-[300px] pl-6 h-12">Pejabat</TableHeader>
                            <TableHeader>Jabatan & Pangkat</TableHeader>
                            <TableHeader>Identitas (NIK/NIDN)</TableHeader>
                            <TableHeader>Periode Menjabat</TableHeader>
                            <TableHeader class="w-[100px] text-center">Status</TableHeader>
                            <TableHeader class="w-[80px] text-right pr-6">Aksi</TableHeader>
                        </TableRow>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <TableRow v-for="item in filteredPejabat" :key="item.id" class="group hover:bg-slate-50/50 transition-colors">
                            <TableCell class="pl-6 py-4">
                                <div class="flex items-center gap-3">
                                    <Avatar class="h-10 w-10 border border-slate-200 bg-slate-50">
                                        <AvatarImage 
                                            v-if="item.tandatangan_path" 
                                            :src="`/storage/${item.tandatangan_path}`" 
                                            alt="Tanda Tangan"
                                            class="object-contain p-1 opacity-50" 
                                        />
                                        <AvatarFallback class="bg-blue-50 text-blue-600 font-semibold text-xs">
                                            {{ getInitials(item.nama) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-900">{{ item.nama_lengkap }}</span>
                                        <span v-if="item.gelar_depan || item.gelar_belakang" class="text-xs text-slate-500">
                                            {{ [item.gelar_depan, item.nama, item.gelar_belakang].filter(Boolean).join(' ') }}
                                        </span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-1">
                                    <div class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 w-fit">
                                        {{ item.jabatan }}
                                    </div>
                                    <span v-if="item.pangkat_golongan" class="text-xs text-slate-500">
                                        {{ item.pangkat_golongan }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col font-mono text-xs text-slate-600 gap-1">
                                    <span v-if="item.nip" class="flex items-center gap-1.5">
                                        <span class="text-slate-400 w-8">NIK</span>
                                        <span>{{ item.nip }}</span>
                                    </span>
                                    <span v-if="item.nidn" class="flex items-center gap-1.5">
                                        <span class="text-slate-400 w-8">NIDN</span>
                                        <span>{{ item.nidn }}</span>
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                    <div v-if="item.periode_awal" class="flex items-center gap-1.5 text-sm text-slate-600">
                                    <span>{{ new Date(item.periode_awal).toLocaleDateString('id-ID', {year: 'numeric'}) }}</span>
                                    <span class="text-slate-300">→</span>
                                    <span :class="!item.periode_akhir ? 'text-emerald-600 font-medium' : ''">
                                        {{ item.periode_akhir ? new Date(item.periode_akhir).toLocaleDateString('id-ID', {year: 'numeric'}) : 'Sekarang' }}
                                    </span>
                                </div>
                                <span v-else class="text-slate-400 text-xs italic">Tidak diset</span>
                            </TableCell>
                            <TableCell class="text-center">
                                <Badge 
                                    :variant="item.is_active ? 'default' : 'secondary'"
                                    :class="item.is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 border-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200 border-slate-200'"
                                    class="uppercase text-[10px] tracking-wider px-2 py-0.5 font-bold shadow-none border"
                                >
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right pr-6">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-400 hover:text-slate-600">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-[160px]">
                                        <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="openEditDialog(item)">
                                            <Pencil class="mr-2 h-3.5 w-3.5 text-slate-500" />
                                            Edit Data
                                        </DropdownMenuItem>
                                        <DropdownMenuItem @click="deletePejabat(item.id)" class="text-red-600 focus:text-red-600 focus:bg-red-50">
                                            <Trash2 class="mr-2 h-3.5 w-3.5" />
                                            Hapus
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>

                        <!-- Empty State -->
                        <TableRow v-if="filteredPejabat.length === 0">
                            <TableCell colspan="6" class="h-[400px] text-center p-0">
                                <div class="flex flex-col items-center justify-center h-full text-slate-400">
                                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <UserCircle class="h-8 w-8 opacity-50" />
                                    </div>
                                    <p class="font-medium text-slate-900">Tidak ada data pejabat</p>
                                    <p class="text-sm mt-1">Belum ada data yang sesuai dengan pencarian Anda.</p>
                                    <Button v-if="searchQuery" @click="searchQuery = ''" variant="link" class="mt-2 h-auto p-0 text-primary">
                                        Reset Pencarian
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </tbody>
                </DataTable>
            </div>
        </div>

        <!-- Dialog Form -->
        <Dialog :open="showDialog" @update:open="val => !val && closeDialog()">
            <DialogContent class="sm:max-w-[600px] p-0 overflow-hidden gap-0">
                <DialogHeader class="p-6 pb-4 bg-slate-50/50 border-b border-slate-100">
                    <DialogTitle>{{ editingPejabat ? 'Edit Data Pejabat' : 'Tambah Pejabat Baru' }}</DialogTitle>
                    <DialogDescription>
                        Isi form berikut untuk {{ editingPejabat ? 'memperbarui' : 'menambahkan' }} data pejabat penandatangan.
                    </DialogDescription>
                </DialogHeader>

                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between p-4 rounded-lg border border-slate-200 bg-slate-50/50">
                        <div class="space-y-0.5">
                            <Label class="text-base">Status Aktif</Label>
                            <p class="text-xs text-slate-500">Pejabat aktif akan muncul di pilihan penandatangan.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="form.is_active ? 'text-emerald-600 font-medium' : 'text-slate-400'" class="text-sm">
                                {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <button 
                                type="button"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ring-offset-background"
                                :class="form.is_active ? 'bg-primary' : 'bg-input'"
                                @click="form.is_active = !form.is_active"
                            >
                                <span
                                    class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform"
                                    :class="form.is_active ? 'translate-x-5' : 'translate-x-0.5'"
                                />
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Dosen Selection -->
                        <div class="col-span-2 p-3 bg-slate-100/50 rounded-lg border border-slate-200">
                            <Label class="text-xs font-semibold uppercase text-slate-500 mb-1.5 block">Ambil Data dari Dosen</Label>
                            <div class="relative">
                                <select
                                    v-model="form.dosen_id"
                                    class="flex h-9 w-full rounded-md border border-input bg-white px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <option :value="null">-- Tidak / Manual --</option>
                                    <option v-for="dosen in dosenOptions" :key="dosen.id" :value="dosen.id">
                                        {{ dosen.nama_lengkap }} ({{ dosen.nidn || dosen.nip || '-' }})
                                    </option>
                                </select>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1.5 flex items-center gap-1">
                                <CheckCircle2 class="w-3 h-3 text-emerald-500" />
                                Otomatis mengisi Nama, NIP/NIDN, dan Gelar.
                            </p>
                        </div>

                        <div class="col-span-2 space-y-1.5">
                            <Label>Nama Panggilan <span class="text-red-500">*</span></Label>
                            <Input v-model="form.nama" placeholder="Nama singkat" />
                            <p class="text-[10px] text-slate-500">Digunakan untuk display singkat.</p>
                            <p v-if="form.errors.nama" class="text-xs text-red-500">{{ form.errors.nama }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label>Gelar Depan</Label>
                            <Input v-model="form.gelar_depan" placeholder="Contoh: Dr., Ir." />
                        </div>

                        <div class="space-y-1.5">
                            <Label>Gelar Belakang</Label>
                            <Input v-model="form.gelar_belakang" placeholder="Contoh: S.Kom, M.T" />
                        </div>

                        <div class="col-span-2 space-y-1.5">
                            <Label>Pangkat / Golongan</Label>
                            <Input v-model="form.pangkat_golongan" placeholder="Contoh: Penata Muda Tk.I (III/b)" />
                        </div>

                        <div class="space-y-1.5">
                            <Label>NIK (Kepegawaian)</Label>
                            <Input v-model="form.nip" placeholder="Nomor Induk Kepegawaian" />
                        </div>

                        <div class="space-y-1.5">
                            <Label>NIDN</Label>
                            <Input v-model="form.nidn" placeholder="Nomor Induk Dosen Nasional" />
                        </div>

                        <div class="col-span-2 space-y-1.5">
                            <Label>No. KTP</Label>
                            <Input v-model="form.nik" placeholder="Nomor Induk Kependudukan" maxlength="16" />
                        </div>

                        <div class="col-span-2 space-y-1.5">
                            <Label>Jabatan Struktural <span class="text-red-500">*</span></Label>
                            <select
                                v-model="form.jabatan"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="" disabled>Pilih Jabatan</option>
                                <option v-for="opt in jabatanOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <p v-if="form.errors.jabatan" class="text-xs text-red-500">{{ form.errors.jabatan }}</p>
                        </div>

                        <div class="space-y-1.5">
                            <Label>Periode Awal</Label>
                            <Input v-model="form.periode_awal" type="date" />
                        </div>

                        <div class="space-y-1.5">
                            <Label>Periode Akhir</Label>
                            <Input v-model="form.periode_akhir" type="date" />
                        </div>

                        <div class="col-span-2 space-y-3 pt-2">
                            <Label>File Tanda Tangan (Scan)</Label>
                            <div class="flex items-center gap-4">
                                <div v-if="editingPejabat?.tandatangan_path && !form.tandatangan" class="h-20 w-32 border rounded-lg p-2 bg-slate-50 flex items-center justify-center">
                                    <img :src="`/storage/${editingPejabat.tandatangan_path}`" class="max-h-full max-w-full object-contain" />
                                </div>
                                <Input type="file" accept="image/png,image/jpeg" @change="handleFileChange" class="flex-1 cursor-pointer" />
                            </div>
                            <p class="text-[10px] text-slate-500">Format: PNG/JPG. Background transparan lebih disarankan.</p>
                        </div>
                    </div>
                </div>

                <DialogFooter class="p-6 pt-2">
                    <Button variant="outline" @click="closeDialog" type="button">Batal</Button>
                    <Button @click="submit" :disabled="form.processing" class="bg-primary text-primary-foreground hover:bg-primary/90">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
