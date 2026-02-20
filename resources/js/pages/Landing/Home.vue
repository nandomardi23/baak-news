<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';

interface Prodi {
    id: number;
    nama_prodi: string;
}

defineProps<{
    prodi: Prodi[];
}>();

const search = ref('');

const handleSearch = () => {
    if (search.value.length >= 3) {
        router.get('/search', { search: search.value });
    }
};
</script>

<template>
    <Head title="BAAK - STIKES Hang Tuah Tanjungpinang" />

    <LandingLayout variant="full" :show-background="true" :show-footer="true">
        <!-- Hero Section -->
        <section id="home" class="py-24 px-4 relative">
            <div class="w-full mx-auto text-center">
                <span class="inline-block py-1 px-3 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-xs font-semibold mb-6">
                    Sistem Pelayanan Akademik Online
                </span>
                <h1 class="text-5xl md:text-6xl font-bold text-slate-900 mb-6 leading-tight">
                    Selamat Datang di<br />
                    <span class="bg-clip-text text-transparent bg-linear-to-r from-blue-600 to-cyan-500">
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
            <div class="w-full mx-auto">
                <div class="text-center mb-16">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Panduan</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Alur Pengajuan Surat</h2>
                </div>
                
                <div class="grid md:grid-cols-4 gap-8 relative">
                    <!-- Connector Line (Hidden on mobile) -->
                    <div class="hidden md:block absolute top-10 left-0 right-0 h-0.5 bg-linear-to-r from-blue-100 via-blue-200 to-blue-100 -z-10"></div>
                    
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
                        <p class="text-slate-500 text-sm">Rekapitulasi seluruh nilai akademik (Reguler & RPL).</p>
                    </div>
                </div>
            </div>
        </section>

    </LandingLayout>
</template>
