<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
    prodi: string;
    angkatan: string;
    ipk: string;
    sks_tempuh: number;
}

interface Semester {
    id: number;
    nama: string;
    has_krs: boolean;
    has_nilai: boolean;
}

interface Pengajuan {
    id: number;
    jenis_surat: string;
    status: string;
    status_label: string;
    status_badge: string;
    created_at: string;
}

const props = defineProps<{
    mahasiswa: Mahasiswa;
    semesters: Semester[];
    existingPending: boolean;
    recentPengajuan: Pengajuan[];
}>();

const isMobileMenuOpen = ref(false);

const getBadgeClass = (badge: string) => {
    const classes: Record<string, string> = {
        warning: 'bg-amber-100 text-amber-800',
        success: 'bg-emerald-100 text-emerald-800',
        danger: 'bg-red-100 text-red-800',
        info: 'bg-blue-100 text-blue-800',
    };
    return classes[badge] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Dokumen - ${mahasiswa.nama}`" />

    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50/30 text-slate-800">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-lg border-b border-slate-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <Link href="/" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/20 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-indigo-600">SHT-BAAK</span>
                    </Link>
                    <div class="hidden md:flex items-center gap-4">
                        <Link href="/" class="text-slate-600 hover:text-blue-600 font-medium transition">Beranda</Link>
                        <Link href="/login" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">Login Admin</Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-5xl mx-auto py-8 px-4 sm:py-12">
            <!-- Back Button -->
            <Link href="/" class="inline-flex items-center text-slate-500 hover:text-blue-600 mb-6 transition font-medium group">
                <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Beranda
            </Link>

            <!-- Student Info Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/60 shadow-xl shadow-slate-200/50 mb-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-blue-500/30">
                        {{ mahasiswa.nama.charAt(0) }}
                    </div>
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-slate-900">{{ mahasiswa.nama }}</h1>
                        <p class="text-slate-500">{{ mahasiswa.nim }} • {{ mahasiswa.prodi }}</p>
                    </div>
                    <div class="flex gap-4 text-center">
                        <div class="px-4 py-2 bg-blue-50 rounded-xl">
                            <p class="text-2xl font-bold text-blue-600">{{ mahasiswa.ipk }}</p>
                            <p class="text-xs text-slate-500">IPK</p>
                        </div>
                        <div class="px-4 py-2 bg-emerald-50 rounded-xl">
                            <p class="text-2xl font-bold text-emerald-600">{{ mahasiswa.sks_tempuh }}</p>
                            <p class="text-xs text-slate-500">SKS</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Grid -->
            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Main Documents Section -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Semester Documents -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Dokumen Per Semester</h2>
                                <p class="text-sm text-slate-500">Cetak KRS & KHS</p>
                            </div>
                        </div>

                        <div v-if="semesters.length > 0" class="space-y-3">
                            <div v-for="sem in semesters" :key="sem.id" 
                                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ sem.nama }}</p>
                                    <div class="flex gap-2 mt-1">
                                        <span v-if="sem.has_krs" class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">KRS</span>
                                        <span v-if="sem.has_nilai" class="text-xs px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full">Nilai</span>
                                    </div>
                                </div>
                                <div class="flex gap-2 flex-wrap">
                                    <a v-if="sem.has_krs" 
                                        :href="`/dokumen/${mahasiswa.id}/krs/${sem.id}/print`" 
                                        target="_blank"
                                        class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        KRS
                                    </a>
                                    <a v-if="sem.has_nilai" 
                                        :href="`/dokumen/${mahasiswa.id}/khs/${sem.id}/print`" 
                                        target="_blank"
                                        class="px-3 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        KHS
                                    </a>
                                    <a v-if="sem.has_krs" 
                                        :href="`/dokumen/${mahasiswa.id}/kartu-ujian/${sem.id}/print`" 
                                        target="_blank"
                                        class="px-3 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 transition flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Kartu Ujian
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>Belum ada data semester</p>
                        </div>
                    </div>

                    <!-- Transkrip Section -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Transkrip Nilai</h2>
                                <p class="text-sm text-slate-500">Cetak transkrip lengkap</p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <a :href="`/dokumen/${mahasiswa.id}/transkrip/reguler`" 
                                target="_blank"
                                class="flex-1 py-3 bg-purple-600 text-white font-medium rounded-xl hover:bg-purple-700 transition text-center">
                                Cetak Transkrip Reguler
                            </a>
                            <a :href="`/dokumen/${mahasiswa.id}/transkrip/rpl`" 
                                target="_blank"
                                class="flex-1 py-3 bg-indigo-600 text-white font-medium rounded-xl hover:bg-indigo-700 transition text-center">
                                Cetak Transkrip RPL
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Surat Keterangan -->
                <div class="space-y-6">
                    <!-- Request Letter -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Surat Keterangan</h2>
                                <p class="text-sm text-slate-500">Perlu approval admin</p>
                            </div>
                        </div>
                        
                        <Link :href="`/pengajuan/${mahasiswa.id}`"
                            class="block w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-medium rounded-xl hover:from-amber-600 hover:to-orange-600 transition text-center shadow-lg shadow-amber-500/20">
                            Ajukan Surat Keterangan
                        </Link>
                        
                        <p class="text-xs text-slate-400 mt-3 text-center">
                            Untuk surat yang memerlukan tanda tangan resmi
                        </p>
                    </div>

                    <!-- Recent Requests -->
                    <div v-if="recentPengajuan.length > 0" class="bg-white rounded-2xl p-6 border border-slate-200/60 shadow-lg">
                        <h3 class="font-bold text-slate-900 mb-4">Pengajuan Terakhir</h3>
                        <div class="space-y-3">
                            <div v-for="p in recentPengajuan" :key="p.id" class="flex items-center justify-between text-sm">
                                <div>
                                    <p class="font-medium text-slate-700">{{ p.jenis_surat }}</p>
                                    <p class="text-xs text-slate-400">{{ p.created_at }}</p>
                                </div>
                                <span :class="getBadgeClass(p.status_badge)" class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ p.status_label }}
                                </span>
                            </div>
                        </div>
                        <Link :href="`/status/${mahasiswa.id}`" class="block mt-4 text-center text-sm text-blue-600 hover:underline">
                            Lihat Semua →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
