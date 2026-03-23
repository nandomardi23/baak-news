<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/InputError.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { ref, computed } from 'vue';
import { Check, ChevronsUpDown, ArrowLeft, Save, User, GraduationCap, MapPin, BookOpen } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import DatePicker from 'primevue/datepicker';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();

const props = defineProps<{
    mahasiswa: any;
    prodi: { id: number; nama_prodi: string }[];
    dosen: { id: number; nama: string }[];
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mahasiswa', href: '/admin/mahasiswa' },
    { title: props.mahasiswa.nama, href: `/admin/mahasiswa/${props.mahasiswa.id}` },
    { title: 'Edit', href: `/admin/mahasiswa/${props.mahasiswa.id}/edit` },
]);

const form = useForm({
    nim: props.mahasiswa.nim || '',
    nama: props.mahasiswa.nama || '',
    nik: props.mahasiswa.nik || '',
    program_studi_id: props.mahasiswa.program_studi_id ? String(props.mahasiswa.program_studi_id) : '',
    angkatan: props.mahasiswa.angkatan || '',
    status_mahasiswa: props.mahasiswa.status_mahasiswa || 'A',
    tempat_lahir: props.mahasiswa.tempat_lahir || '',
    tanggal_lahir: props.mahasiswa.tanggal_lahir || '',
    jenis_kelamin: props.mahasiswa.jenis_kelamin || 'L',
    id_agama: props.mahasiswa.id_agama ? String(props.mahasiswa.id_agama) : '',
    email: props.mahasiswa.email || '',
    no_hp: props.mahasiswa.no_hp || '',
    alamat: props.mahasiswa.alamat || '',
    dosen_wali_id: props.mahasiswa.dosen_wali_id ? String(props.mahasiswa.dosen_wali_id) : '',
});

// Combobox state for Dosen Wali
const dosenOpen = ref(false);

const selectedDosenName = computed(() => {
    if (!form.dosen_wali_id) return '';
    const found = props.dosen.find(d => String(d.id) === String(form.dosen_wali_id));
    return found?.nama || '';
});

const submit = () => {
    form.patch(`/admin/mahasiswa/${props.mahasiswa.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Mahasiswa" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6 lg:p-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link :href="`/admin/mahasiswa/${mahasiswa.id}`" class="inline-flex items-center justify-center w-10 h-10 rounded-lg border bg-white hover:bg-slate-50 transition text-slate-600 hover:text-slate-900">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit Mahasiswa</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ mahasiswa.nim }} — {{ mahasiswa.nama }}</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="max-w-5xl space-y-6">
            <!-- Data Utama -->
            <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b bg-slate-50/50">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <User class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Data Utama</h3>
                        <p class="text-xs text-slate-500">Informasi identitas dasar mahasiswa</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5">
                        <div class="space-y-1.5">
                            <Label for="nim" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">NIM <span class="text-red-500">*</span></Label>
                            <Input id="nim" v-model="form.nim" required class="font-mono" />
                            <InputError :message="form.errors.nim" />
                        </div>
                        <div class="space-y-1.5 md:col-span-2">
                            <Label for="nama" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama Lengkap <span class="text-red-500">*</span></Label>
                            <Input id="nama" v-model="form.nama" required />
                            <InputError :message="form.errors.nama" />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="nik" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">NIK</Label>
                            <Input id="nik" v-model="form.nik" class="font-mono" />
                            <InputError :message="form.errors.nik" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Program Studi <span class="text-red-500">*</span></Label>
                            <Select v-model="form.program_studi_id" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Program Studi" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="p in prodi" :key="p.id" :value="String(p.id)">
                                        {{ p.nama_prodi }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.program_studi_id" />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="angkatan" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Angkatan <span class="text-red-500">*</span></Label>
                            <Input id="angkatan" v-model="form.angkatan" required class="font-mono" />
                            <InputError :message="form.errors.angkatan" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Status <span class="text-red-500">*</span></Label>
                            <Select v-model="form.status_mahasiswa" required>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="A">Aktif</SelectItem>
                                    <SelectItem value="C">Cuti</SelectItem>
                                    <SelectItem value="L">Lulus</SelectItem>
                                    <SelectItem value="N">Non-Aktif</SelectItem>
                                    <SelectItem value="K">Keluar</SelectItem>
                                    <SelectItem value="D">Drop-out</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.status_mahasiswa" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profil & Kontak -->
            <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b bg-slate-50/50">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <MapPin class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Profil & Kontak</h3>
                        <p class="text-xs text-slate-500">Data personal dan informasi kontak</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5">
                        <div class="space-y-1.5">
                            <Label for="tempat_lahir" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Tempat Lahir</Label>
                            <Input id="tempat_lahir" v-model="form.tempat_lahir" />
                            <InputError :message="form.errors.tempat_lahir" />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="tanggal_lahir" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal Lahir</Label>
                            <DatePicker
                                id="tanggal_lahir"
                                :model-value="form.tanggal_lahir ? new Date(form.tanggal_lahir) : null"
                                @update:model-value="(val) => {
                                    if (val && val instanceof Date) {
                                        const year = val.getFullYear();
                                        const month = String(val.getMonth() + 1).padStart(2, '0');
                                        const day = String(val.getDate()).padStart(2, '0');
                                        form.tanggal_lahir = `${year}-${month}-${day}`;
                                    } else {
                                        form.tanggal_lahir = '';
                                    }
                                }"
                                dateFormat="yy-mm-dd"
                                showIcon
                                class="w-full flex h-10 border-input bg-background"
                            />
                            <InputError :message="form.errors.tanggal_lahir" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Jenis Kelamin</Label>
                            <Select v-model="form.jenis_kelamin">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="L">Laki-laki</SelectItem>
                                    <SelectItem value="P">Perempuan</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.jenis_kelamin" />
                        </div>
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Agama</Label>
                            <Select v-model="form.id_agama">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih Agama" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="1">Islam</SelectItem>
                                    <SelectItem value="2">Kristen</SelectItem>
                                    <SelectItem value="3">Katolik</SelectItem>
                                    <SelectItem value="4">Hindu</SelectItem>
                                    <SelectItem value="5">Buddha</SelectItem>
                                    <SelectItem value="6">Konghucu</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.id_agama" />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="email" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Email</Label>
                            <Input id="email" type="email" v-model="form.email" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="no_hp" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">No HP</Label>
                            <Input id="no_hp" v-model="form.no_hp" />
                            <InputError :message="form.errors.no_hp" />
                        </div>
                        <div class="space-y-1.5 md:col-span-3">
                            <Label for="alamat" class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Alamat</Label>
                            <Textarea id="alamat" v-model="form.alamat" rows="3" />
                            <InputError :message="form.errors.alamat" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Akademik Tambahan -->
            <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b bg-slate-50/50">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                        <BookOpen class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-900">Akademik Tambahan</h3>
                        <p class="text-xs text-slate-500">Dosen pembimbing akademik</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Dosen Wali</Label>
                            <Popover v-model:open="dosenOpen">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        role="combobox"
                                        :aria-expanded="dosenOpen"
                                        class="w-full justify-between font-normal"
                                    >
                                        <span :class="selectedDosenName ? 'text-foreground' : 'text-muted-foreground'">
                                            {{ selectedDosenName || 'Cari dan pilih dosen wali...' }}
                                        </span>
                                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-[--reka-popover-trigger-width] p-0" align="start">
                                    <Command>
                                        <CommandInput placeholder="Ketik nama dosen..." />
                                        <CommandEmpty>Dosen tidak ditemukan.</CommandEmpty>
                                        <CommandList>
                                            <CommandGroup>
                                                <CommandItem
                                                    v-for="d in dosen"
                                                    :key="d.id"
                                                    :value="d.nama"
                                                    @select="() => { form.dosen_wali_id = String(d.id); dosenOpen = false; }"
                                                >
                                                    <Check :class="cn('mr-2 h-4 w-4', String(form.dosen_wali_id) === String(d.id) ? 'opacity-100' : 'opacity-0')" />
                                                    {{ d.nama }}
                                                </CommandItem>
                                            </CommandGroup>
                                        </CommandList>
                                    </Command>
                                </PopoverContent>
                            </Popover>
                            <InputError :message="form.errors.dosen_wali_id" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer -->
            <div class="flex items-center justify-between pt-2 pb-4">
                <Link :href="`/admin/mahasiswa/${mahasiswa.id}`">
                    <Button variant="outline" type="button" class="gap-2">
                        <ArrowLeft class="w-4 h-4" />
                        Batal
                    </Button>
                </Link>
                <Button type="submit" :disabled="form.processing" class="gap-2 bg-blue-600 hover:bg-blue-700">
                    <Save class="w-4 h-4" />
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </Button>
            </div>
        </form>
    </div>
</template>
