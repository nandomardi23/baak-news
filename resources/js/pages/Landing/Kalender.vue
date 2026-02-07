<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface KalenderItem {
    id: number;
    judul: string;
    deskripsi: string | null;
    tanggal_mulai: string;
    tanggal_selesai: string | null;
    tanggal_format: string;
    jenis: string;
    jenis_label: string;
    warna: string;
    duration_days: number;
}

interface TahunAkademik {
    id: number;
    nama: string;
}

const props = defineProps<{
    kalender: KalenderItem[];
    tahunAkademik: TahunAkademik;
    upcomingEvents: KalenderItem[];
}>();

// Group by month
const groupedByMonth = computed(() => {
    const groups: Record<string, KalenderItem[]> = {};
    
    props.kalender.forEach(item => {
        const date = new Date(item.tanggal_mulai);
        const monthKey = date.toLocaleDateString('id-ID', { year: 'numeric', month: 'long' });
        
        if (!groups[monthKey]) {
            groups[monthKey] = [];
        }
        groups[monthKey].push(item);
    });
    
    return groups;
});
</script>

<template>
    <Head title="Kalender Akademik" />

    <div class="min-h-screen bg-linear-to-br from-slate-50 to-blue-50 dark:from-slate-900 dark:to-slate-800">
        <!-- Header -->
        <header class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b sticky top-0 z-40">
            <div class="max-w-6xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <a href="/" class="text-2xl font-bold text-blue-600">📅</a>
                        <div>
                            <h1 class="font-bold text-lg">Kalender Akademik</h1>
                            <p class="text-sm text-muted-foreground">{{ tahunAkademik?.nama }}</p>
                        </div>
                    </div>
                    <a
                        href="/"
                        class="px-4 py-2 rounded-lg border hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-sm"
                    >
                        ← Kembali
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-4 py-8">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Main Timeline -->
                <div class="lg:col-span-2">
                    <div v-if="Object.keys(groupedByMonth).length > 0" class="space-y-8">
                        <div v-for="(items, month) in groupedByMonth" :key="month">
                            <h2 class="text-xl font-bold mb-4 text-blue-600 dark:text-blue-400">{{ month }}</h2>
                            
                            <div class="space-y-3 pl-4 border-l-2 border-blue-200 dark:border-blue-800">
                                <div
                                    v-for="item in items"
                                    :key="item.id"
                                    class="relative pl-6"
                                >
                                    <!-- Timeline dot -->
                                    <div
                                        class="absolute -left-[9px] top-3 w-4 h-4 rounded-full border-2 border-white dark:border-slate-900 shadow"
                                        :style="{ backgroundColor: item.warna }"
                                    ></div>

                                    <!-- Card -->
                                    <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-md hover:shadow-lg transition-shadow border border-gray-100 dark:border-gray-700">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-2 h-full rounded-full shrink-0"
                                                :style="{ backgroundColor: item.warna }"
                                            ></div>
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span
                                                        class="px-2 py-0.5 rounded-full text-xs font-medium text-white"
                                                        :style="{ backgroundColor: item.warna }"
                                                    >
                                                        {{ item.jenis_label }}
                                                    </span>
                                                    <span v-if="item.duration_days > 1" class="text-xs text-muted-foreground">
                                                        {{ item.duration_days }} hari
                                                    </span>
                                                </div>
                                                <h3 class="font-semibold text-lg">{{ item.judul }}</h3>
                                                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                                                    {{ item.tanggal_format }}
                                                </p>
                                                <p v-if="item.deskripsi" class="text-sm mt-2 text-muted-foreground">
                                                    {{ item.deskripsi }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="rounded-xl bg-white dark:bg-slate-800 p-12 text-center shadow">
                        <p class="text-muted-foreground">Belum ada jadwal kalender akademik</p>
                    </div>
                </div>

                <!-- Sidebar - Upcoming Events -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                            <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                                🔔 Event Mendatang
                            </h2>

                            <div v-if="upcomingEvents.length > 0" class="space-y-3">
                                <div
                                    v-for="event in upcomingEvents"
                                    :key="event.id"
                                    class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800"
                                >
                                    <div class="flex items-center gap-2 mb-1">
                                        <div
                                            class="w-2 h-2 rounded-full"
                                            :style="{ backgroundColor: event.warna }"
                                        ></div>
                                        <span class="text-xs text-muted-foreground">{{ event.jenis_label }}</span>
                                    </div>
                                    <p class="font-medium text-sm">{{ event.judul }}</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400">{{ event.tanggal_format }}</p>
                                </div>
                            </div>

                            <p v-else class="text-sm text-muted-foreground text-center py-4">
                                Tidak ada event mendatang
                            </p>
                        </div>

                        <!-- Legend -->
                        <div class="mt-4 rounded-xl bg-white dark:bg-slate-800 p-4 shadow border border-gray-100 dark:border-gray-700">
                            <h3 class="font-medium text-sm mb-3">Keterangan Warna</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-[#10B981]"></div>
                                    <span>Pendaftaran</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-[#3B82F6]"></div>
                                    <span>Perkuliahan</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-[#EF4444]"></div>
                                    <span>Ujian</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-[#F59E0B]"></div>
                                    <span>Libur</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-[#6B7280]"></div>
                                    <span>Lainnya</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t bg-white/50 dark:bg-slate-900/50 backdrop-blur mt-12">
            <div class="max-w-6xl mx-auto px-4 py-6 text-center text-sm text-muted-foreground">
                © {{ new Date().getFullYear() }} BAAK - Sistem Informasi Akademik
            </div>
        </footer>
    </div>
</template>
