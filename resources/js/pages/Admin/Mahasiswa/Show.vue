<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import MahasiswaDetailTab from '@/components/mahasiswa/MahasiswaDetailTab.vue';
import MahasiswaKrsTab from '@/components/mahasiswa/MahasiswaKrsTab.vue';
import MahasiswaNilaiTab from '@/components/mahasiswa/MahasiswaNilaiTab.vue';

interface TahunAkademik {
    id: number;
    nama_semester: string;
    is_active: boolean;
}

interface Mahasiswa {
    id: number;
    id_mahasiswa: string;
    nim: string;
    nama: string;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    ttl: string;
    jenis_kelamin: string | null;
    alamat: string | null;
    dusun: string | null;
    rt: string | null;
    rw: string | null;
    kelurahan: string | null;
    kode_pos: string | null;
    alamat_lengkap: string;
    no_hp: string | null;
    email: string | null;
    nama_ayah: string | null;
    nama_ibu: string | null;
    pekerjaan_ayah: string | null;
    pekerjaan_ibu: string | null;
    program_studi: string | null;
    jenjang: string | null;
    angkatan: string | null;
    status: string | null;
    ipk: number | null;
    sks_tempuh: number | null;
    dosen_wali: string | null;
}

interface KrsDetail {
    kode: string;
    nama: string;
    sks: number;
    kelas: string;
    dosen_pengajar: string[] | null;
    nama_dosen: string | null;
}

interface Krs {
    id: number;
    tahun_akademik_id: number;
    semester: string;
    total_sks: number;
    details: KrsDetail[];
}

interface NilaiItem {
    kode: string;
    nama: string;
    sks: number;
    nilai_huruf: string;
    nilai_angka: number;
    nilai_indeks: number;
}

interface NilaiGroup {
    tahun_akademik_id: number;
    semester: string;
    list: NilaiItem[];
}

const props = defineProps<{
    mahasiswa: Mahasiswa;
    tahunAkademik: TahunAkademik[];
    krs: Krs[];
    nilai: NilaiGroup[];
    dosen: { id: number; nama: string }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Mahasiswa', href: '/admin/mahasiswa' },
    { title: 'Detail', href: '#' },
];

const activeTab = ref('detail');
const isSyncing = ref(false);

const syncDetail = () => {
    if (!confirm('Update data biodata (termasmasuk orang tua) dari Neo Feeder?')) return;
    
    isSyncing.value = true;
    router.post('/admin/sync/mahasiswa/detail', { 
        id: props.mahasiswa.id 
    }, {
        preserveScroll: true,
        onFinish: () => {
            isSyncing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Detail - ${mahasiswa.nama}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ mahasiswa.nama }}</h1>
                    <p class="text-muted-foreground">{{ mahasiswa.nim }} • {{ mahasiswa.program_studi }}</p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="syncDetail"
                        :disabled="isSyncing"
                        class="inline-flex items-center px-4 py-2 border border-primary text-primary font-medium rounded-lg hover:bg-primary/5 transition disabled:opacity-50"
                    >
                        <svg v-if="isSyncing" class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ isSyncing ? 'Syncing...' : 'Sync Data Lengkap' }}
                    </button>
                    <a
                        :href="`/admin/mahasiswa/${mahasiswa.id}/transkrip/print?jenis=reguler`"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-amber-600 text-white font-medium rounded-lg hover:bg-amber-700 transition"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Transkrip
                    </a>
                </div>
            </div>

            <!-- Sub Menu Tabs -->
            <div class="flex flex-wrap gap-2 border-b pb-4 overflow-x-auto">
                <button
                    @click="activeTab = 'detail'"
                    :class="['px-4 py-2 text-sm font-medium rounded shadow-sm transition whitespace-nowrap', activeTab === 'detail' ? 'bg-blue-500 text-white' : 'bg-white border text-gray-700 hover:bg-gray-50']"
                >
                    DETAIL MAHASISWA
                </button>
                <button
                    @click="activeTab = 'krs'"
                    :class="['px-4 py-2 text-sm font-medium rounded shadow-sm transition whitespace-nowrap', activeTab === 'krs' ? 'bg-blue-500 text-white' : 'bg-white border text-gray-700 hover:bg-gray-50']"
                >
                    KRS MAHASISWA
                </button>
                <button
                    @click="activeTab = 'nilai'"
                    :class="['px-4 py-2 text-sm font-medium rounded shadow-sm transition whitespace-nowrap', activeTab === 'nilai' ? 'bg-blue-500 text-white' : 'bg-white border text-gray-700 hover:bg-gray-50']"
                >
                    HISTORI NILAI
                </button>
                <div class="flex-1"></div>
            </div>

            <!-- Tab Content -->
            <MahasiswaDetailTab
                v-if="activeTab === 'detail'"
                :mahasiswa="mahasiswa"
                :dosen="dosen"
            />

            <MahasiswaKrsTab
                v-if="activeTab === 'krs'"
                :krs="krs"
                :mahasiswa-id="mahasiswa.id"
            />

            <MahasiswaNilaiTab
                v-if="activeTab === 'nilai'"
                :nilai="nilai"
                :mahasiswa-id="mahasiswa.id"
            />
        </div>
    </AppLayout>
</template>
