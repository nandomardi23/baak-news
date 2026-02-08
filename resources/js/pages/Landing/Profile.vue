<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Pejabat {
    id: number;
    nama: string;
    jabatan: string;
    nip: string;
}

defineProps<{
    pejabat: Pejabat[];
}>();

const isMobileMenuOpen = ref(false);
</script>

<template>
    <Head title="Profil BAAK - STIKES Hang Tuah Tanjungpinang" />

    <div class="min-h-screen bg-white text-slate-800 font-sans selection:bg-blue-100 selection:text-blue-900 relative overflow-hidden">
        <!-- Background Decoration (Pemanis) -->
        <div class="absolute top-0 inset-x-0 h-[400px] bg-linear-to-b from-blue-50 via-white to-transparent -z-10 pointer-events-none"></div>
        <div class="absolute -top-40 -left-20 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
        <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-cyan-400/5 rounded-full blur-3xl -z-10"></div>

        <!-- Navbar -->
        <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    <Link href="/" class="flex items-center gap-3">
                         <!-- Logo Icon wrapper -->
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/20 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-linear-to-r from-blue-700 to-blue-500">SHT-BAAK</h1>
                    </Link>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-8">
                        <Link href="/" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Home</Link>
                        <Link href="/profil" class="text-sm font-medium text-blue-600 transition">Profil</Link>
                        <a href="/#alur" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Alur Sistem</a>
                        <a href="/#layanan" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Layanan</a>
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
                        <Link href="/" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Home</Link>
                        <Link href="/profil" class="block px-3 py-3 rounded-xl text-base font-medium text-blue-600 bg-blue-50 transition">Profil</Link>
                        <a href="/#alur" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Alur Sistem</a>
                        <a href="/#layanan" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Layanan</a>
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
        <section class="py-16 px-4">
            <div class="w-full mx-auto text-center">
                <span class="inline-block py-1 px-3 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-xs font-semibold mb-6">
                    Mengenal Kami Lebih Dekat
                </span>
                <h1 class="text-4xl md:text-5xl font-bold text-slate-900 mb-6 leading-tight">
                    Profil BAAK
                </h1>
                <p class="text-lg text-slate-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Biro Administrasi Akademik dan Kemahasiswaan (BAAK) STIKES Hang Tuah Tanjungpinang
                </p>
            </div>
        </section>

        <!-- Tentang Kami & Visi Misi -->
        <section class="py-12 px-4 bg-white">
            <div class="w-full mx-auto">
                <div class="grid md:grid-cols-2 gap-12 items-start">
                    <!-- Tentang Kami -->
                    <div class="bg-slate-50 rounded-3xl p-8 border border-slate-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mb-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900 mb-4">Tentang Kami</h2>
                        <div class="prose prose-slate text-slate-600">
                            <p>
                                Biro Administrasi Akademik dan Kemahasiswaan (BAAK) adalah unit kerja di STIKES Hang Tuah Tanjungpinang yang bertugas memberikan layanan administrasi di bidang akademik dan kemahasiswaan.
                            </p>
                            <p class="mt-4">
                                Kami berkomitmen untuk memberikan pelayanan yang prima, cepat, tepat, dan transparan kepada seluruh civitas akademika, khususnya mahasiswa, dalam rangka mendukung kelancaran proses belajar mengajar dan kegiatan kemahasiswaan.
                            </p>
                        </div>
                    </div>

                    <!-- Visi & Misi -->
                    <div class="space-y-6">
                        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
                            <h3 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg></span>
                                Visi
                            </h3>
                            <p class="text-slate-600 leading-relaxed">
                                Menjadi pusat layanan administrasi akademik dan kemahasiswaan yang profesional, berbasis teknologi informasi, dan unggul dalam mendukung terciptanya lulusan yang berkompeten.
                            </p>
                        </div>
                        
                        <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-sm">
                            <h3 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></span>
                                Misi
                            </h3>
                            <ul class="list-disc list-outside ml-4 text-slate-600 space-y-2">
                                <li>Menyediakan layanan administrasi akademik yang akurat dan tepat waktu.</li>
                                <li>Mengembangkan sistem informasi akademik yang terintegrasi.</li>
                                <li>Meningkatkan kualitas layanan kemahasiswaan dan alumni.</li>
                                <li>Mewujudkan tata kelola administrasi yang transparan dan akuntabel.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Struktur Organisasi / Tim Kami -->
        <section class="py-16 px-4 bg-slate-50 border-t border-slate-200">
            <div class="w-full mx-auto">
                <div class="text-center mb-12">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Tim Kami</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Struktur Organisasi</h2>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="p in pejabat" :key="p.id" class="bg-white rounded-2xl p-6 border border-slate-200 hover:shadow-lg transition group">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-linear-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center text-blue-600 text-xl font-bold border-2 border-white shadow-sm shrink-0">
                                {{ p.nama.substring(0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 group-hover:text-blue-600 transition">{{ p.nama }}</h3>
                                <p class="text-sm text-blue-600 font-medium mb-1">{{ p.jabatan }}</p>
                                <p class="text-xs text-slate-500 font-mono" v-if="p.nip">NIP: {{ p.nip }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400 py-16">
            <div class="w-full mx-auto px-4">
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
