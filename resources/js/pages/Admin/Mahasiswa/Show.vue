<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

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
const detailTab = ref('orang_tua');
const selectedSemester = ref<number | null>(null);
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

const isSyncingKrs = ref(false);
const syncKrs = () => {
    if (!confirm('Update Data KRS dari Neo Feeder?')) return;
    
    isSyncingKrs.value = true;
    router.post(`/admin/mahasiswa/${props.mahasiswa.id}/sync-krs`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isSyncingKrs.value = false;
        },
    });
};

const isEditingDosenWali = ref(false);
import { useForm } from '@inertiajs/vue3';

const dosenWaliForm = useForm({
    dosen_wali_id: '' as string | number,
});

const saveDosenWali = () => {
    dosenWaliForm.patch(`/admin/mahasiswa/${props.mahasiswa.id}`, {
        onSuccess: () => {
            isEditingDosenWali.value = false;
            dosenWaliForm.reset();
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
                    :class="['px-4 py-2 text-sm font-medium rounded shadow-sm transition whitespace-nowrap', (activeTab === 'detail' || activeTab === 'biodata') ? 'bg-blue-500 text-white' : 'bg-white border text-gray-700 hover:bg-gray-50']"
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

            <!-- Biodata / Detail Mahasiswa Tab -->
            <div v-if="activeTab === 'detail' || activeTab === 'biodata'" class="space-y-6">
                <!-- ... (Data Mahasiswa stays same) ... -->
                <div class="rounded bg-white border shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-blue-500 mb-6 border-b pb-2">Data Mahasiswa</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama</label>
                                <div class="p-2 bg-indigo-50 border border-indigo-100 rounded text-gray-800 font-medium uppercase">
                                    {{ mahasiswa.nama }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Jenis Kelamin</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">
                                    {{ mahasiswa.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 flex items-center justify-between">
                                    <span>{{ mahasiswa.tanggal_lahir || '-' }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tempat Lahir</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 uppercase">
                                    {{ mahasiswa.tempat_lahir || '-' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ibu</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 uppercase">
                                    {{ mahasiswa.nama_ibu || '-' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Agama</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">
                                    -
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Dosen Wali</label>
                                <div class="p-2 bg-blue-50 border border-blue-200 rounded text-blue-700 font-medium">
                                    {{ mahasiswa.dosen_wali || '-' }}
                                    <button @click="isEditingDosenWali = true" class="ml-2 text-blue-600 hover:text-blue-800 text-xs font-semibold">
                                        (Edit)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Dosen Wali Edit Modal -->
                <div v-if="isEditingDosenWali" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                        <h3 class="text-lg font-bold mb-4">Edit Dosen Wali</h3>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Dosen Wali</label>
                            <select v-model="dosenWaliForm.dosen_wali_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="" disabled>-- Pilih Dosen --</option>
                                <option v-for="d in dosen" :key="d.id" :value="d.id">
                                    {{ d.nama }}
                                </option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button @click="isEditingDosenWali = false" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                            <button @click="saveDosenWali" :disabled="dosenWaliForm.processing" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                                {{ dosenWaliForm.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Informasi Detail Mahasiswa -->
                <div class="rounded bg-white border shadow-sm p-6 min-h-[400px]">
                    <h3 class="text-lg font-semibold text-blue-500 mb-6 border-b pb-2">Informasi Detail Mahasiswa</h3>

                    <!-- Nested Tabs -->
                    <div class="flex justify-center mb-8">
                        <div class="flex border rounded overflow-hidden divide-x">
                            <button 
                                @click="detailTab = 'alamat'"
                                :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'alamat' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                            >
                                ALAMAT
                            </button>
                            <button 
                                @click="detailTab = 'orang_tua'"
                                :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'orang_tua' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                            >
                                ORANG TUA
                            </button>
                            <button 
                                @click="detailTab = 'wali'"
                                :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'wali' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                            >
                                WALI
                            </button>
                            <button 
                                @click="detailTab = 'kebutuhan_khusus'"
                                :class="['px-6 py-2 text-sm font-medium transition', detailTab === 'kebutuhan_khusus' ? 'bg-blue-500 text-white' : 'bg-white text-gray-600 hover:bg-gray-50']"
                            >
                                KEBUTUHAN KHUSUS
                            </button>
                        </div>
                    </div>

                    <!-- Content: Alamat -->
                    <div v-show="detailTab === 'alamat'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Jalan</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.alamat || '-' }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Dusun</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.dusun || '-' }}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">RT</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.rt || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">RW</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.rw || '-' }}</div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Kelurahan</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.kelurahan || '-' }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Kode Pos</label>
                                <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700">{{ mahasiswa.kode_pos || '-' }}</div>
                            </div>
                        </div>
                        <div class="mt-6 p-4 bg-yellow-50 text-yellow-700 text-sm rounded border border-yellow-100 flex items-start gap-2">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Data lengkap alamat tersedia di menu edit atau setelah sinkronisasi detail.</p>
                        </div>
                    </div>

                    <!-- Content: Orang Tua -->
                    <div v-show="detailTab === 'orang_tua'">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <!-- Ayah -->
                            <div class="space-y-4">
                                <h4 class="text-center font-bold text-gray-700 text-lg mb-6">Ayah</h4>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">NIK</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ayah</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px] uppercase">{{ mahasiswa.nama_ayah || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pendidikan</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pekerjaan</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">{{ mahasiswa.pekerjaan_ayah || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Penghasilan</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                            </div>

                            <!-- Ibu -->
                            <div class="space-y-4">
                                <h4 class="text-center font-bold text-gray-700 text-lg mb-6">Ibu</h4>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">NIK</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ibu</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px] uppercase">{{ mahasiswa.nama_ibu || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Lahir</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pendidikan</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pekerjaan</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">{{ mahasiswa.pekerjaan_ibu || '-' }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Penghasilan</label>
                                    <div class="p-2 bg-gray-50 border border-gray-200 rounded text-gray-700 min-h-[40px]">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content: Wali & Kebutuhan Khusus -->
                    <div v-show="detailTab === 'wali'" class="p-8 text-center text-gray-400 italic">
                        Data Wali belum tersedia
                    </div>
                    <div v-show="detailTab === 'kebutuhan_khusus'" class="p-8 text-center text-gray-400 italic">
                        Data Kebutuhan Khusus belum tersedia
                    </div>

                </div>
            </div>

            <!-- KRS Tab -->
            <div v-if="activeTab === 'krs'" class="space-y-6">
                <div class="flex items-center justify-end gap-4">
                    <button
                        @click="syncKrs"
                        :disabled="isSyncingKrs"
                        class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 disabled:opacity-50 flex items-center gap-2"
                    >
                        <svg v-if="isSyncingKrs" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSyncingKrs ? 'Syncing...' : 'Sync Data KRS' }}
                    </button>
                </div>

                <div v-for="krsItem in krs" :key="krsItem.id" class="rounded-xl border bg-card shadow-sm overflow-hidden">
                    <div class="p-4 bg-muted/50 border-b flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold">{{ krsItem.semester }}</h3>
                            <span class="text-sm text-muted-foreground">Total: {{ krsItem.total_sks }} SKS</span>
                        </div>
                        <div class="flex gap-2">
                            <a
                                :href="`/admin/mahasiswa/${mahasiswa.id}/krs/${krsItem.tahun_akademik_id}/print`"
                                target="_blank"
                                class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak KRS
                            </a>
                            <a
                                :href="`/admin/mahasiswa/${mahasiswa.id}/kartu-ujian/${krsItem.tahun_akademik_id}/print`"
                                target="_blank"
                                class="inline-flex items-center px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded hover:bg-amber-700 transition"
                            >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Kartu Ujian
                            </a>
                        </div>
                    </div>
                    <table class="w-full">
                        <thead class="bg-muted/30">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium">No</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Kode</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Mata Kuliah</th>
                                <th class="px-4 py-2 text-center text-sm font-medium">SKS</th>
                                <th class="px-4 py-2 text-center text-sm font-medium">Kelas</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Dosen Pengajar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(detail, idx) in krsItem.details" :key="idx">
                                <td class="px-4 py-2 text-sm">{{ idx + 1 }}</td>
                                <td class="px-4 py-2 text-sm font-mono">{{ detail.kode }}</td>
                                <td class="px-4 py-2 text-sm">{{ detail.nama }}</td>
                                <td class="px-4 py-2 text-sm text-center">{{ detail.sks }}</td>
                                <td class="px-4 py-2 text-sm text-center">{{ detail.kelas }}</td>
                                <td class="px-4 py-2 text-sm text-gray-600">
                                    <div v-if="detail.dosen_pengajar && detail.dosen_pengajar.length > 0" class="flex flex-col gap-0.5">
                                        <span v-for="(dsn, i) in detail.dosen_pengajar" :key="i" class="text-xs">
                                            {{ dsn }}
                                        </span>
                                    </div>
                                    <span v-else>{{ detail.nama_dosen || '-' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="krs.length === 0" class="text-center py-12 text-muted-foreground">
                    Tidak ada data KRS
                </div>
            </div>

            <!-- Nilai Tab -->
            <div v-if="activeTab === 'nilai'" class="space-y-6">

                <div v-for="nilaiGroup in nilai" :key="nilaiGroup.semester" class="rounded-xl border bg-card shadow-sm overflow-hidden">
                    <div class="p-4 bg-muted/50 border-b flex justify-between items-center">
                        <h3 class="font-semibold">{{ nilaiGroup.semester }}</h3>
                        <a
                            :href="`/admin/mahasiswa/${mahasiswa.id}/khs/${nilaiGroup.tahun_akademik_id}/print`"
                            target="_blank"
                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 transition"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak KHS
                        </a>
                    </div>
                    <table class="w-full">
                        <thead class="bg-muted/30">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-medium">No</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Kode</th>
                                <th class="px-4 py-2 text-left text-sm font-medium">Mata Kuliah</th>
                                <th class="px-4 py-2 text-center text-sm font-medium">SKS</th>
                                <th class="px-4 py-2 text-center text-sm font-medium">Nilai</th>
                                <th class="px-4 py-2 text-center text-sm font-medium">Indeks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(item, idx) in nilaiGroup.list" :key="idx">
                                <td class="px-4 py-2 text-sm">{{ idx + 1 }}</td>
                                <td class="px-4 py-2 text-sm font-mono">{{ item.kode }}</td>
                                <td class="px-4 py-2 text-sm">{{ item.nama }}</td>
                                <td class="px-4 py-2 text-sm text-center">{{ item.sks }}</td>
                                <td class="px-4 py-2 text-sm text-center font-semibold">{{ item.nilai_huruf }}</td>
                                <td class="px-4 py-2 text-sm text-center">{{ item.nilai_indeks !== null ? Number(item.nilai_indeks).toFixed(2) : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="nilai.length === 0" class="text-center py-12 text-muted-foreground">
                    Tidak ada data nilai
                </div>
            </div>
        </div>
    </AppLayout>
</template>
