<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingLayout from '@/layouts/LandingLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

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
import DatePicker from 'primevue/datepicker';
import { ref } from 'vue';

const calendarDate = ref(new Date());

// Get events for a specific calendar date (handling multi-day events)
const getEventsForDate = (day: number, month: number, year: number) => {
    const targetDate = new Date(year, month, day);
    targetDate.setHours(0, 0, 0, 0);
    
    return props.kalender.filter(item => {
        const start = new Date(item.tanggal_mulai);
        start.setHours(0, 0, 0, 0);
        
        let end = start;
        if (item.tanggal_selesai) {
            end = new Date(item.tanggal_selesai);
            end.setHours(0, 0, 0, 0);
        }
        
        return targetDate >= start && targetDate <= end;
    });
};

// Dynamic legend: unique jenis + warna from all events
const legendItems = computed(() => {
    const seen = new Map<string, { label: string; warna: string }>();
    props.kalender.forEach(item => {
        if (!seen.has(item.jenis)) {
            seen.set(item.jenis, {
                label: item.jenis_label,
                warna: item.warna,
            });
        }
    });
    return Array.from(seen.values());
});
</script>

<template>
    <SeoHead 
        title="Kalender Akademik - BAAK STIKES Hang Tuah" 
        description="Jadwal kalender akademik lengkap STIKES Hang Tuah Tanjungpinang."
    />

    <LandingLayout variant="simple">
        <main class="max-w-6xl mx-auto px-4 py-8">
            <!-- Page Title -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-900">📅 Kalender Akademik</h1>
                <p class="text-sm text-slate-500">{{ tahunAkademik?.nama }}</p>
            </div>

            <div class="grid lg:grid-cols-12 gap-8 items-start">
                <!-- Main Left (Visual Calendar) -->
                <div class="lg:col-span-7 xl:col-span-8 flex flex-col gap-6">
                    <!-- Visual Calendar Widget -->
                    <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden w-full max-w-3xl mx-auto">
                        <DatePicker v-model="calendarDate" inline class="w-full border-none !text-lg">
                            <template #date="slotProps">
                                <div class="relative w-full h-full flex flex-col items-center justify-center py-2">
                                    <span class="text-base z-10">{{ slotProps.date.day }}</span>
                                    <!-- Event Dots Container -->
                                    <div class="absolute bottom-1 left-0 w-full flex justify-center gap-[2px]">
                                        <div 
                                            v-for="(event, idx) in getEventsForDate(slotProps.date.day, slotProps.date.month, slotProps.date.year).slice(0, 4)" 
                                            :key="event.id"
                                            class="w-1.5 h-1.5 rounded-full"
                                            :style="{ backgroundColor: event.warna }"
                                            :title="event.judul"
                                        ></div>
                                    </div>
                                </div>
                            </template>
                        </DatePicker>
                    </div>

                    <!-- Legend -->
                    <div class="w-full max-w-3xl mx-auto rounded-xl bg-white dark:bg-slate-800 p-6 shadow border border-gray-100 dark:border-gray-700">
                        <h3 class="font-medium text-sm mb-4">Keterangan Warna</h3>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div
                                v-for="item in legendItems"
                                :key="item.label"
                                class="flex items-center gap-2"
                            >
                                <div
                                    class="w-3 h-3 rounded-full shrink-0"
                                    :style="{ backgroundColor: item.warna }"
                                ></div>
                                <span>{{ item.label }}</span>
                            </div>
                        </div>
                        <p v-if="legendItems.length === 0" class="text-xs text-muted-foreground">Belum ada data</p>
                    </div>
                </div>

                <!-- Right Sidebar (Events) -->
                <div class="lg:col-span-5 xl:col-span-4">
                    <div class="sticky top-24 space-y-6">
                        
                        <!-- Event Mendatang -->
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

                        <!-- Main Timeline -->
                        <div class="rounded-xl bg-white dark:bg-slate-800 p-6 shadow-lg border border-gray-100 dark:border-gray-700">
                            <h2 class="font-bold text-lg mb-6 flex items-center gap-2">
                                📋 Semua Agenda
                            </h2>
                            <div v-if="Object.keys(groupedByMonth).length > 0" class="space-y-8">
                                <div v-for="(items, month) in groupedByMonth" :key="month">
                                    <h3 class="font-bold mb-3 text-blue-600 dark:text-blue-400">{{ month }}</h3>
                                    
                                    <div class="space-y-3 pl-3 border-l-2 border-blue-200 dark:border-blue-800">
                                        <div
                                            v-for="item in items"
                                            :key="item.id"
                                            class="relative pl-5"
                                        >
                                            <!-- Timeline dot -->
                                            <div
                                                class="absolute -left-[7px] top-2 w-3 h-3 rounded-full border-2 border-white dark:border-slate-900 shadow"
                                                :style="{ backgroundColor: item.warna }"
                                            ></div>

                                            <!-- Card -->
                                            <div class="rounded-lg bg-slate-50 dark:bg-slate-900 p-3 border border-gray-100 dark:border-gray-700">
                                                <div class="flex items-start gap-2">
                                                    <div class="flex-1">
                                                        <div class="flex items-center flex-wrap gap-2 mb-1">
                                                            <span
                                                                class="px-2 py-0.5 rounded text-[10px] font-medium text-white"
                                                                :style="{ backgroundColor: item.warna }"
                                                            >
                                                                {{ item.jenis_label }}
                                                            </span>
                                                            <span v-if="item.duration_days > 1" class="text-[10px] text-muted-foreground">
                                                                {{ item.duration_days }} hari
                                                            </span>
                                                        </div>
                                                        <h4 class="font-semibold text-sm">{{ item.judul }}</h4>
                                                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                                                            {{ item.tanggal_format }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-center py-8">
                                <p class="text-sm text-muted-foreground">Belum ada jadwal kalender akademik</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </LandingLayout>
</template>
