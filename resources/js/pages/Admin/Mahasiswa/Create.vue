<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import DatePicker from 'primevue/datepicker';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mahasiswa', href: '/admin/mahasiswa' },
    { title: 'Tambah', href: '/admin/mahasiswa/create' },
]);

const props = defineProps<{
    prodi: { id: number; nama_prodi: string }[];
    dosen: { id: number; nama: string }[];
}>();

const form = useForm({
    nim: '',
    nama: '',
    nik: '',
    program_studi_id: null as number | null,
    angkatan: new Date().getFullYear().toString(),
    status_mahasiswa: 'A',
    tempat_lahir: '',
    tanggal_lahir: '',
    jenis_kelamin: 'L',
    id_agama: null as number | null,
    email: '',
    no_hp: '',
    alamat: '',
    dosen_wali_id: null as number | null,
});

const submit = () => {
    form.post('/admin/mahasiswa', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Tambah Mahasiswa" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Tambah Mahasiswa</h1>
                <p class="text-muted-foreground">Tambah data mahasiswa secara manual</p>
            </div>
            <Link href="/admin/mahasiswa">
                <Button variant="outline">Kembali</Button>
            </Link>
        </div>

        <form @submit.prevent="submit" class="max-w-4xl space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Data Utama</CardTitle>
                    <CardDescription>Informasi esensial mahasiswa</CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="nim">NIM <span class="text-red-500">*</span></Label>
                        <Input id="nim" v-model="form.nim" required />
                        <InputError :message="form.errors.nim" />
                    </div>
                    <div class="space-y-2">
                        <Label for="nama">Nama Lengkap <span class="text-red-500">*</span></Label>
                        <Input id="nama" v-model="form.nama" required />
                        <InputError :message="form.errors.nama" />
                    </div>
                    <div class="space-y-2">
                        <Label for="nik">NIK</Label>
                        <Input id="nik" v-model="form.nik" />
                        <InputError :message="form.errors.nik" />
                    </div>
                    <div class="space-y-2">
                        <Label>Program Studi <span class="text-red-500">*</span></Label>
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
                    <div class="space-y-2">
                        <Label for="angkatan">Angkatan <span class="text-red-500">*</span></Label>
                        <Input id="angkatan" v-model="form.angkatan" required />
                        <InputError :message="form.errors.angkatan" />
                    </div>
                    <div class="space-y-2">
                        <Label>Status <span class="text-red-500">*</span></Label>
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
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Profil & Kontak</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="tempat_lahir">Tempat Lahir</Label>
                        <Input id="tempat_lahir" v-model="form.tempat_lahir" />
                        <InputError :message="form.errors.tempat_lahir" />
                    </div>
                    <div class="space-y-2">
                        <Label for="tanggal_lahir">Tanggal Lahir</Label>
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
                    <div class="space-y-2">
                        <Label>Jenis Kelamin</Label>
                        <Select v-model="form.jenis_kelamin">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Jenis Kelamin" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="L">Laki-laki</SelectItem>
                                <SelectItem value="P">Perempuan</SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.jenis_kelamin" />
                    </div>
                    <div class="space-y-2">
                        <Label>Agama</Label>
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
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="form.email" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="space-y-2">
                        <Label for="no_hp">No HP</Label>
                        <Input id="no_hp" v-model="form.no_hp" />
                        <InputError :message="form.errors.no_hp" />
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <Label for="alamat">Alamat</Label>
                        <Textarea id="alamat" v-model="form.alamat" rows="3" />
                        <InputError :message="form.errors.alamat" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Akademik Tambahan</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2 md:col-span-2">
                        <Label>Dosen Wali</Label>
                        <Select v-model="form.dosen_wali_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Dosen Wali" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="d in dosen" :key="d.id" :value="String(d.id)">
                                    {{ d.nama }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.dosen_wali_id" />
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end gap-4">
                <Link href="/admin/mahasiswa">
                    <Button variant="outline" type="button">Batal</Button>
                </Link>
                <Button type="submit" :disabled="form.processing">
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Data</span>
                </Button>
            </div>
        </form>
    </div>
</template>
