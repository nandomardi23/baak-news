<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits(['search']);
const search = ref('');

const handleSearch = () => {
    emit('search', search.value);
};
</script>

<template>
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
</template>
