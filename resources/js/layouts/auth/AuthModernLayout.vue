<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-slate-900">
        <!-- Animated Background -->
        <div class="absolute inset-0 z-0">
            <!-- Base Gradient: dark slate to subtle teal -->
            <div class="absolute inset-0 bg-linear-to-br from-slate-800 via-slate-900 to-emerald-950"></div>
            
            <!-- Subtle Abstract Shapes -->
            <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] bg-emerald-600/10 rounded-full mix-blend-screen blur-[100px] animate-pulse"></div>
            <div class="absolute top-[30%] -right-[10%] w-[60vw] h-[60vw] bg-teal-500/8 rounded-full mix-blend-screen blur-[120px] animate-pulse" style="animation-delay: 2s"></div>
            <div class="absolute -bottom-[20%] left-[20%] w-[50vw] h-[50vw] bg-cyan-400/5 rounded-full mix-blend-screen blur-[80px] animate-pulse" style="animation-delay: 4s"></div>
            
            <!-- Grid Pattern Overlay -->
            <div class="absolute inset-0 bg-[url('/images/grid.svg')] opacity-5"></div>
        </div>

        <!-- Main Content Container -->
        <div class="relative z-10 w-full max-w-md px-4">
            <!-- Branding Header -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-3 bg-white/8 backdrop-blur-md px-5 py-2.5 rounded-full border border-white/10 shadow-lg shadow-black/20 hover:bg-white/12 transition group">
                    <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform overflow-hidden">
                        <AppLogoIcon class="w-full h-full object-contain" />
                    </div>
                    <span class="text-white font-bold tracking-wide text-lg">BAAK STIKES</span>
                </Link>
            </div>

            <!-- Login Card -->
            <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl shadow-black/30 border border-white/20 p-6 sm:p-8">
                <div class="mb-6 mt-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ title }}</h1>
                    <p class="text-slate-500 mt-1 text-sm">{{ description }}</p>
                </div>

                <slot />
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-slate-400 text-xs font-medium opacity-70">
                    &copy; {{ new Date().getFullYear() }} Biro Administrasi Akademik &amp; Kemahasiswaan
                </p>
            </div>
        </div>
        <Toaster position="top-right" />
    </div>
</template>
