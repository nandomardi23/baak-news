<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Prodi {
    id: number;
    nama_prodi: string;
}

defineProps<{
    prodi: Prodi[];
}>();

const search = ref('');
const isMobileMenuOpen = ref(false);

const handleSearch = () => {
    if (search.value.length >= 3) {
        router.get('/search', { search: search.value });
    }
};
</script>

<template>
    <Head title="BAAK - STIKES Hang Tuah Tanjungpinang" />

    <div class="min-h-screen bg-white text-slate-800 font-sans selection:bg-blue-100 selection:text-blue-900 relative overflow-hidden">
        <!-- Background Decoration (Pemanis) -->
        <div class="absolute top-0 inset-x-0 h-[600px] bg-gradient-to-b from-blue-50 via-white to-transparent -z-10 pointer-events-none"></div>
        <div class="absolute -top-40 -left-20 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
        <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-cyan-400/5 rounded-full blur-3xl -z-10"></div>

        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    <div class="flex items-center gap-3">
                         <!-- Logo Icon wrapper -->
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/20 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-blue-500">SHT-BAAK</h1>
                    </div>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-8">
                        <a href="#home" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Home</a>
                        <a href="#alur" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Alur Sistem</a>
                        <a href="#layanan" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Layanan</a>
                        <Link href="/login" class="px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5">
                            Login Admin
                        </Link>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex md:hidden">
                        <button 
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                            type="button" 
                            class="text-slate-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 p-2"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Panel -->
            <transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform -translate-y-4 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-4 opacity-0"
            >
                <div 
                    v-if="isMobileMenuOpen" 
                    class="md:hidden bg-white border-b border-slate-100 shadow-xl"
                >
                    <div class="px-4 pt-2 pb-6 space-y-2">
                        <a href="#home" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Home</a>
                        <a href="#alur" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Alur Sistem</a>
                        <a href="#layanan" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Layanan</a>
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <Link href="/login" class="block w-full text-center px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-500/30 active:scale-95 transition">
                                Login Admin
                            </Link>
                        </div>
                    </div>
                </div>
            </transition>
        </nav>

        <!-- Hero Section -->
        <section id="home" class="py-24 px-4 relative">
            <div class="max-w-4xl mx-auto text-center">
                <span class="inline-block py-1 px-3 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-xs font-semibold mb-6">
                    Sistem Pelayanan Akademik Online
                </span>
                <h1 class="text-5xl md:text-6xl font-bold text-slate-900 mb-6 leading-tight">
                    Selamat Datang di<br />
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-cyan-500">
                        Biro Administrasi Akademik
                    </span>
                </h1>
                <p class="text-lg text-slate-600 mb-12 max-w-2xl mx-auto leading-relaxed">
                    Kami menyediakan layanan pengajuan surat dan dokumen akademik secara digital untuk mempermudah kebutuhan administrasi mahasiswa STIKES Hang Tuah Tanjungpinang.
                </p>

                <!-- Search Box -->
                <div class="bg-white rounded-3xl p-2 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.1)] border border-slate-100 max-w-3xl mx-auto relative z-10">
                    <div class="bg-slate-50/50 rounded-[1.25rem] p-6 border border-slate-100">
                        <div class="mb-6 text-left">
                            <h2 class="text-xl font-bold text-slate-800 mb-1">Cari Data Mahasiswa</h2>
                            <p class="text-slate-500 text-sm">Masukkan Nama atau NIM untuk memulai pengajuan surat</p>
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
                        <div class="mt-4 flex items-center gap-2 text-xs text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Minimal 3 karakter untuk melakukan pencarian
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Alur Section -->
        <section id="alur" class="py-24 px-4 bg-slate-50/80 border-y border-slate-100">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Panduan</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Alur Pengajuan Surat</h2>
                </div>
                
                <div class="grid md:grid-cols-4 gap-8 relative">
                    <!-- Connector Line (Hidden on mobile) -->
                    <div class="hidden md:block absolute top-[2.5rem] left-0 right-0 h-0.5 bg-gradient-to-r from-blue-100 via-blue-200 to-blue-100 -z-10"></div>
                    
                    <!-- Step 1 -->
                    <div class="relative group">
                        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-md border border-slate-100 group-hover:shadow-lg group-hover:scale-105 transition duration-300 relative z-10">
                            <div class="w-16 h-16 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 text-xl font-bold border border-blue-100">1</div>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 text-center mb-3">Cari Data</h3>
                        <p class="text-slate-500 text-sm text-center leading-relaxed">Cari data mahasiswa Anda menggunakan Nama atau NIM pada form pencarian.</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="relative group">
                        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-md border border-slate-100 group-hover:shadow-lg group-hover:scale-105 transition duration-300 relative z-10">
                            <div class="w-16 h-16 bg-cyan-50 rounded-xl flex items-center justify-center text-cyan-600 text-xl font-bold border border-cyan-100">2</div>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 text-center mb-3">Isi Form</h3>
                        <p class="text-slate-500 text-sm text-center leading-relaxed">Lengkapi formulir pengajuan surat keterangan atau dokumen akademik.</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="relative group">
                        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-md border border-slate-100 group-hover:shadow-lg group-hover:scale-105 transition duration-300 relative z-10">
                            <div class="w-16 h-16 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 text-xl font-bold border border-indigo-100">3</div>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 text-center mb-3">Proses</h3>
                        <p class="text-slate-500 text-sm text-center leading-relaxed">Admin akan memvalidasi data dan memproses dokumen yang Anda ajukan.</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="relative group">
                        <div class="w-20 h-20 bg-white rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-md border border-slate-100 group-hover:shadow-lg group-hover:scale-105 transition duration-300 relative z-10">
                            <div class="w-16 h-16 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600 text-xl font-bold border border-emerald-100">4</div>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 text-center mb-3">Selesai</h3>
                        <p class="text-slate-500 text-sm text-center leading-relaxed">Silakan ambil dokumen fisik di kantor BAAK setelah status selesai.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Layanan Section -->
        <section id="layanan" class="py-24 px-4">
            <div class="max-w-6xl mx-auto">
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
                        <p class="text-slate-500 text-sm">Rekapitulasi seluruh nilai akademik (Reguler & RPL).</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400 py-16">
            <div class="max-w-6xl mx-auto px-4">
                <div class="grid md:grid-cols-4 gap-12 mb-12">
                    <div class="col-span-2">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-blue-600 rounded-md flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                            <h2 class="text-xl font-bold text-white">SHT-BAAK</h2>
                        </div>
                        <p class="leading-relaxed mb-6">
                            Biro Administrasi Akademik Kemahasiswaan STIKES Hang Tuah Tanjungpinang berkomitmen memberikan pelayanan akademik terbaik secara efisien dan transparan.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-white font-bold mb-6">Kontak Kami</h3>
                        <ul class="space-y-4 text-sm">
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span>Jl. WR Supratman, Tanjungpinang Timur, Kepulauan Riau</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                <span>(0771) 4440071</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                <span>stikestpi@gmail.com</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-slate-800 pt-8 text-center text-sm">
                    <p>&copy; 2026 STIKES Hang Tuah Tanjungpinang. All Rights Reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
