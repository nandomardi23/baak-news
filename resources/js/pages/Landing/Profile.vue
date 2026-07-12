<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

interface Pejabat {
    id: number;
    nama: string;
    jabatan: string;
    nip: string;
    nidn: string;
    pangkat_golongan: string;
    foto_path: string;
    periode_awal: string;
    periode_akhir: string;
}

defineProps<{
    pejabat: Pejabat[];
}>();
</script>

<template>
    <SeoHead 
        title="Profil BAAK - STIKES Hang Tuah Tanjungpinang" 
        description="Profil Biro Administrasi Akademik dan Kemahasiswaan (BAAK) STIKES Hang Tuah Tanjungpinang. Visi, misi, dan struktur organisasi."
        type="profile"
    />

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
            <div class="w-full mx-auto max-w-6xl">
                <div class="text-center mb-16">
                    <span class="text-blue-600 font-semibold tracking-wider text-sm uppercase">Tim Kami</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Struktur Organisasi BAAK</h2>
                    <p class="text-slate-500 mt-3 max-w-2xl mx-auto">Mengenal lebih dekat para staf dan pejabat di lingkungan Biro Administrasi Akademik dan Kemahasiswaan.</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="p in pejabat" :key="p.id" class="bg-white rounded-3xl overflow-hidden border border-slate-200 hover:shadow-xl hover:shadow-blue-500/5 hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full">
                        <div class="relative h-48 bg-linear-to-br from-blue-50 to-indigo-50 overflow-hidden flex items-center justify-center border-b border-slate-100">
                            <!-- Decorative background pattern -->
                            <div class="absolute inset-0 opacity-[0.03] group-hover:opacity-[0.05] transition-opacity" style="background-image: radial-gradient(#3b82f6 2px, transparent 2px); background-size: 20px 20px;"></div>
                            
                            <img v-if="p.foto_path" :src="`/storage/${p.foto_path}`" :alt="p.nama" class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-md relative z-10 group-hover:scale-105 transition-transform duration-500" />
                            <div v-else class="w-32 h-32 rounded-full bg-linear-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-4xl font-bold border-4 border-white shadow-md relative z-10 group-hover:scale-105 transition-transform duration-500">
                                {{ p.nama.substring(0, 1).toUpperCase() }}
                            </div>
                            
                            <!-- Badge Periode -->
                            <div v-if="p.periode_awal && p.periode_akhir" class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-[10px] font-bold text-blue-700 shadow-sm border border-blue-100">
                                {{ p.periode_awal }} - {{ p.periode_akhir }}
                            </div>
                        </div>
                        
                        <div class="p-6 flex-1 flex flex-col text-center">
                            <h3 class="font-bold text-lg text-slate-900 group-hover:text-blue-600 transition-colors mb-1">{{ p.nama }}</h3>
                            <p class="text-sm font-semibold text-blue-600 mb-4">{{ p.jabatan }}</p>
                            
                            <div class="mt-auto space-y-2 border-t border-slate-100 pt-4">
                                <div class="flex items-center justify-between text-xs" v-if="p.nip">
                                    <span class="text-slate-500 font-medium flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg> NIP</span>
                                    <span class="text-slate-700 font-mono bg-slate-50 px-2 py-0.5 rounded">{{ p.nip }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs" v-if="p.nidn">
                                    <span class="text-slate-500 font-medium flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> NIDN</span>
                                    <span class="text-slate-700 font-mono bg-slate-50 px-2 py-0.5 rounded">{{ p.nidn }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs" v-if="p.pangkat_golongan">
                                    <span class="text-slate-500 font-medium flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg> Golongan</span>
                                    <span class="text-slate-700 bg-slate-50 px-2 py-0.5 rounded">{{ p.pangkat_golongan }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </LandingLayout>
</template>

