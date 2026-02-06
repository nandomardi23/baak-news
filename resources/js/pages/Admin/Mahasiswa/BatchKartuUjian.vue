<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

interface TahunAkademik {
    id: number;
    nama: string;
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
    prodi: string;
    angkatan: string;
}

const props = defineProps<{
    tahunAkademik: TahunAkademik[];
    prodi: Prodi[];
    angkatanList: string[];
    mahasiswa: Mahasiswa[];
    filters: {
        tahun_akademik_id?: number;
        angkatan?: string;
        prodi_id?: number;
    };
    selectedSemester?: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mahasiswa', href: '/admin/mahasiswa' },
    { title: 'Batch Kartu Ujian', href: '#' },
];

const form = ref({
    tahun_akademik_id: props.filters.tahun_akademik_id || '',
    angkatan: props.filters.angkatan || '',
    prodi_id: props.filters.prodi_id || '',
});

const isLoading = ref(false);

const applyFilter = () => {
    if (!form.value.tahun_akademik_id) {
        alert('Pilih semester terlebih dahulu');
        return;
    }
    
    isLoading.value = true;
    router.get('/admin/mahasiswa/kartu-ujian/batch', form.value as any, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const printUrl = computed(() => {
    const params = new URLSearchParams();
    if (form.value.tahun_akademik_id) params.append('tahun_akademik_id', String(form.value.tahun_akademik_id));
    if (form.value.angkatan) params.append('angkatan', form.value.angkatan);
    if (form.value.prodi_id) params.append('prodi_id', String(form.value.prodi_id));
    return `/admin/mahasiswa/kartu-ujian/batch/print?${params.toString()}`;
});

const totalMahasiswa = computed(() => props.mahasiswa.length);
</script>

<template>
    <Head title="Batch Kartu Ujian" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Cetak Kartu Ujian Batch</h1>
                    <p class="text-muted-foreground">Cetak kartu ujian untuk satu angkatan sekaligus</p>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="rounded-xl border bg-card shadow-sm p-6">
                <h3 class="font-semibold mb-4">Filter Mahasiswa</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Semester -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester *</label>
                        <select 
                            v-model="form.tahun_akademik_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">-- Pilih Semester --</option>
                            <option v-for="ta in tahunAkademik" :key="ta.id" :value="ta.id">
                                {{ ta.nama }}
                            </option>
                        </select>
                    </div>

                    <!-- Angkatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Angkatan</label>
                        <select 
                            v-model="form.angkatan"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">-- Semua --</option>
                            <option v-for="ang in angkatanList" :key="ang" :value="ang">
                                {{ ang }}
                            </option>
                        </select>
                    </div>

                    <!-- Prodi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                        <select 
                            v-model="form.prodi_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">-- Semua --</option>
                            <option v-for="p in prodi" :key="p.id" :value="p.id">
                                {{ p.nama_prodi }}
                            </option>
                        </select>
                    </div>

                    <!-- Button -->
                    <div class="flex items-end">
                        <button
                            @click="applyFilter"
                            :disabled="isLoading"
                            class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <svg v-if="isLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Cari
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div v-if="selectedSemester" class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <!-- Table Header -->
                <div class="p-4 bg-muted/50 border-b flex justify-between items-center">
                    <div>
                        <h3 class="font-semibold">Daftar Mahasiswa</h3>
                        <p class="text-sm text-muted-foreground">
                            {{ selectedSemester }}
                            <span v-if="filters.angkatan"> • Angkatan {{ filters.angkatan }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                            Total: {{ totalMahasiswa }} Mahasiswa
                        </span>
                        <a
                            v-if="totalMahasiswa > 0"
                            :href="printUrl"
                            target="_blank"
                            class="px-4 py-2 bg-amber-600 text-white font-medium rounded-lg hover:bg-amber-700 transition flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Semua ({{ totalMahasiswa }})
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <table class="w-full">
                    <thead class="bg-muted/30">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium w-12">No</th>
                            <th class="px-4 py-3 text-left text-sm font-medium">NIM</th>
                            <th class="px-4 py-3 text-left text-sm font-medium">Nama</th>
                            <th class="px-4 py-3 text-left text-sm font-medium">Program Studi</th>
                            <th class="px-4 py-3 text-center text-sm font-medium">Angkatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="(mhs, idx) in mahasiswa" :key="mhs.id" class="hover:bg-muted/20">
                            <td class="px-4 py-3 text-sm">{{ idx + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ mhs.nim }}</td>
                            <td class="px-4 py-3 text-sm font-medium">{{ mhs.nama }}</td>
                            <td class="px-4 py-3 text-sm">{{ mhs.prodi }}</td>
                            <td class="px-4 py-3 text-sm text-center">{{ mhs.angkatan }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="totalMahasiswa === 0" class="text-center py-12 text-muted-foreground">
                    <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Tidak ada mahasiswa yang memenuhi kriteria</p>
                    <p class="text-sm mt-1">Coba ubah filter dan cari lagi</p>
                </div>
            </div>

            <!-- Initial State -->
            <div v-else class="rounded-xl border bg-card shadow-sm p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-semibold text-muted-foreground">Pilih Semester</h3>
                <p class="text-sm text-muted-foreground mt-1">Pilih semester terlebih dahulu untuk melihat daftar mahasiswa</p>
            </div>
        </div>
    </AppLayout>
</template>
