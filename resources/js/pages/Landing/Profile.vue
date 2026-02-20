<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';

interface Pejabat {
    id: number;
    nama: string;
    jabatan: string;
    nip: string;
}

defineProps<{
    pejabat: Pejabat[];
}>();
</script>

<template>
    <Head title="Profil BAAK - STIKES Hang Tuah Tanjungpinang" />

    <LandingLayout variant="full" :show-background="true" :show-footer="true">
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

    </LandingLayout>
</template>
