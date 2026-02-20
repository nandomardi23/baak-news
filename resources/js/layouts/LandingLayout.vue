<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    /** 'full' = Home/Profile-style (h-20, 4 nav links, footer), 'simple' = inner pages (h-16, 3 nav links, no footer) */
    variant?: 'full' | 'simple';
    /** Whether to show the decorative background blobs */
    showBackground?: boolean;
    /** Whether to include the standard footer */
    showFooter?: boolean;
}>();

const isMobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-white text-slate-800 font-sans selection:bg-blue-100 selection:text-blue-900 relative overflow-hidden">
        <!-- Background Decoration -->
        <template v-if="showBackground">
            <div class="absolute top-0 inset-x-0 h-[600px] bg-linear-to-b from-blue-50 via-white to-transparent -z-10 pointer-events-none"></div>
            <div class="absolute -top-40 -left-20 w-96 h-96 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
            <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-cyan-400/5 rounded-full blur-3xl -z-10"></div>
        </template>

        <!-- Navbar -->
        <nav class="sticky top-0 z-50 border-b border-slate-100 shadow-sm"
            :class="variant === 'simple' ? 'bg-white/90 backdrop-blur-lg' : 'bg-white/80 backdrop-blur-md'">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center" :class="variant === 'simple' ? 'h-16' : 'h-20'">
                    <!-- Logo -->
                    <component :is="variant === 'simple' ? Link : 'div'" :href="variant === 'simple' ? '/' : undefined"
                        class="flex items-center gap-3" :class="variant === 'simple' ? 'group' : ''">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/20 text-white"
                            :class="variant === 'simple' ? 'bg-linear-to-br from-blue-600 to-indigo-600 rounded-xl group-hover:shadow-blue-500/40 transition' : 'bg-blue-600'">
                            <svg :class="variant === 'simple' ? 'w-5 h-5' : 'w-6 h-6'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <component :is="variant === 'simple' ? 'span' : 'h1'"
                            class="font-bold bg-clip-text text-transparent bg-linear-to-r"
                            :class="variant === 'simple' ? 'text-xl from-blue-700 to-indigo-600' : 'text-2xl from-blue-700 to-blue-500'">
                            SHT-BAAK
                        </component>
                    </component>

                    <!-- Desktop Menu (full variant) -->
                    <div v-if="variant !== 'simple'" class="hidden md:flex items-center gap-8">
                        <slot name="nav-links">
                            <Link href="/" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Home</Link>
                            <Link href="/profil" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Profil</Link>
                            <a href="/#alur" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Alur Sistem</a>
                            <a href="/#layanan" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition">Layanan</a>
                        </slot>
                        <Link href="/login" class="px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5">
                            Login Admin
                        </Link>
                    </div>

                    <!-- Desktop Menu (simple variant) -->
                    <div v-else class="hidden md:flex items-center gap-4">
                        <Link href="/" class="text-slate-600 hover:text-blue-600 font-medium transition">Beranda</Link>
                        <Link href="/profil" class="text-slate-600 hover:text-blue-600 font-medium transition">Profil</Link>
                        <Link href="/login" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">Login Admin</Link>
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="flex md:hidden">
                        <button
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                            type="button"
                            :class="variant === 'simple' ? 'p-2 rounded-lg hover:bg-slate-100' : 'text-slate-500 hover:text-blue-600 focus:outline-none focus:text-blue-600 p-2'"
                        >
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu Panel (full variant) -->
            <transition v-if="variant !== 'simple'"
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
                        <slot name="mobile-nav-links">
                            <Link href="/" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Home</Link>
                            <Link href="/profil" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Profil</Link>
                            <a href="/#alur" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Alur Sistem</a>
                            <a href="/#layanan" @click="isMobileMenuOpen = false" class="block px-3 py-3 rounded-xl text-base font-medium text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition">Layanan</a>
                        </slot>
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <Link href="/login" class="block w-full text-center px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold shadow-lg shadow-blue-500/30 active:scale-95 transition">
                                Login Admin
                            </Link>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Mobile Menu Panel (simple variant) -->
            <div v-else-if="isMobileMenuOpen" class="md:hidden border-t border-slate-100 bg-white/95 backdrop-blur-lg">
                <div class="px-4 py-3 space-y-2">
                    <Link href="/" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">Beranda</Link>
                    <Link href="/profil" class="block px-4 py-2 rounded-lg hover:bg-slate-100 font-medium">Profil</Link>
                    <Link href="/login" class="block px-4 py-2 bg-blue-600 text-white font-medium rounded-lg text-center">Login Admin</Link>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <slot />

        <!-- Footer -->
        <footer v-if="showFooter" class="bg-slate-900 text-slate-400 py-16">
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
                    <p>&copy; {{ new Date().getFullYear() }} STIKES Hang Tuah Tanjungpinang. All Rights Reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
