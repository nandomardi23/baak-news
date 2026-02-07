<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
    prodi: string;
    angkatan: string;
}

defineProps<{
    mahasiswa: Mahasiswa[];
    search: string;
}>();

const isMobileMenuOpen = ref(false);
</script>

<template>
    <Head :title="`Hasil Pencarian: ${search}`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50/30 text-slate-800">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-lg border-b border-slate-100 shadow-sm">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 text-white group-hover:shadow-blue-500/40 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-indigo-600">SHT-BAAK</span>
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

            <!-- Main Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/60 shadow-xl shadow-slate-200/50">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-6 pb-6 border-b border-slate-100">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900">Hasil Pencarian</h2>
                        <p class="text-slate-500 text-sm">Kata kunci: "<span class="font-medium text-blue-600">{{ search }}</span>"</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="mahasiswa.length === 0" class="text-center py-16">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-6 text-lg font-semibold text-slate-700">Data mahasiswa tidak ditemukan</h3>
                    <p class="mt-2 text-slate-500 text-sm">Pastikan nama atau NIM yang Anda masukkan sudah benar</p>
                    <Link href="/" class="inline-flex items-center mt-6 px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                        Coba Lagi
                    </Link>
                </div>

                <!-- Results List -->
                <div v-else class="space-y-4">
                    <div
                        v-for="mhs in mahasiswa"
                        :key="mhs.id"
                        class="bg-gradient-to-r from-slate-50 to-blue-50/50 rounded-2xl p-5 border border-slate-100 hover:border-blue-200 hover:shadow-lg transition-all group"
                    >
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-700 transition">
                                    {{ mhs.nama }}
                                </h3>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                    <span class="text-slate-500">
                                        <span class="text-slate-400 font-medium">NIM:&nbsp;</span>
                                        <span class="text-slate-700 font-mono">{{ mhs.nim }}</span>
                                    </span>
                                    <span class="text-slate-500">
                                        <span class="text-slate-400 font-medium">Prodi:&nbsp;</span>
                                        <span class="text-slate-700">{{ mhs.prodi }}</span>
                                    </span>
                                    <span class="text-slate-500">
                                        <span class="text-slate-400 font-medium">Angkatan:&nbsp;</span>
                                        <span class="text-slate-700">{{ mhs.angkatan }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <Link
                                    :href="`/dokumen/${mhs.id}`"
                                    class="inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-teal-700 transition shadow-lg shadow-emerald-500/30"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Akademik
                                </Link>
                                <Link
                                    :href="`/pengajuan/${mhs.id}`"
                                    class="inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg shadow-blue-500/30"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    Ajukan Surat
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Count -->
                <div v-if="mahasiswa.length > 0" class="mt-6 pt-6 border-t border-slate-100 text-center">
                    <p class="text-slate-500 text-sm">
                        Ditemukan <span class="font-bold text-blue-600">{{ mahasiswa.length }}</span> mahasiswa
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
