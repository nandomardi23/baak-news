<template>
    <Head title="Pengaturan Surat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="w-full mx-auto">
                <div class="bg-card rounded-xl border shadow-sm">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold">Pengaturan Surat</h2>
                    </div>
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <!-- Identitas Instansi (Kop Surat) -->
                            <div>
                                <h3 class="text-lg font-medium border-b pb-2 mb-4">Identitas Instansi (Kop Surat)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="kop_nama_yayasan">Nama Yayasan</Label>
                                        <Input
                                            id="kop_nama_yayasan"
                                            type="text"
                                            v-model="form.kop_nama_yayasan"
                                            placeholder="Contoh: YAYASAN NALA"
                                        />
                                        <InputError :message="form.errors.kop_nama_yayasan" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="kop_nama_kampus">Nama Kampus</Label>
                                        <Input
                                            id="kop_nama_kampus"
                                            type="text"
                                            v-model="form.kop_nama_kampus"
                                            required
                                            placeholder="Contoh: STIKES HANG TUAH..."
                                        />
                                        <InputError :message="form.errors.kop_nama_kampus" />
                                    </div>

                                    <div class="col-span-2 space-y-2">
                                        <Label for="kop_alamat">Alamat Lengkap</Label>
                                        <Input
                                            id="kop_alamat"
                                            type="text"
                                            v-model="form.kop_alamat"
                                            required
                                        />
                                        <InputError :message="form.errors.kop_alamat" />
                                    </div>
                                    
                                     <div class="space-y-2">
                                        <Label for="kop_website">Website</Label>
                                        <Input
                                            id="kop_website"
                                            type="text"
                                            v-model="form.kop_website"
                                        />
                                        <InputError :message="form.errors.kop_website" />
                                    </div>
                                     <div class="space-y-2">
                                        <Label for="kop_email">Email</Label>
                                        <Input
                                            id="kop_email"
                                            type="email"
                                            v-model="form.kop_email"
                                        />
                                        <InputError :message="form.errors.kop_email" />
                                    </div>
                                </div>
                            </div>

                            <!-- Format Surat -->
                            <div>
                                <h3 class="text-lg font-medium border-b pb-2 mb-4">Format Surat</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="kota_terbit">Kota Terbit (Tempat Tanggal Surat)</Label>
                                        <Input
                                            id="kota_terbit"
                                            type="text"
                                            v-model="form.kota_terbit"
                                            required
                                            placeholder="Contoh: Tanjungpinang"
                                        />
                                        <InputError :message="form.errors.kota_terbit" />
                                    </div>
                                </div>
                            </div>

                            <!-- Default Signers -->
                            <div>
                                <h3 class="text-lg font-medium border-b pb-2 mb-4">Penandatangan Default</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="signer_aktif_kuliah">Surat Aktif Kuliah</Label>
                                        <select
                                            id="signer_aktif_kuliah"
                                            v-model="form.signer_aktif_kuliah"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="">-- Pilih Pejabat --</option>
                                            <option v-for="p in pejabat" :key="p.id" :value="p.id">
                                                {{ p.nama }} ({{ p.jabatan }})
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.signer_aktif_kuliah" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="signer_kartu_ujian">Kartu Ujian</Label>
                                        <select
                                            id="signer_kartu_ujian"
                                            v-model="form.signer_kartu_ujian"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="">-- Pilih Pejabat --</option>
                                            <option v-for="p in pejabat" :key="p.id" :value="p.id">
                                                {{ p.nama }} ({{ p.jabatan }})
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.signer_kartu_ujian" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="signer_krs">Kartu Rencana Studi (KRS)</Label>
                                        <select
                                            id="signer_krs"
                                            v-model="form.signer_krs"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="">-- Pilih Pejabat --</option>
                                            <option v-for="p in pejabat" :key="p.id" :value="p.id">
                                                {{ p.nama }} ({{ p.jabatan }})
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.signer_krs" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="signer_khs">Kartu Hasil Studi (KHS)</Label>
                                        <select
                                            id="signer_khs"
                                            v-model="form.signer_khs"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="">-- Pilih Pejabat --</option>
                                            <option v-for="p in pejabat" :key="p.id" :value="p.id">
                                                {{ p.nama }} ({{ p.jabatan }})
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.signer_khs" />
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <Label for="signer_transkrip">Transkrip Nilai</Label>
                                        <select
                                            id="signer_transkrip"
                                            v-model="form.signer_transkrip"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            <option value="">-- Pilih Pejabat --</option>
                                            <option v-for="p in pejabat" :key="p.id" :value="p.id">
                                                {{ p.nama }} ({{ p.jabatan }})
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.signer_transkrip" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end">
                                <Button :disabled="form.processing">
                                    Simpan Pengaturan
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const props = defineProps({
    settings: Object,
    pejabat: Array,
});
// ... (rest is same)


const breadcrumbs = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pengaturan Surat', href: '/admin/settings/surat' },
];

const form = useForm({
    kop_nama_yayasan: props.settings.kop_nama_yayasan || '',
    kop_nama_kampus: props.settings.kop_nama_kampus || '',
    kop_alamat: props.settings.kop_alamat || '',
    kop_website: props.settings.kop_website || '',
    kop_email: props.settings.kop_email || '',
    kota_terbit: props.settings.kota_terbit || '',
    signer_aktif_kuliah: props.settings.signer_aktif_kuliah || '',
    signer_kartu_ujian: props.settings.signer_kartu_ujian || '',
    signer_krs: props.settings.signer_krs || '',
    signer_khs: props.settings.signer_khs || '',
    signer_transkrip: props.settings.signer_transkrip || '',
});

const submit = () => {
    form.post(route('admin.settings.surat.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Toast notification
        },
    });
};
</script>
