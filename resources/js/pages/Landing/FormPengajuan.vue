<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted, watch } from 'vue';
import axios from 'axios';
import LandingLayout from '@/layouts/LandingLayout.vue';
import SeoHead from '@/components/SeoHead.vue';
import DatePicker from 'primevue/datepicker';

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
    tempat_lahir: string | null;
    tanggal_lahir: string | null;
    alamat: string | null;
    rt: string | null;
    rw: string | null;
    kelurahan: string | null;
    kecamatan: string | null;
    kota_kabupaten: string | null;
    provinsi: string | null;
    no_hp: string | null;
    prodi: string;
    jenis_program: string;
    angkatan: string;
    nama_ayah: string | null;
    pekerjaan_ayah: string | null;
    nama_ibu: string | null;
    pekerjaan_ibu: string | null;
    alamat_ortu: string | null;
    rt_ortu: string | null;
    rw_ortu: string | null;
    kelurahan_ortu: string | null;
    kecamatan_ortu: string | null;
    kota_kabupaten_ortu: string | null;
    provinsi_ortu: string | null;
    status: string;
}

interface Semester {
    id: number;
    nama: string;
}

const props = defineProps<{
    mahasiswa: Mahasiswa;
    existingPending: boolean;
    semesters: Semester[];
}>();

const form = useForm({
    jenis_surat: 'aktif_kuliah' as 'aktif_kuliah' | 'krs' | 'khs' | 'transkrip',
    keperluan: '',
    tahun_akademik_id: '' as string | number,
    jenis_transkrip: props.mahasiswa.jenis_program === 'rpl' ? 'rpl' : 'reguler',
    nama: props.mahasiswa.nama, // Add editable name
    tempat_lahir: props.mahasiswa.tempat_lahir || '',
    tanggal_lahir: props.mahasiswa.tanggal_lahir || '',
    alamat: props.mahasiswa.alamat || '',
    rt: props.mahasiswa.rt || '',
    rw: props.mahasiswa.rw || '',
    kelurahan: props.mahasiswa.kelurahan?.toUpperCase() || '',
    kecamatan: props.mahasiswa.kecamatan?.toUpperCase() || '',
    kota_kabupaten: props.mahasiswa.kota_kabupaten?.toUpperCase() || '',
    provinsi: props.mahasiswa.provinsi?.toUpperCase() || '',
    no_hp: props.mahasiswa.no_hp || '',
    nama_ayah: props.mahasiswa.nama_ayah || '',
    pekerjaan_ayah: props.mahasiswa.pekerjaan_ayah || '',
    nama_ibu: props.mahasiswa.nama_ibu || '',
    pekerjaan_ibu: props.mahasiswa.pekerjaan_ibu || '',
    alamat_ortu: props.mahasiswa.alamat_ortu || '',
    rt_ortu: props.mahasiswa.rt_ortu || '',
    rw_ortu: props.mahasiswa.rw_ortu || '',
    kelurahan_ortu: props.mahasiswa.kelurahan_ortu?.toUpperCase() || '',
    kecamatan_ortu: props.mahasiswa.kecamatan_ortu?.toUpperCase() || '',
    kota_kabupaten_ortu: props.mahasiswa.kota_kabupaten_ortu?.toUpperCase() || '',
    provinsi_ortu: props.mahasiswa.provinsi_ortu?.toUpperCase() || '',
});

// Alamat Sama Toggle
const isAlamatSama = ref(false);

watch(isAlamatSama, (newVal) => {
    if (newVal) {
        form.alamat_ortu = form.alamat;
        form.rt_ortu = form.rt;
        form.rw_ortu = form.rw;
        form.provinsi_ortu = form.provinsi;
        form.kota_kabupaten_ortu = form.kota_kabupaten;
        form.kecamatan_ortu = form.kecamatan;
        form.kelurahan_ortu = form.kelurahan;
        
        // Load region lists for parent to match student
        parentRegencies.value = [...regencies.value];
        parentDistricts.value = [...districts.value];
        parentVillages.value = [...villages.value];
    }
});

// Update parent if it's synced and student changes
watch(
    () => [form.alamat, form.rt, form.rw, form.provinsi, form.kota_kabupaten, form.kecamatan, form.kelurahan],
    () => {
        if (isAlamatSama.value) {
            form.alamat_ortu = form.alamat;
            form.rt_ortu = form.rt;
            form.rw_ortu = form.rw;
            form.provinsi_ortu = form.provinsi;
            form.kota_kabupaten_ortu = form.kota_kabupaten;
            form.kecamatan_ortu = form.kecamatan;
            form.kelurahan_ortu = form.kelurahan;
            
            parentRegencies.value = [...regencies.value];
            parentDistricts.value = [...districts.value];
            parentVillages.value = [...villages.value];
        }
    },
    { deep: true }
);

// Emsifa API Integration
interface Region {
    id: string;
    name: string;
}

const provinces = ref<Region[]>([]);
const regencies = ref<Region[]>([]);
const districts = ref<Region[]>([]);
const villages = ref<Region[]>([]);

const parentRegencies = ref<Region[]>([]);
const parentDistricts = ref<Region[]>([]);
const parentVillages = ref<Region[]>([]);

// Fetch Provinces on Load
onMounted(async () => {
    try {
        const response = await axios.get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        provinces.value = response.data;
        
        // Initialize Regencies if province was preset
        if (form.provinsi) {
            const selectedProv = provinces.value.find(p => p.name === form.provinsi);
            if (selectedProv) {
                const regRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`);
                regencies.value = regRes.data;
                
                // Initialize Districts if regency was preset
                if (form.kota_kabupaten) {
                    const selectedReg = regencies.value.find(r => r.name === form.kota_kabupaten);
                    if (selectedReg) {
                        const distRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedReg.id}.json`);
                        districts.value = distRes.data;
                        
                        // Initialize Villages if district was preset
                        if (form.kecamatan) {
                            const selectedDist = districts.value.find(d => d.name === form.kecamatan);
                            if (selectedDist) {
                                const vilRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${selectedDist.id}.json`);
                                villages.value = vilRes.data;
                            }
                        }
                    }
                }
            }
        }
        
        // Similarly for Parent Address
        if (form.provinsi_ortu && !isAlamatSama.value) {
            const selectedPProv = provinces.value.find(p => p.name === form.provinsi_ortu);
            if (selectedPProv) {
                const pRegRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedPProv.id}.json`);
                parentRegencies.value = pRegRes.data;
                
                if (form.kota_kabupaten_ortu) {
                    const selectedPReg = parentRegencies.value.find(r => r.name === form.kota_kabupaten_ortu);
                    if (selectedPReg) {
                        const pDistRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedPReg.id}.json`);
                        parentDistricts.value = pDistRes.data;
                        
                        if (form.kecamatan_ortu) {
                            const selectedPDist = parentDistricts.value.find(d => d.name === form.kecamatan_ortu);
                            if (selectedPDist) {
                                const pVilRes = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${selectedPDist.id}.json`);
                                parentVillages.value = pVilRes.data;
                            }
                        }
                    }
                }
            }
        }
        
    } catch (error) {
        console.error('Error fetching region lists on mount:', error);
    }
});

// Student Selectors
const onProvinsiChange = async () => {
    form.kota_kabupaten = '';
    form.kecamatan = '';
    form.kelurahan = '';
    regencies.value = [];
    districts.value = [];
    villages.value = [];
    
    if (!form.provinsi) return;
    
    // Find ID from name
    const selectedProv = provinces.value.find(p => p.name === form.provinsi);
    if (selectedProv) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`);
            regencies.value = response.data;
        } catch (error) { console.error(error); }
    }
};

const onKotaChange = async () => {
    form.kecamatan = '';
    form.kelurahan = '';
    districts.value = [];
    villages.value = [];
    
    if (!form.kota_kabupaten) return;
    
    const selectedReg = regencies.value.find(r => r.name === form.kota_kabupaten);
    if (selectedReg) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedReg.id}.json`);
            districts.value = response.data;
        } catch (error) { console.error(error); }
    }
};

const onKecamatanChange = async () => {
    form.kelurahan = '';
    villages.value = [];
    
    if (!form.kecamatan) return;
    
    const selectedDist = districts.value.find(d => d.name === form.kecamatan);
    if (selectedDist) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${selectedDist.id}.json`);
            villages.value = response.data;
        } catch (error) { console.error(error); }
    }
};

// Parent Selectors
const onParentProvinsiChange = async () => {
    if (isAlamatSama.value) return; // Ignore if synced
    
    form.kota_kabupaten_ortu = '';
    form.kecamatan_ortu = '';
    form.kelurahan_ortu = '';
    parentRegencies.value = [];
    parentDistricts.value = [];
    parentVillages.value = [];
    
    if (!form.provinsi_ortu) return;
    const selectedProv = provinces.value.find(p => p.name === form.provinsi_ortu);
    if (selectedProv) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProv.id}.json`);
            parentRegencies.value = response.data;
        } catch (error) { console.error(error); }
    }
};

const onParentKotaChange = async () => {
    if (isAlamatSama.value) return;
    
    form.kecamatan_ortu = '';
    form.kelurahan_ortu = '';
    parentDistricts.value = [];
    parentVillages.value = [];
    
    if (!form.kota_kabupaten_ortu) return;
    const selectedReg = parentRegencies.value.find(r => r.name === form.kota_kabupaten_ortu);
    if (selectedReg) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedReg.id}.json`);
            parentDistricts.value = response.data;
        } catch (error) { console.error(error); }
    }
};

const onParentKecamatanChange = async () => {
    if (isAlamatSama.value) return;
    
    form.kelurahan_ortu = '';
    parentVillages.value = [];
    
    if (!form.kecamatan_ortu) return;
    const selectedDist = parentDistricts.value.find(d => d.name === form.kecamatan_ortu);
    if (selectedDist) {
        try {
            const response = await axios.get(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${selectedDist.id}.json`);
            parentVillages.value = response.data;
        } catch (error) { console.error(error); }
    }
};

const isLulus = computed(() => props.mahasiswa.status === 'Lulus');

const allJenisSuratOptions = [
    { value: 'aktif_kuliah', label: 'Surat Keterangan Aktif Kuliah', icon: '📄' },
];

const jenisSuratOptions = computed(() => {
    if (isLulus.value) {
        return allJenisSuratOptions.filter(o => o.value !== 'aktif_kuliah');
    }
    return allJenisSuratOptions;
});

// Reset jenis_surat if current selection is not available
watch(isLulus, (lulus) => {
    if (lulus && form.jenis_surat === 'aktif_kuliah') {
        const available = jenisSuratOptions.value;
        if (available.length > 0) {
            form.jenis_surat = available[0].value as any;
        }
    }
}, { immediate: true });

const keperluanOptions = [
    'Beasiswa', 'Magang / PKL', 'KIP Kuliah', 'BPJS', 'Rekening Bank', 'Keperluan Lainnya',
];

const showKeperluan = computed(() => form.jenis_surat === 'aktif_kuliah');
const showSemester = computed(() => ['krs', 'khs'].includes(form.jenis_surat));
const showJenisTranskrip = computed(() => form.jenis_surat === 'transkrip');

const pageTitle = computed(() => {
    const labels: Record<string, string> = {
        aktif_kuliah: 'Surat Aktif Kuliah', krs: 'Kartu Rencana Studi',
        khs: 'Kartu Hasil Studi', transkrip: 'Transkrip Nilai',
    };
    return `Form Pengajuan ${labels[form.jenis_surat]}`;
});

const toTitleCase = (str: string | null) => {
    if (!str) return '';
    return str.toLowerCase().replace(/\b\w/g, s => s.toUpperCase());
};

const submit = () => {
    form.post(`/pengajuan/${props.mahasiswa.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <SeoHead 
        :title="`${pageTitle} | ${mahasiswa.nama}`" 
        :description="`Isi formulir pengajuan surat keterangan dan dokumen akademik lainnya untuk ${mahasiswa.nama}.`"
    />

    <LandingLayout variant="simple">
        <div class="w-full mx-auto py-8 px-4 sm:py-12">
            <!-- Back Button -->
            <Link href="/" class="inline-flex items-center text-slate-500 hover:text-blue-600 mb-6 transition font-medium group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Beranda
            </Link>

            <!-- Warning if pending exists -->
            <div v-if="existingPending" class="bg-linear-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-5 mb-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-amber-900 font-bold">Pengajuan Masih Diproses</h4>
                        <p class="text-amber-700 text-sm mt-1">Anda sudah memiliki pengajuan yang sedang diproses. Silakan tunggu hingga selesai.</p>
                    </div>
                </div>
            </div>

            <!-- Warning if student is Lulus -->
            <div v-if="isLulus" class="bg-linear-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-5 mb-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-blue-900 font-bold">Status Anda: Lulus 🎓</h4>
                        <p class="text-blue-700 text-sm mt-1">Selamat! Anda telah dinyatakan lulus. Mahasiswa berstatus <strong>Lulus</strong> tidak dapat mengajukan Surat Keterangan Aktif Kuliah karena sudah tidak aktif sebagai mahasiswa.</p>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/60 shadow-xl shadow-slate-200/50">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-100">
                    <div class="w-14 h-14 bg-linear-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ pageTitle }}</h1>
                        <p class="text-slate-500 text-sm">Lengkapi formulir di bawah untuk mengajukan surat</p>
                    </div>
                </div>

                <!-- Data Mahasiswa Card -->
                <div class="bg-linear-to-br from-slate-50 to-blue-50/50 rounded-2xl p-5 sm:p-6 mb-8 border border-slate-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="font-bold text-slate-800">Data Mahasiswa</h3>
                    </div>
                    
                    <div class="grid sm:grid-cols-2 gap-5">
                        <!-- Nama (Editable) -->
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Nama Lengkap <span class="text-blue-500 ml-1 text-[10px] normal-case tracking-normal bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">Bisa dikoreksi</span></label>
                            <input v-model="form.nama" @blur="form.nama = toTitleCase(form.nama)" type="text" 
                                class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-sm"
                                placeholder="Nama sesuai KTM"/>
                            <p v-if="form.errors.nama" class="text-red-500 text-sm mt-1">{{ form.errors.nama }}</p>
                        </div>

                        <!-- NIM (Read-only) -->
                        <div>
                            <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">NIM</label>
                            <div class="relative">
                                <input :value="mahasiswa.nim" type="text" readonly
                                    class="w-full px-4 py-3 rounded-xl bg-slate-100/50 border border-slate-200 text-slate-600 font-mono font-medium focus:outline-none cursor-not-allowed"/>
                                <svg class="w-4 h-4 text-slate-400 absolute right-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>

                        <!-- Prodi (Read-only) -->
                        <div>
                            <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Program Studi</label>
                            <div class="relative">
                                <input :value="mahasiswa.prodi" type="text" readonly
                                    class="w-full px-4 py-3 rounded-xl bg-slate-100/50 border border-slate-200 text-slate-600 font-medium focus:outline-none cursor-not-allowed"/>
                                <svg class="w-4 h-4 text-slate-400 absolute right-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>

                        <!-- Angkatan (Read-only) -->
                        <div>
                            <label class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-2">Angkatan</label>
                            <div class="relative">
                                <input :value="mahasiswa.angkatan" type="text" readonly
                                    class="w-full px-4 py-3 rounded-xl bg-slate-100/50 border border-slate-200 text-slate-600 font-medium focus:outline-none cursor-not-allowed"/>
                                <svg class="w-4 h-4 text-slate-400 absolute right-4 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- No available letter types (Lulus with only aktif_kuliah) -->
                    <div v-if="jenisSuratOptions.length === 0" class="bg-slate-50 border border-slate-200 rounded-2xl p-8 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 mb-2">Tidak Ada Jenis Surat Tersedia</h3>
                        <p class="text-slate-500 text-sm max-w-md mx-auto">Saat ini tidak ada jenis surat yang dapat diajukan untuk status Anda. Mahasiswa berstatus <strong>Lulus</strong> tidak dapat mengajukan Surat Keterangan Aktif Kuliah.</p>
                    </div>

                    <!-- Jenis Surat Card Selection (only show when options available) -->
                    <div v-else>
                        <label class="block text-slate-700 font-bold mb-3">Jenis Surat <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-3">
                            <label v-for="opt in jenisSuratOptions" :key="opt.value"
                                :class="[form.jenis_surat === opt.value ? 'ring-2 ring-blue-500 border-blue-200 bg-blue-50/50' : 'hover:border-slate-300 hover:bg-slate-50']"
                                class="cursor-pointer p-4 rounded-xl border-2 border-slate-200 transition-all">
                                <input v-model="form.jenis_surat" type="radio" :value="opt.value" class="sr-only"/>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ opt.icon }}</span>
                                    <span class="font-semibold text-sm text-slate-700">{{ opt.label }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Static Guide for Aktif Kuliah -->
                    <div v-if="form.jenis_surat === 'aktif_kuliah'" class="bg-amber-50 border border-amber-100 rounded-2xl p-5 mb-6">
                        <div class="flex gap-3">
                            <div class="mt-0.5 text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="space-y-2 text-sm text-amber-800">
                                <h4 class="font-bold text-amber-900">Panduan Pengisian:</h4>
                                <ul class="list-disc pl-4 space-y-1">
                                    <li>Pastikan <strong>Nama</strong> dan <strong>Alamat</strong> ditulis dengan huruf kapital di awal kata (Contoh: <em>Jalan Merdeka</em>, bukan <em>jalan merdeka</em>).</li>
                                    <li>Isi data <strong>Alamat Lengkap</strong> (Nama Jalan, RT, RW, Kelurahan, Kecamatan) agar surat terlihat profesional.</li>
                                    <li>Untuk <strong>Data Orang Tua</strong>, mohon lengkapi pekerjaan dan alamat ayah/ibu dengan detail yang sama untuk keperluan administrasi surat.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Keperluan -->
                    <div v-if="showKeperluan">
                        <label class="block text-slate-700 font-bold mb-2">Keperluan Surat <span class="text-red-500">*</span></label>
                        <select v-model="form.keperluan" required class="w-full px-4 py-3 rounded-xl bg-white border-2 border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="" disabled>Pilih keperluan</option>
                            <option v-for="opt in keperluanOptions" :key="opt" :value="opt">{{ opt }}</option>
                        </select>
                        <p v-if="form.errors.keperluan" class="text-red-500 text-sm mt-1">{{ form.errors.keperluan }}</p>
                    </div>

                    <!-- Semester -->
                    <div v-if="showSemester">
                        <label class="block text-slate-700 font-bold mb-2">Pilih Semester <span class="text-red-500">*</span></label>
                        <select v-model="form.tahun_akademik_id" required class="w-full px-4 py-3 rounded-xl bg-white border-2 border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="" disabled>Pilih semester</option>
                            <option v-for="sem in semesters" :key="sem.id" :value="sem.id">{{ sem.nama }}</option>
                        </select>
                    </div>

                    <!-- Jenis Transkrip -> Hidden since we unified it into just Transkrip Nilai -->

                    <!-- Data Lengkap Section -->
                    <div class="border-t border-slate-100 pt-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Lengkapi Data (Opsional)</h3>
                                <p class="text-slate-500 text-xs">Data ini akan digunakan untuk surat</p>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-slate-600 text-sm font-medium mb-2">Tempat Lahir</label>
                                <input v-model="form.tempat_lahir" @blur="form.tempat_lahir = toTitleCase(form.tempat_lahir)" type="text" placeholder="Contoh: Tanjungpinang"
                                    class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            </div>
                            <div>
                                <label class="block text-slate-600 text-sm font-medium mb-2">Tanggal Lahir</label>
                                <DatePicker 
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
                                    class="w-full" 
                                />
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-slate-600 text-sm font-medium mb-2">Alamat Mahasiswa</label>
                            
                            <div class="grid sm:grid-cols-2 gap-3 mb-3">
                                <select v-model="form.provinsi" @change="onProvinsiChange" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                    <option value="" disabled>Pilih Provinsi</option>
                                    <option v-for="prov in provinces" :key="prov.id" :value="prov.name">{{ prov.name }}</option>
                                </select>
                                
                                <select v-model="form.kota_kabupaten" @change="onKotaChange" :disabled="!form.provinsi" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:bg-slate-50">
                                    <option value="" disabled>Pilih Kota/Kab</option>
                                    <option v-for="reg in regencies" :key="reg.id" :value="reg.name">{{ reg.name }}</option>
                                </select>
                                
                                <select v-model="form.kecamatan" @change="onKecamatanChange" :disabled="!form.kota_kabupaten" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:bg-slate-50">
                                    <option value="" disabled>Pilih Kecamatan</option>
                                    <option v-for="dist in districts" :key="dist.id" :value="dist.name">{{ dist.name }}</option>
                                </select>
                                
                                <select v-model="form.kelurahan" :disabled="!form.kecamatan" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:bg-slate-50">
                                    <option value="" disabled>Pilih Kelurahan/Desa</option>
                                    <option v-for="vil in villages" :key="vil.id" :value="vil.name">{{ vil.name }}</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-4 gap-3">
                                <div class="col-span-4 sm:col-span-3">
                                    <textarea v-model="form.alamat" @blur="form.alamat = toTitleCase(form.alamat)" rows="1" placeholder="Nama jalan, gang, nomor rumah, dll"
                                        class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none"></textarea>
                                </div>
                                <div class="col-span-4 sm:col-span-1 flex gap-3">
                                    <input v-model="form.rt" type="text" placeholder="RT" class="w-1/2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center"/>
                                    <input v-model="form.rw" type="text" placeholder="RW" class="w-1/2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center"/>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <input v-model="form.no_hp" type="tel" placeholder="No. HP / WhatsApp"
                                class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                        </div>
                    </div>

                    <!-- Orang Tua Section -->
                    <div class="border-t border-slate-100 pt-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Data Orang Tua</h3>
                                <p class="text-slate-500 text-xs">Untuk keperluan surat keterangan</p>
                            </div>
                        </div>
                        
                        <!-- Static Guide for Parent Data -->
                        <div v-if="form.jenis_surat === 'aktif_kuliah'" class="bg-amber-50 border border-amber-100 rounded-xl p-4 mb-5">
                            <div class="flex gap-3">
                                <div class="mt-0.5 text-amber-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div class="text-sm text-amber-800">
                                    <p class="font-semibold text-amber-900 mb-1">Penting:</p>
                                    <ul class="list-disc pl-4 space-y-0.5">
                                        <li>Mohon lengkapi <strong>Pekerjaan</strong> Ayah dan Ibu.</li>
                                        <li>Isi <strong>Alamat Orang Tua</strong> secara lengkap (termasuk RT/RW, Kelurahan, Kecamatan) jika berbeda dengan alamat asal.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4">
                            <input v-model="form.nama_ayah" @blur="form.nama_ayah = toTitleCase(form.nama_ayah)" type="text" placeholder="Nama Ayah" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            <input v-model="form.pekerjaan_ayah" @blur="form.pekerjaan_ayah = toTitleCase(form.pekerjaan_ayah)" type="text" placeholder="Pekerjaan Ayah" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            <input v-model="form.nama_ibu" @blur="form.nama_ibu = toTitleCase(form.nama_ibu)" type="text" placeholder="Nama Ibu" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            <input v-model="form.pekerjaan_ibu" @blur="form.pekerjaan_ibu = toTitleCase(form.pekerjaan_ibu)" type="text" placeholder="Pekerjaan Ibu" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                        </div>
                        
                        <!-- Toggle Samakan Alamat -->
                        <div class="mt-6 mb-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <div class="relative flex items-center justify-center w-6 h-6 rounded-md border-2 transition-colors" 
                                     :class="isAlamatSama ? 'bg-blue-600 border-blue-600' : 'bg-white border-slate-300 group-hover:border-blue-400'">
                                    <input type="checkbox" v-model="isAlamatSama" class="sr-only"/>
                                    <svg v-if="isAlamatSama" class="w-4 h-4 text-white pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="font-medium text-slate-700 select-none">Alamat Orang Tua sama dengan alamat Mahasiswa</span>
                            </label>
                        </div>

                        <div class="mt-3">
                            <label class="block text-slate-600 text-sm font-medium mb-2">Alamat Orang Tua</label>
                            
                            <div class="grid sm:grid-cols-2 gap-3 mb-3">
                                <select v-model="form.provinsi_ortu" @change="onParentProvinsiChange" :disabled="isAlamatSama" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:bg-slate-50">
                                    <option value="" disabled>Pilih Provinsi</option>
                                    <option v-for="prov in provinces" :key="prov.id" :value="prov.name">{{ prov.name }}</option>
                                </select>
                                
                                <select v-model="form.kota_kabupaten_ortu" @change="onParentKotaChange" :disabled="!form.provinsi_ortu || isAlamatSama" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:bg-slate-50">
                                    <option value="" disabled>Pilih Kota/Kab</option>
                                    <option v-for="reg in parentRegencies" :key="reg.id" :value="reg.name">{{ reg.name }}</option>
                                </select>
                                
                                <select v-model="form.kecamatan_ortu" @change="onParentKecamatanChange" :disabled="!form.kota_kabupaten_ortu || isAlamatSama" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:bg-slate-50">
                                    <option value="" disabled>Pilih Kecamatan</option>
                                    <option v-for="dist in parentDistricts" :key="dist.id" :value="dist.name">{{ dist.name }}</option>
                                </select>
                                
                                <select v-model="form.kelurahan_ortu" :disabled="!form.kecamatan_ortu || isAlamatSama" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:bg-slate-50">
                                    <option value="" disabled>Pilih Kelurahan/Desa</option>
                                    <option v-for="vil in parentVillages" :key="vil.id" :value="vil.name">{{ vil.name }}</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-4 gap-3">
                                <div class="col-span-4 sm:col-span-3">
                                    <textarea v-model="form.alamat_ortu" :disabled="isAlamatSama" @blur="form.alamat_ortu = toTitleCase(form.alamat_ortu)" rows="1" placeholder="Nama jalan, gang, nomor rumah, dll (jika berbeda)"
                                        class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none disabled:opacity-50 disabled:bg-slate-50"></textarea>
                                </div>
                                <div class="col-span-4 sm:col-span-1 flex gap-3">
                                    <input v-model="form.rt_ortu" type="text" :disabled="isAlamatSama" placeholder="RT" class="w-1/2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center disabled:opacity-50 disabled:bg-slate-50"/>
                                    <input v-model="form.rw_ortu" type="text" :disabled="isAlamatSama" placeholder="RW" class="w-1/2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center disabled:opacity-50 disabled:bg-slate-50"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="pt-4" v-if="jenisSuratOptions.length > 0">
                        <button type="submit" :disabled="form.processing || existingPending"
                            class="w-full py-4 bg-linear-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-xl shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            <span>{{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </LandingLayout>
</template>
