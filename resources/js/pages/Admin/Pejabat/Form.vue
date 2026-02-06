<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Pejabat {
    id?: number;
    nama: string;
    nip: string | null;
    nidn: string | null;
    nik: string | null;
    jabatan: string;
    pangkat_golongan: string | null;
    gelar_depan: string | null;
    gelar_belakang: string | null;
    periode_awal: string | null;
    periode_akhir: string | null;
    tandatangan_path: string | null;
    is_active: boolean;
}

const props = defineProps<{
    pejabat?: Pejabat;
    jabatanOptions: string[];
}>();

const isEditing = computed(() => !!props.pejabat?.id);

const form = useForm({
    nama: props.pejabat?.nama || '',
    nip: props.pejabat?.nip || '',
    nidn: props.pejabat?.nidn || '',
    nik: props.pejabat?.nik || '',
    jabatan: props.pejabat?.jabatan || '',
    pangkat_golongan: props.pejabat?.pangkat_golongan || '',
    gelar_depan: props.pejabat?.gelar_depan || '',
    gelar_belakang: props.pejabat?.gelar_belakang || '',
    periode_awal: props.pejabat?.periode_awal || '',
    periode_akhir: props.pejabat?.periode_akhir || '',
    tandatangan: null as File | null,
    is_active: props.pejabat?.is_active ?? true,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pejabat', href: '/admin/pejabat' },
    { title: isEditing.value ? 'Edit' : 'Tambah', href: '#' },
];

const submit = () => {
    if (isEditing.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(`/admin/pejabat/${props.pejabat?.id}`, {
            forceFormData: true,
        });
    } else {
        form.post('/admin/pejabat');
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
    <Head :title="isEditing ? 'Edit Pejabat' : 'Tambah Pejabat'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold">{{ isEditing ? 'Edit Pejabat' : 'Tambah Pejabat' }}</h1>
                <p class="text-muted-foreground">{{ isEditing ? 'Perbarui data pejabat' : 'Tambah data pejabat baru' }}</p>
            </div>

            <div class="rounded-xl border bg-card shadow-sm p-6 max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Nama <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.nama"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                required
                            />
                            <p v-if="form.errors.nama" class="text-red-500 text-sm mt-1">{{ form.errors.nama }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Gelar Depan</label>
                            <input
                                v-model="form.gelar_depan"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Dr."
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Gelar Belakang</label>
                            <input
                                v-model="form.gelar_belakang"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="S.Kep, M.Kep"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Pangkat / Golongan</label>
                            <input
                                v-model="form.pangkat_golongan"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="Contoh: Kolonel Laut (K/W) Purn"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">NIP</label>
                            <input
                                v-model="form.nip"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">NIDN</label>
                            <input
                                v-model="form.nidn"
                                type="text"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">NIK</label>
                            <input
                                v-model="form.nik"
                                type="text"
                                maxlength="16"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                placeholder="16 digit NIK"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Jabatan <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.jabatan"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                                required
                            >
                                <option value="" disabled>Pilih jabatan</option>
                                <option v-for="opt in jabatanOptions" :key="opt" :value="opt">{{ opt }}</option>
                            </select>
                            <p v-if="form.errors.jabatan" class="text-red-500 text-sm mt-1">{{ form.errors.jabatan }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Periode Awal</label>
                            <input
                                v-model="form.periode_awal"
                                type="date"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Periode Akhir</label>
                            <input
                                v-model="form.periode_akhir"
                                type="date"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Tanda Tangan (PNG/JPG)</label>
                            <input
                                type="file"
                                accept="image/png,image/jpeg"
                                @change="handleFileChange"
                                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                            <p v-if="pejabat?.tandatangan_path" class="text-sm text-muted-foreground mt-1">
                                File saat ini: {{ pejabat.tandatangan_path }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                                />
                                <span class="text-sm font-medium">Aktif</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-primary text-primary-foreground font-medium rounded-lg hover:bg-primary/90 transition disabled:opacity-50"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                        <Link href="/admin/pejabat" class="px-6 py-2 border rounded-lg hover:bg-muted transition">
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
