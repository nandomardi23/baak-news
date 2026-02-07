<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-blue-600">
        <!-- Animated Background Waves/Gradients -->
        <div class="absolute inset-0 z-0">
            <!-- Base Gradient -->
            <div class="absolute inset-0 bg-linear-to-br from-blue-500 via-blue-600 to-indigo-800"></div>
            
            <!-- Abstract Shapes (Simulating the waves from reference) -->
            <div class="absolute -top-[20%] -left-[10%] w-[70vw] h-[70vw] bg-blue-400/30 rounded-full mix-blend-overlay blur-[80px] animate-pulse"></div>
            <div class="absolute top-[30%] -right-[10%] w-[60vw] h-[60vw] bg-indigo-500/30 rounded-full mix-blend-overlay blur-[100px] animate-pulse" style="animation-delay: 2s"></div>
            <div class="absolute -bottom-[20%] left-[20%] w-[50vw] h-[50vw] bg-sky-300/20 rounded-full mix-blend-overlay blur-[60px] animate-pulse" style="animation-delay: 4s"></div>
            
            <!-- Grid Pattern Overlay -->
            <div class="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10"></div>
        </div>

        <!-- Main Content Container -->
        <div class="relative z-10 w-full max-w-md px-4">
            <!-- Branding Header -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-md px-5 py-2 rounded-full border border-white/20 shadow-lg hover:bg-white/20 transition group">
                    <div class="w-8 h-8 rounded-lg bg-white/90 flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-110 transition-transform">
                        <AppLogoIcon class="w-5 h-5 fill-current" />
                    </div>
                    <span class="text-white font-bold tracking-wide text-lg">BAAK STIKES</span>
                </Link>
            </div>

            <!-- Login Card -->
            <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl shadow-blue-900/20 border border-white/50 p-6 sm:p-8">
                <div class="mb-6 mt-2">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ title }}</h1>
                    <p class="text-slate-500 mt-1 text-sm">{{ description }}</p>
                </div>

                <slot />
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-blue-100 text-xs font-medium opacity-80">
                    &copy; {{ new Date().getFullYear() }} Biro Administrasi Akademik & Kemahasiswaan
                </p>
            </div>
        </div>
    </div>
</template>
