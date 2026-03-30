<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { Toaster } from 'vue-sonner';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex text-slate-900 bg-white">
        <!-- Left Pane -->
        <div class="w-full lg:w-1/2 flex flex-col relative px-6 py-12 sm:px-12 md:px-20 overflow-y-auto">
            <!-- Back to Home -->
            <div class="absolute top-6 left-6 sm:top-8 sm:left-12">
                <Link href="/" class="group flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 transition-colors">
                    <ChevronLeft class="w-4 h-4 transition-transform group-hover:-translate-x-1" />
                    Back to home
                </Link>
            </div>

            <!-- Content Area -->
            <div class="flex-1 flex flex-col justify-center w-full max-w-[400px] mx-auto mt-12 lg:mt-0">
                <div class="mb-8 mt-2">
                    <h1 class="text-4xl font-bold tracking-tight text-slate-900 mb-2">{{ title }}</h1>
                    <p class="text-slate-500 text-sm">{{ description }}</p>
                </div>

                <slot />
            </div>
        </div>

        <!-- Right Pane -->
        <div class="hidden lg:flex w-1/2 bg-blue-600 relative overflow-hidden items-center justify-center">
            <!-- Grid Background Overlay -->
            <div class="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10 pointer-events-none mix-blend-overlay border-l border-white/5"></div>
            
            <!-- Additional decoration background based on image -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <!-- grid blocks illustration -->
                <div class="absolute top-0 right-0 w-[400px] h-[400px]">
                    <div class="grid grid-cols-4 grid-rows-4 w-full h-full opacity-10">
                        <div class="border border-white/20"></div><div class="border border-white/20"></div><div class="border border-white/20 bg-white/5"></div><div class="border border-white/20"></div>
                        <div class="border border-white/20"></div><div class="border border-white/20 bg-white/10"></div><div class="border border-white/20"></div><div class="border border-white/20 bg-white/5"></div>
                        <div class="border border-white/20 bg-white/5"></div><div class="border border-white/20"></div><div class="border border-white/20 bg-white/10"></div><div class="border border-white/20"></div>
                        <div class="border border-white/20"></div><div class="border border-white/20 bg-white/5"></div><div class="border border-white/20"></div><div class="border border-white/20"></div>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-[300px] h-[300px]">
                    <div class="grid grid-cols-3 grid-rows-3 w-full h-full opacity-10">
                        <div class="border border-white/20 bg-white/5"></div><div class="border border-white/20"></div><div class="border border-white/20 bg-white/10"></div>
                        <div class="border border-white/20"></div><div class="border border-white/20 bg-white/10"></div><div class="border border-white/20"></div>
                        <div class="border border-white/20 bg-white/10"></div><div class="border border-white/20"></div><div class="border border-white/20 bg-white/5"></div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 flex flex-col items-center max-w-lg px-8 text-center text-white">
                <!-- Branding Header -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center shadow-lg border-2 border-white/30 p-2 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                        <AppLogoIcon class="w-full h-full object-contain block drop-shadow-md" />
                    </div>
                    <h2 class="text-4xl font-extrabold tracking-tight">
                        BAAK STIKES
                    </h2>
                </div>
                
                <p class="text-blue-100 text-lg leading-relaxed mt-2">
                    Biro Administrasi Akademik &amp; Kemahasiswaan
                </p>
            </div>
            
            <!-- Footer -->
            <div class="absolute bottom-8 text-center w-full z-10">
                <p class="text-blue-200 text-xs font-medium opacity-80">
                    &copy; {{ new Date().getFullYear() }} BAAK STIKES
                </p>
            </div>
        </div>

        <Toaster position="top-right" />
    </div>
</template>
