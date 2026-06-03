<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface DokumenTemplate {
    id: number;
    nama: string;
    deskripsi: string | null;
    kategori: string;
    ukuran_format: string;
    file_type: string;
}

const props = defineProps<{
    prodi: Prodi[];
    templates?: any;
    filters?: any;
}>();

const search = ref('');
const searchTemplate = ref(props.filters?.search_template || '');
const filterKategori = ref(props.filters?.kategori || 'all');
const isDropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const kategoriOptions = [
    { value: 'all', label: 'Semua Kategori' },
    { value: 'Skripsi', label: 'Skripsi' },
    { value: 'Tugas', label: 'Tugas' },
    { value: 'Absen Praktek', label: 'Absen Praktek' },
    { value: 'Laporan', label: 'Laporan' },
    { value: 'Administrasi', label: 'Administrasi' },
    { value: 'Lainnya', label: 'Lainnya' },
];

const selectedLabel = computed(() => {
    const found = kategoriOptions.find(opt => opt.value === filterKategori.value);
    return found ? found.label : 'Semua Kategori';
});

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const selectOption = (value: string) => {
    filterKategori.value = value;
    isDropdownOpen.value = false;
    handleFilterTemplates();
};

const handleClickOutside = (event: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
        isDropdownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleSearch = () => {
    if (search.value.length >= 3) {
        router.get('/search', { search: search.value });
    }
};

const handleFilterTemplates = () => {
    router.get('/', {
        search_template: searchTemplate.value || null,
        kategori: filterKategori.value === 'all' ? null : filterKategori.value
    }, { preserveState: true, preserveScroll: true });
};

// Debounced watcher — auto-filter saat user mengetik
let debounceTimer: ReturnType<typeof setTimeout> | null = null;
watch(searchTemplate, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        handleFilterTemplates();
    }, 400);
});
</script>

<template>
    <SeoHead 
        title="BAAK STIKES Hang Tuah Tanjungpinang - Layanan Administrasi Akademik" 
        description="BAAK STIKES Hang Tuah Tanjungpinang menyediakan layanan administrasi akademik, pengajuan surat, dan dokumen akademik secara digital untuk mahasiswa."
        keywords="BAAK, STIKES Hang Tuah, Tanjungpinang, administrasi akademik, pengajuan surat, dokumen akademik, KRS, KHS, transkrip nilai, surat aktif kuliah"
    />

    <LandingLayout variant="full" :show-background="true" :show-footer="true">
        <!-- Hero Section -->
        <section id="home" class="py-12 sm:py-24 px-4 relative">
            <div class="w-full mx-auto text-center">
                <span class="inline-block py-1 px-3 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-xs font-semibold mb-6">
                    Sistem Pelayanan Akademik Online
                </span>
                <h1 class="text-3xl sm:text-5xl md:text-6xl font-bold text-slate-900 mb-6 leading-tight">
                    Selamat Datang di<br />
                    <span class="bg-clip-text text-transparent bg-linear-to-r from-blue-600 to-cyan-500">
                        Biro Administrasi Akademik
                    </span>
                </h1>
                <p class="text-base sm:text-lg text-slate-600 mb-8 sm:mb-12 max-w-2xl mx-auto leading-relaxed">
                    Kami menyediakan layanan pengajuan surat dan dokumen akademik secara digital untuk mempermudah kebutuhan administrasi mahasiswa STIKES Hang Tuah Tanjungpinang.
                </p>

                <!-- Search Box -->
                <div class="bg-white rounded-3xl p-2 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 max-w-3xl mx-auto relative z-10">
                    <div class="bg-slate-50/50 rounded-[1.25rem] p-6 border border-slate-100">
                        <div class="mb-6 text-left">
                            <h2 class="text-xl font-bold text-slate-800 mb-1">Cari Data Mahasiswa</h2>
                            <p class="text-slate-600 text-sm">Masukkan Nama atau NIM untuk memulai pengajuan surat</p>
                        </div>
                        
                        <form @submit.prevent="handleSearch" class="flex flex-col md:flex-row gap-3">
                            <div class="relative flex-1 group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Ketik Nama atau NIM..."
                                    class="block w-full pl-12 pr-4 py-4 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 transition shadow-sm"
                                />
                            </div>
                            <button
                                type="submit"
                                class="px-8 py-4 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-600/25 flex items-center justify-center gap-2 group"
                            >
                                Cari Data
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </button>
                        </form>
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Minimal 3 karakter untuk melakukan pencarian
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Alur Section -->
        <section id="alur" class="py-12 sm:py-24 px-4 bg-slate-50/80 border-y border-slate-100">
            <div class="w-full mx-auto">
                <div class="text-center mb-16">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Panduan</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Alur Pengajuan Surat</h2>
                </div>
                
                <div class="relative max-w-7xl mx-auto py-10">
                    <!-- Responsive horizontal/vertical connector line -->
                    <div class="hidden lg:block absolute top-[85px] left-[8%] right-[8%] h-0.5 bg-linear-to-r from-blue-300 via-purple-300 to-emerald-300 -z-10 rounded-full"></div>
                    <div class="hidden lg:block absolute top-[85px] left-[8%] right-[8%] h-2 bg-linear-to-r from-blue-500 via-purple-500 to-emerald-500 opacity-20 blur-sm -z-10 rounded-full"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6 lg:gap-4 relative z-0">
                        <!-- Step 1 -->
                        <div class="group relative flex flex-col items-center p-5 bg-white/70 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl shadow-blue-900/5 hover:border-blue-400 hover:shadow-blue-500/10 transition-all duration-300 hover:-translate-y-2">
                            <span class="absolute -top-3 px-3 py-1 rounded-full bg-linear-to-r from-blue-600 to-cyan-500 text-white text-[10px] font-bold tracking-wider shadow-md shadow-blue-500/30">LANGKAH 1</span>
                            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-500 shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5H4a2 2 0 01-2-2v-5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2zM15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 text-center mb-2">Cari Data</h3>
                            <p class="text-slate-500 text-xs text-center leading-relaxed font-medium">Cari data mahasiswa Anda menggunakan nama atau NIM pada form pencarian.</p>
                        </div>

                        <!-- Step 2 -->
                        <div class="group relative flex flex-col items-center p-5 bg-white/70 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl shadow-cyan-900/5 hover:border-cyan-400 hover:shadow-cyan-500/10 transition-all duration-300 hover:-translate-y-2 lg:mt-6">
                            <span class="absolute -top-3 px-3 py-1 rounded-full bg-linear-to-r from-cyan-600 to-teal-500 text-white text-[10px] font-bold tracking-wider shadow-md shadow-cyan-500/30">LANGKAH 2</span>
                            <div class="w-16 h-16 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-4 group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-500 shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 text-center mb-2">Isi Formulir</h3>
                            <p class="text-slate-500 text-xs text-center leading-relaxed font-medium">Lengkapi formulir pengajuan surat keterangan atau dokumen akademik secara saksama.</p>
                        </div>

                        <!-- Step 3 -->
                        <div class="group relative flex flex-col items-center p-5 bg-white/70 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl shadow-indigo-900/5 hover:border-indigo-400 hover:shadow-indigo-500/10 transition-all duration-300 hover:-translate-y-2">
                            <span class="absolute -top-3 px-3 py-1 rounded-full bg-linear-to-r from-indigo-600 to-blue-500 text-white text-[10px] font-bold tracking-wider shadow-md shadow-indigo-500/30">LANGKAH 3</span>
                            <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500 shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 text-center mb-2">Validasi BAAK</h3>
                            <p class="text-slate-500 text-xs text-center leading-relaxed font-medium">Data akan divalidasi oleh pihak BAAK dan tahap pemrosesan dokumen dimulai.</p>
                        </div>

                        <!-- Step 4 -->
                        <div class="group relative flex flex-col items-center p-5 bg-white/70 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl shadow-violet-900/5 hover:border-violet-400 hover:shadow-violet-500/10 transition-all duration-300 hover:-translate-y-2 lg:mt-6">
                            <span class="absolute -top-3 px-3 py-1 rounded-full bg-linear-to-r from-violet-600 to-purple-500 text-white text-[10px] font-bold tracking-wider shadow-md shadow-violet-500/30">LANGKAH 4</span>
                            <div class="w-16 h-16 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center mb-4 group-hover:bg-violet-600 group-hover:text-white transition-colors duration-500 shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 text-center mb-2">Ambil Dokumen</h3>
                            <p class="text-slate-500 text-xs text-center leading-relaxed font-medium">Setelah proses validasi selesai, ambil dokumen fisik Anda di ruangan BAAK.</p>
                        </div>

                        <!-- Step 5 -->
                        <div class="group relative flex flex-col items-center p-5 bg-white/70 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl shadow-purple-900/5 hover:border-purple-400 hover:shadow-purple-500/10 transition-all duration-300 hover:-translate-y-2">
                            <span class="absolute -top-3 px-3 py-1 rounded-full bg-linear-to-r from-purple-600 to-pink-500 text-white text-[10px] font-bold tracking-wider shadow-md shadow-purple-500/30">LANGKAH 5</span>
                            <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-500 shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 text-center mb-2">Tanda Tangan Prodi</h3>
                            <p class="text-slate-500 text-xs text-center leading-relaxed font-medium">Mintalah pengesahan tanda tangan dari dosen program studi terkait pada dokumen.</p>
                        </div>
                        
                        <!-- Step 6 -->
                        <div class="group relative flex flex-col items-center p-5 bg-white/70 backdrop-blur-xl rounded-3xl border border-slate-200/60 shadow-xl shadow-emerald-900/5 hover:border-emerald-400 hover:shadow-emerald-500/10 transition-all duration-300 hover:-translate-y-2 lg:mt-6">
                            <span class="absolute -top-3 px-3 py-1 rounded-full bg-linear-to-r from-emerald-600 to-teal-500 text-white text-[10px] font-bold tracking-wider shadow-md shadow-emerald-500/30">LANGKAH 6</span>
                            <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-500 shadow-inner">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 text-center mb-2">Pengesahan Pimpinan</h3>
                            <p class="text-slate-500 text-xs text-center leading-relaxed font-medium">Ke sekretariat pimpinan untuk mendapat tanda tangan validasi akhir sampai selesai.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Layanan Section -->
        <section id="layanan" class="py-24 px-4">
            <div class="w-full mx-auto">
                <div class="text-center mb-16">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Dokumen</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Layanan Tersedia</h2>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Card 1 -->
                    <div class="group bg-white rounded-2xl p-6 border border-slate-200 hover:border-emerald-500/50 hover:shadow-xl hover:shadow-emerald-500/5 transition duration-300 cursor-default">
                        <div class="w-14 h-14 bg-emerald-50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
                           <svg class="w-7 h-7 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-emerald-700 transition">Surat Aktif Kuliah</h3>
                        <p class="text-slate-500 text-sm">Dokumen resmi yang menyatakan status aktif mahasiswa pada semester berjalan.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="group bg-white rounded-2xl p-6 border border-slate-200 hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/5 transition duration-300 cursor-default">
                        <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                           <svg class="w-7 h-7 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-blue-700 transition">KRS</h3>
                        <p class="text-slate-500 text-sm">Kartu Rencana Studi semester yang sedang diambil.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="group bg-white rounded-2xl p-6 border border-slate-200 hover:border-purple-500/50 hover:shadow-xl hover:shadow-purple-500/5 transition duration-300 cursor-default">
                        <div class="w-14 h-14 bg-purple-50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300">
                           <svg class="w-7 h-7 text-purple-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-purple-700 transition">KHS</h3>
                        <p class="text-slate-500 text-sm">Kartu Hasil Studi berisi nilai mata kuliah per semester.</p>
                    </div>

                    <!-- Card 4 -->
                    <div class="group bg-white rounded-2xl p-6 border border-slate-200 hover:border-amber-500/50 hover:shadow-xl hover:shadow-amber-500/5 transition duration-300 cursor-default">
                        <div class="w-14 h-14 bg-amber-50 rounded-xl flex items-center justify-center mb-6 group-hover:bg-amber-500 group-hover:text-white transition-colors duration-300">
                            <svg class="w-7 h-7 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-amber-700 transition">Transkrip Nilai</h3>
                        <p class="text-slate-500 text-sm">Rekapitulasi seluruh nilai akademik mahasiswa.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Links Section -->
        <section class="py-16 px-4">
            <div class="w-full mx-auto max-w-5xl">
                <div class="text-center mb-10">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Navigasi</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Tautan Cepat</h2>
                    <p class="text-slate-500 mt-2">Akses cepat ke halaman-halaman penting</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link href="/profil" class="group flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-200 hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/10 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 group-hover:text-blue-600 transition">Profil BAAK</h3>
                            <p class="text-xs text-slate-500">Visi, misi & struktur</p>
                        </div>
                    </Link>

                    <Link href="/kalender-akademik" class="group flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-200 hover:border-emerald-400 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 group-hover:text-emerald-600 transition">Kalender Akademik</h3>
                            <p class="text-xs text-slate-500">Jadwal kegiatan kampus</p>
                        </div>
                    </Link>

                    <a href="#alur" class="group flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-200 hover:border-violet-400 hover:shadow-lg hover:shadow-violet-500/10 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center group-hover:bg-violet-600 group-hover:text-white transition-colors duration-300 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 group-hover:text-violet-600 transition">Panduan Pengajuan</h3>
                            <p class="text-xs text-slate-500">Alur pengajuan surat</p>
                        </div>
                    </a>

                    <Link href="/login" class="group flex items-center gap-4 p-5 bg-white rounded-2xl border border-slate-200 hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/10 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 group-hover:text-amber-600 transition">Login Admin</h3>
                            <p class="text-xs text-slate-500">Akses panel admin</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Template Dokumen Section -->
        <section id="templates" class="py-24 px-4 bg-slate-50/80 border-t border-slate-100">
            <div class="w-full mx-auto max-w-7xl">
                <div class="text-center mb-16">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Unduhan</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Template Dokumen</h2>
                    <p class="text-slate-500 mt-2">Unduh template dokumen akademik yang Anda perlukan.</p>
                </div>

                <!-- Ada Template -->
                <div v-if="templates && templates.data && templates.data.length > 0" class="overflow-hidden bg-white rounded-2xl border border-slate-200 shadow-sm">
                    <!-- Filters -->
                    <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row gap-4 justify-between items-center">
                        <div class="relative w-full sm:max-w-xs">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <input type="text" v-model="searchTemplate" placeholder="Cari dokumen..." class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow bg-white" />
                        </div>
                        <div class="w-full sm:w-auto flex gap-2">
                            <!-- Custom Modern Dropdown (Simplified) -->
                            <div ref="dropdownRef" class="relative w-full sm:w-56">
                                <button
                                    @click="toggleDropdown"
                                    type="button"
                                    class="flex items-center justify-between w-full px-3 py-2 text-sm border border-slate-300 rounded-md bg-white cursor-pointer focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                >
                                    <span class="text-slate-700">{{ selectedLabel }}</span>
                                    <svg 
                                        class="w-4 h-4 text-slate-500" 
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div
                                    v-if="isDropdownOpen"
                                    class="absolute z-50 mt-1 w-full bg-white border border-slate-300 rounded-md shadow-lg overflow-hidden"
                                >
                                    <div class="py-1">
                                        <button
                                            v-for="option in kategoriOptions"
                                            :key="option.value"
                                            @click="selectOption(option.value)"
                                            type="button"
                                            class="w-full text-left px-3 py-2 text-sm transition-colors cursor-pointer"
                                            :class="filterKategori === option.value 
                                                ? 'bg-blue-600 text-white' 
                                                : 'text-slate-700 hover:bg-blue-600 hover:text-white'"
                                        >
                                            {{ option.label }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Card Layout -->
                    <div class="md:hidden divide-y divide-slate-100">
                        <div v-for="template in templates.data" :key="'mobile-' + template.id" class="p-4 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-[11px] font-semibold uppercase tracking-wide whitespace-nowrap shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    {{ template.kategori }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ template.file_type }}</span>
                                    <span class="text-xs text-slate-500 font-medium">{{ template.ukuran_format }}</span>
                                </div>
                            </div>
                            <div class="font-bold text-slate-900 mb-1 text-sm">{{ template.nama }}</div>
                            <div class="text-xs text-slate-500 leading-relaxed mb-3" v-if="template.deskripsi">{{ template.deskripsi }}</div>
                            <a :href="`/dokumen-template/${template.id}/download`" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm shadow-blue-500/20 active:scale-95 transition-all">
                                Unduh
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Desktop Table Layout -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-sm">
                                    <th class="px-6 py-4 font-semibold w-[15%]">Kategori</th>
                                    <th class="px-6 py-4 font-semibold w-[55%]">Nama Dokumen</th>
                                    <th class="px-6 py-4 font-semibold w-[15%] text-center">Format & Ukuran</th>
                                    <th class="px-6 py-4 font-semibold w-[15%] text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="template in templates.data" :key="'desktop-' + template.id" class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-6 py-5 align-top">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-[11px] font-semibold uppercase tracking-wide whitespace-nowrap">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            {{ template.kategori }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="font-bold text-slate-900 mb-1.5 text-base group-hover:text-blue-700 transition-colors">{{ template.nama }}</div>
                                        <div class="text-sm text-slate-500 leading-relaxed max-w-2xl" v-if="template.deskripsi">{{ template.deskripsi }}</div>
                                    </td>
                                    <td class="px-6 py-5 align-top text-center">
                                        <div class="flex flex-col items-center justify-center h-full gap-1.5 mt-0.5">
                                            <span class="text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ template.file_type }}</span>
                                            <span class="text-xs text-slate-500 font-medium whitespace-nowrap">{{ template.ukuran_format }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top text-center">
                                        <div class="flex items-center justify-center h-full">
                                            <a :href="`/dokumen-template/${template.id}/download`" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm shadow-blue-500/20 active:scale-95 transition-all w-full justify-center">
                                                Unduh
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-4 sm:px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3" v-if="templates.links && templates.links.length > 3">
                         <div class="text-sm text-slate-500 hidden sm:block">
                            Menampilkan <span class="font-semibold text-slate-800">{{ templates.from }}</span> - <span class="font-semibold text-slate-800">{{ templates.to }}</span> dari <span class="font-semibold text-slate-800">{{ templates.total }}</span>
                        </div>
                        <div class="flex items-center gap-1 flex-wrap justify-center">
                            <Link v-for="(link, index) in templates.links" :key="index" :href="link.url || '#'" :class="[
                                'inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors',
                                link.active ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-200',
                                !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                            ]" v-html="link.label.replace('&laquo; Previous', '←').replace('Next &raquo;', '→')" preserve-scroll preserve-state></Link>
                        </div>
                    </div>
                </div>

                <!-- Kosong (Empty State) -->
                <div v-else-if="templates && templates.data && templates.data.length === 0 && (filters?.search_template || filters?.kategori)" class="text-center py-16 bg-white/50 backdrop-blur-sm rounded-3xl border border-dashed border-slate-300 max-w-3xl mx-auto">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-5 text-slate-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Pencarian Tidak Ditemukan</h3>
                    <p class="text-slate-500 text-sm max-w-md mx-auto leading-relaxed">Tidak ada dokumen yang sesuai dengan filter pencarian Anda. Silakan coba kata kunci lain.</p>
                    <button @click="searchTemplate = ''; filterKategori = 'all'; handleFilterTemplates()" class="mt-6 px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold rounded-lg text-sm transition">Reset Pencarian</button>
                </div>

                <!-- Kosong Total -->
                <div v-else class="text-center py-16 bg-white/50 backdrop-blur-sm rounded-3xl border border-dashed border-slate-300 max-w-3xl mx-auto">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-5 text-slate-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Belum Ada Template</h3>
                    <p class="text-slate-500 text-sm max-w-md mx-auto leading-relaxed">Saat ini belum ada dokumen atau template akademik yang diunggah oleh pihak BAAK/Admin untuk dapat diunduh.</p>
                </div>
            </div>
        </section>

    </LandingLayout>
</template>
