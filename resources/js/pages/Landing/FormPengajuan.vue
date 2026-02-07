<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

const isMobileMenuOpen = ref(false);

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
    kelurahan: props.mahasiswa.kelurahan || '',
    kecamatan: props.mahasiswa.kecamatan || '',
    kota_kabupaten: props.mahasiswa.kota_kabupaten || '',
    provinsi: props.mahasiswa.provinsi || '',
    no_hp: props.mahasiswa.no_hp || '',
    nama_ayah: props.mahasiswa.nama_ayah || '',
    pekerjaan_ayah: props.mahasiswa.pekerjaan_ayah || '',
    nama_ibu: props.mahasiswa.nama_ibu || '',
    pekerjaan_ibu: props.mahasiswa.pekerjaan_ibu || '',
    alamat_ortu: props.mahasiswa.alamat_ortu || '',
    rt_ortu: props.mahasiswa.rt_ortu || '',
    rw_ortu: props.mahasiswa.rw_ortu || '',
    kelurahan_ortu: props.mahasiswa.kelurahan_ortu || '',
    kecamatan_ortu: props.mahasiswa.kecamatan_ortu || '',
    kota_kabupaten_ortu: props.mahasiswa.kota_kabupaten_ortu || '',
    provinsi_ortu: props.mahasiswa.provinsi_ortu || '',
});

const jenisSuratOptions = [
    { value: 'aktif_kuliah', label: 'Surat Keterangan Aktif Kuliah', icon: '📄' },
];

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
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-blue-50/30 text-slate-800">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-lg border-b border-slate-100 shadow-sm">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-linear-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 text-white group-hover:shadow-blue-500/40 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-linear-to-r from-blue-700 to-indigo-600">SHT-BAAK</span>
                    </Link>
                    <!-- Mobile Menu Button -->
                    <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="md:hidden p-2 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <!-- Desktop Nav -->
                    <div class="hidden md:flex items-center gap-4">
                        <Link href="/" class="text-slate-600 hover:text-blue-600 font-medium transition">Beranda</Link>
                        <Link href="/profil" class="text-slate-600 hover:text-blue-600 font-medium transition">Profil</Link>
                        <Link href="/login" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">Login Admin</Link>
                    </div>
                </div>
            </div>
            <!-- Mobile Menu -->
            <div v-if="isMobileMenuOpen" class="md:hidden border-t border-slate-100 bg-white/95 backdrop-blur-lg">
                <div class="px-4 py-3 space-y-2">
                    <Link href="/" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">Beranda</Link>
                    <Link href="/profil" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">Profil</Link>
                    <Link href="/login" class="block px-4 py-2 bg-blue-600 text-white font-medium rounded-lg text-center">Login Admin</Link>
                </div>
            </div>
        </nav>

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

            <!-- Main Form Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/60 shadow-xl shadow-slate-200/50">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-8 pb-6 border-b border-slate-100">
                    <div class="w-14 h-14 bg-linear-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">{{ pageTitle }}</h2>
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
                    <!-- Jenis Surat Card Selection -->
                    <div>
                        <label class="block text-slate-700 font-bold mb-3">Jenis Surat <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
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

                    <!-- Jenis Transkrip -->
                    <div v-if="showJenisTranskrip">
                        <label class="block text-slate-700 font-bold mb-3">Jenis Transkrip <span class="text-red-500">*</span></label>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <label :class="[form.jenis_transkrip === 'reguler' ? 'ring-2 ring-blue-500 border-blue-200 bg-blue-50' : 'hover:border-slate-300']"
                                class="flex-1 cursor-pointer flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 transition-all">
                                <input v-model="form.jenis_transkrip" type="radio" value="reguler" class="w-5 h-5 text-blue-600"/>
                                <span class="font-semibold text-slate-700">Reguler</span>
                            </label>
                            <label :class="[form.jenis_transkrip === 'rpl' ? 'ring-2 ring-blue-500 border-blue-200 bg-blue-50' : 'hover:border-slate-300']"
                                class="flex-1 cursor-pointer flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 transition-all">
                                <input v-model="form.jenis_transkrip" type="radio" value="rpl" class="w-5 h-5 text-blue-600"/>
                                <span class="font-semibold text-slate-700">RPL (Rekognisi)</span>
                            </label>
                        </div>
                    </div>

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
                                <input v-model="form.tanggal_lahir" type="date"
                                    class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-slate-600 text-sm font-medium mb-2">Alamat</label>
                            <textarea v-model="form.alamat" @blur="form.alamat = toTitleCase(form.alamat)" rows="2" placeholder="Alamat lengkap"
                                class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-4 gap-3 mt-3">
                            <input v-model="form.rt" type="text" placeholder="RT" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center"/>
                            <input v-model="form.rw" type="text" placeholder="RW" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center"/>
                            <input v-model="form.kelurahan" @blur="form.kelurahan = toTitleCase(form.kelurahan)" type="text" placeholder="Kelurahan" class="col-span-2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                        </div>
                        <div class="grid sm:grid-cols-3 gap-3 mt-3">
                            <input v-model="form.kecamatan" @blur="form.kecamatan = toTitleCase(form.kecamatan)" type="text" placeholder="Kecamatan" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            <input v-model="form.kota_kabupaten" @blur="form.kota_kabupaten = toTitleCase(form.kota_kabupaten)" type="text" placeholder="Kota/Kab" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            <input v-model="form.provinsi" @blur="form.provinsi = toTitleCase(form.provinsi)" type="text" placeholder="Provinsi" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
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
                        <div class="mt-3">
                            <label class="block text-slate-600 text-sm font-medium mb-2">Alamat Orang Tua</label>
                            <textarea v-model="form.alamat_ortu" @blur="form.alamat_ortu = toTitleCase(form.alamat_ortu)" rows="2" placeholder="Alamat Orang Tua (jika berbeda)"
                                class="w-full px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none"></textarea>
                        </div>
                        <div class="grid grid-cols-4 gap-3 mt-3">
                            <input v-model="form.rt_ortu" type="text" placeholder="RT" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center"/>
                            <input v-model="form.rw_ortu" type="text" placeholder="RW" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-center"/>
                            <input v-model="form.kelurahan_ortu" @blur="form.kelurahan_ortu = toTitleCase(form.kelurahan_ortu)" type="text" placeholder="Kelurahan" class="col-span-2 px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                        </div>
                        <div class="grid sm:grid-cols-3 gap-3 mt-3">
                            <input v-model="form.kecamatan_ortu" @blur="form.kecamatan_ortu = toTitleCase(form.kecamatan_ortu)" type="text" placeholder="Kecamatan" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            <input v-model="form.kota_kabupaten_ortu" @blur="form.kota_kabupaten_ortu = toTitleCase(form.kota_kabupaten_ortu)" type="text" placeholder="Kota/Kab" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                            <input v-model="form.provinsi_ortu" @blur="form.provinsi_ortu = toTitleCase(form.provinsi_ortu)" type="text" placeholder="Provinsi" class="px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"/>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing || existingPending"
                            class="w-full py-4 bg-linear-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-xl shadow-blue-500/30 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2">
                            <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            <span>{{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
