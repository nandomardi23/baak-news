<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();

import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DatePicker from 'primevue/datepicker';
import Swal from 'sweetalert2';

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
    tahun_akademik: string | null;
    duration_days: number;
}

interface JenisOption {
    value: string;
    label: string;
    color: string;
}

interface TahunAkademikOption {
    id: number;
    nama: string;
}

const props = defineProps<{
    kalender: KalenderItem[];
    filters: {
        tahun_akademik_id: number | null;
        jenis: string | null;
    };
    tahunAkademikOptions: TahunAkademikOption[];
    jenisOptions: JenisOption[];
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kalender Akademik', href: '/admin/kalender' },
]);

// Calendar widget
const calendarDate = ref(new Date());

// Get events for a specific calendar date
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

// Dynamic legend
const legendItems = computed(() => {
    const seen = new Map<string, { label: string; warna: string }>();
    props.kalender.forEach(item => {
        if (!seen.has(item.jenis)) {
            seen.set(item.jenis, { label: item.jenis_label, warna: item.warna });
        }
    });
    return Array.from(seen.values());
});

// Modal state
const showModal = ref(false);
const editingItem = ref<KalenderItem | null>(null);

const form = useForm({
    judul: '',
    deskripsi: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
    jenis: 'lainnya',
    tahun_akademik_id: props.filters.tahun_akademik_id || (props.tahunAkademikOptions[0]?.id ?? ''),
    warna: '',
});

const resetForm = () => {
    form.clearErrors();
    form.defaults({
        judul: '',
        deskripsi: '',
        tanggal_mulai: '',
        tanggal_selesai: '',
        jenis: 'lainnya',
        tahun_akademik_id: props.filters.tahun_akademik_id || (props.tahunAkademikOptions[0]?.id ?? ''),
        warna: '',
    });
    form.reset();
    form.judul = '';
    form.deskripsi = '';
    form.tanggal_mulai = '';
    form.tanggal_selesai = '';
    form.jenis = 'lainnya';
    form.tahun_akademik_id = props.filters.tahun_akademik_id || (props.tahunAkademikOptions[0]?.id ?? '');
    form.warna = '';
};

function openModal(item?: KalenderItem) {
    if (item) {
        editingItem.value = item;
        form.clearErrors();
        form.judul = item.judul;
        form.deskripsi = item.deskripsi || '';
        form.tanggal_mulai = item.tanggal_mulai;
        form.tanggal_selesai = item.tanggal_selesai || '';
        form.jenis = item.jenis;
        form.warna = item.warna;
    } else {
        editingItem.value = null;
        resetForm();
    }
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingItem.value = null;
    form.reset();
}

function submitForm() {
    if (editingItem.value) {
        form.put(`/admin/kalender/${editingItem.value.id}`, {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post('/admin/kalender', {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
}

import { useConfirmDelete } from '@/composables/useConfirmDelete';
const { confirmDelete } = useConfirmDelete();

function deleteItem(item: KalenderItem) {
    confirmDelete({
        url: `/admin/kalender/${item.id}`,
        entityName: 'Event Kalender',
        text: `Hapus event "${item.judul}"? Tindakan ini tidak dapat dibatalkan.`,
    });
}

function filterByTahun(tahunId: number | string) {
    router.get('/admin/kalender', { tahun_akademik_id: tahunId }, { preserveState: true });
}

function filterByJenis(jenis: string | null) {
    router.get('/admin/kalender', { 
        tahun_akademik_id: props.filters.tahun_akademik_id,
        jenis: jenis 
    }, { preserveState: true });
}

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

const selectedJenisColor = computed(() => {
    const option = props.jenisOptions.find(o => o.value === form.jenis);
    return option?.color || '#6B7280';
});

const activeColor = computed(() => form.warna || selectedJenisColor.value);

// Preset color swatches for quick selection
const colorSwatches = [
    '#10B981', '#3B82F6', '#EF4444', '#F59E0B', '#8B5CF6',
    '#EC4899', '#14B8A6', '#F97316', '#06B6D4', '#6366F1',
    '#84CC16', '#D946EF',
];

function selectJenis(value: string) {
    form.jenis = value;
    // Auto-set color from jenis option
    const opt = props.jenisOptions.find(o => o.value === value);
    if (opt) {
        form.warna = opt.color;
    }
}

const customJenisInput = ref(false);

// Stats
const totalEvents = computed(() => props.kalender.length);
const totalMonths = computed(() => Object.keys(groupedByMonth.value).length);
</script>

<template>
    <Head title="Kalender Akademik" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Header with Gradient -->
        <div class="relative rounded-2xl bg-gradient-to-br from-indigo-600 via-blue-600 to-cyan-500 p-6 text-white overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
            <div class="relative flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold flex items-center gap-2">
                        📅 Kalender Akademik
                    </h1>
                    <p class="text-blue-100 mt-1">Kelola jadwal kegiatan akademik per semester</p>
                </div>
                <button
                    @click="openModal()"
                    class="px-5 py-2.5 bg-white text-blue-600 rounded-xl hover:bg-blue-50 transition-all font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                >
                    + Tambah Event
                </button>
            </div>

            <!-- Stats Row -->
            <div class="relative flex flex-wrap gap-6 mt-5 pt-5 border-t border-white/20">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm">📊</div>
                    <div>
                        <p class="text-xs text-blue-200">Total Event</p>
                        <p class="font-bold text-lg leading-tight">{{ totalEvents }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm">📆</div>
                    <div>
                        <p class="text-xs text-blue-200">Bulan Aktif</p>
                        <p class="font-bold text-lg leading-tight">{{ totalMonths }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-sm">🏷️</div>
                    <div>
                        <p class="text-xs text-blue-200">Jenis Kegiatan</p>
                        <p class="font-bold text-lg leading-tight">{{ legendItems.length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3">
            <select
                :value="filters.tahun_akademik_id"
                @change="filterByTahun(($event.target as HTMLSelectElement).value)"
                class="px-4 py-2 rounded-xl border bg-card text-sm font-medium shadow-sm focus:ring-2 focus:ring-blue-500 outline-none"
            >
                <option v-for="ta in tahunAkademikOptions" :key="ta.id" :value="ta.id">
                    {{ ta.nama }}
                </option>
            </select>

            <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 hidden sm:block"></div>

            <div class="flex flex-wrap gap-2">
                <button
                    @click="filterByJenis(null)"
                    :class="[
                        'px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all',
                        !filters.jenis 
                            ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md' 
                            : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700'
                    ]"
                >
                    Semua
                </button>
                <button
                    v-for="jenis in jenisOptions"
                    :key="jenis.value"
                    @click="filterByJenis(jenis.value)"
                    :class="[
                        'px-3.5 py-1.5 rounded-lg text-sm font-medium transition-all',
                        filters.jenis === jenis.value 
                            ? 'text-white shadow-md' 
                            : 'bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700'
                    ]"
                    :style="filters.jenis === jenis.value ? { backgroundColor: jenis.color } : {}"
                >
                    {{ jenis.label }}
                </button>
            </div>
        </div>

        <!-- Main Layout: Calendar + Events -->
        <div class="grid lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Calendar Widget -->
            <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                <div class="rounded-2xl border bg-card p-6 shadow-sm overflow-hidden">
                    <DatePicker v-model="calendarDate" inline class="w-full border-none !text-lg">
                        <template #date="slotProps">
                            <div class="relative w-full h-full flex flex-col items-center justify-center py-2">
                                <span class="text-base z-10">{{ slotProps.date.day }}</span>
                                <div class="absolute bottom-1 left-0 w-full flex justify-center gap-[2px]">
                                    <div 
                                        v-for="event in getEventsForDate(slotProps.date.day, slotProps.date.month, slotProps.date.year).slice(0, 4)" 
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
                <div class="rounded-2xl border bg-card p-5 shadow-sm">
                    <h3 class="font-semibold text-sm mb-3 text-muted-foreground uppercase tracking-wide">Keterangan Warna</h3>
                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="item in legendItems"
                            :key="item.label"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-800/50"
                        >
                            <div
                                class="w-3 h-3 rounded-full shrink-0"
                                :style="{ backgroundColor: item.warna }"
                            ></div>
                            <span class="text-sm">{{ item.label }}</span>
                        </div>
                        <p v-if="legendItems.length === 0" class="text-sm text-muted-foreground">Belum ada data</p>
                    </div>
                </div>
            </div>

            <!-- Right: Event List -->
            <div class="lg:col-span-5 xl:col-span-4">
                <div class="sticky top-24 space-y-6">
                    
                    <!-- Event List -->
                    <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                        <div class="p-5 border-b bg-gradient-to-r from-gray-50 to-transparent dark:from-gray-800/50">
                            <h2 class="font-bold text-base flex items-center gap-2">
                                📋 Daftar Event
                                <span class="ml-auto text-xs font-normal text-muted-foreground">{{ totalEvents }} event</span>
                            </h2>
                        </div>
                        
                        <div v-if="Object.keys(groupedByMonth).length > 0" class="max-h-[65vh] overflow-y-auto">
                            <div v-for="(items, month) in groupedByMonth" :key="month" class="border-b last:border-b-0">
                                <div class="sticky top-0 z-10 px-5 py-2.5 bg-blue-50 dark:bg-blue-950/30 border-b border-blue-100 dark:border-blue-900">
                                    <h3 class="text-sm font-bold text-blue-700 dark:text-blue-400">{{ month }}</h3>
                                </div>
                                
                                <div class="divide-y">
                                    <div
                                        v-for="item in items"
                                        :key="item.id"
                                        class="group px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                                    >
                                        <div class="flex items-start gap-3">
                                            <!-- Color indicator -->
                                            <div
                                                class="w-1 self-stretch rounded-full shrink-0 mt-0.5"
                                                :style="{ backgroundColor: item.warna }"
                                            ></div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span
                                                        class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold text-white leading-tight"
                                                        :style="{ backgroundColor: item.warna }"
                                                    >
                                                        {{ item.jenis_label }}
                                                    </span>
                                                    <span v-if="item.duration_days > 1" class="text-[10px] text-muted-foreground">
                                                        {{ item.duration_days }} hari
                                                    </span>
                                                </div>
                                                <h4 class="font-semibold text-sm leading-snug">{{ item.judul }}</h4>
                                                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-0.5">
                                                    {{ item.tanggal_format }}
                                                </p>
                                                <p v-if="item.deskripsi" class="text-xs text-muted-foreground mt-1 line-clamp-2">
                                                    {{ item.deskripsi }}
                                                </p>
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button
                                                    @click="openModal(item)"
                                                    class="p-1.5 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg transition-colors text-blue-600"
                                                    title="Edit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <button
                                                    @click="deleteItem(item)"
                                                    class="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors text-red-500"
                                                    title="Hapus"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center text-2xl">📅</div>
                            <p class="text-muted-foreground text-sm mb-4">Belum ada event kalender untuk semester ini</p>
                            <button
                                @click="openModal()"
                                class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                + Tambah Event Pertama
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-all duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>

                <!-- Modal Content -->
                <Transition
                    enter-active-class="transition-all duration-300 delay-100"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="showModal" class="relative bg-background rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-auto">
                        <!-- Modal Header with Gradient -->
                        <div class="sticky top-0 z-10 rounded-t-2xl overflow-hidden">
                            <div class="relative px-6 py-5" :style="{ background: `linear-gradient(135deg, ${activeColor}, ${activeColor}dd)` }">
                                <div class="absolute inset-0 bg-black/10"></div>
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4"></div>
                                <div class="relative flex items-center justify-between">
                                    <div>
                                        <h2 class="text-lg font-bold text-white">
                                            {{ editingItem ? 'Edit Event' : 'Tambah Event Baru' }}
                                        </h2>
                                        <p class="text-white/70 text-sm mt-0.5">Isi detail kegiatan akademik</p>
                                    </div>
                                    <button @click="closeModal" class="p-2 hover:bg-white/20 rounded-xl transition-colors text-white">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <form @submit.prevent="submitForm" class="space-y-6">
                                
                                <!-- Live Preview Card -->
                                <div v-if="form.judul" class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/30">
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-widest font-semibold mb-2">Preview</p>
                                    <div class="flex items-start gap-3">
                                        <div class="w-1.5 self-stretch rounded-full shrink-0" :style="{ backgroundColor: activeColor }"></div>
                                        <div>
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold text-white mb-1" :style="{ backgroundColor: activeColor }">
                                                {{ form.jenis || 'jenis' }}
                                            </span>
                                            <h4 class="font-semibold text-sm">{{ form.judul }}</h4>
                                            <p v-if="form.tanggal_mulai" class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-0.5">
                                                {{ form.tanggal_mulai }}{{ form.tanggal_selesai ? ' — ' + form.tanggal_selesai : '' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Judul -->
                                <div>
                                    <label class="flex items-center gap-1.5 text-sm font-medium mb-2">
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        Judul Event
                                    </label>
                                    <input
                                        v-model="form.judul"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border bg-card focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-base"
                                        placeholder="Contoh: UTS Semester Ganjil 2024/2025"
                                    />
                                    <p v-if="form.errors.judul" class="text-red-500 text-sm mt-1">{{ form.errors.judul }}</p>
                                </div>

                                <!-- Tanggal -->
                                <div>
                                    <label class="flex items-center gap-1.5 text-sm font-medium mb-2">
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Periode Kegiatan
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <span class="text-xs text-muted-foreground mb-1 block">Mulai *</span>
                                            <DatePicker
                                                :model-value="form.tanggal_mulai ? new Date(form.tanggal_mulai) : null"
                                                @update:model-value="(val) => {
                                                    if (val && val instanceof Date) {
                                                        const year = val.getFullYear();
                                                        const month = String(val.getMonth() + 1).padStart(2, '0');
                                                        const day = String(val.getDate()).padStart(2, '0');
                                                        form.tanggal_mulai = `${year}-${month}-${day}`;
                                                    } else {
                                                        form.tanggal_mulai = '';
                                                    }
                                                }"
                                                dateFormat="yy-mm-dd"
                                                showIcon
                                                required
                                                class="w-full"
                                                inputClass="w-full px-4 py-2.5 flex rounded-xl border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                            />
                                        </div>
                                        <div>
                                            <span class="text-xs text-muted-foreground mb-1 block">Selesai</span>
                                            <DatePicker
                                                :model-value="form.tanggal_selesai ? new Date(form.tanggal_selesai) : null"
                                                @update:model-value="(val) => {
                                                    if (val && val instanceof Date) {
                                                        const year = val.getFullYear();
                                                        const month = String(val.getMonth() + 1).padStart(2, '0');
                                                        const day = String(val.getDate()).padStart(2, '0');
                                                        form.tanggal_selesai = `${year}-${month}-${day}`;
                                                    } else {
                                                        form.tanggal_selesai = '';
                                                    }
                                                }"
                                                dateFormat="yy-mm-dd"
                                                showIcon
                                                class="w-full"
                                                inputClass="w-full px-4 py-2.5 flex rounded-xl border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Jenis Kegiatan as Chips -->
                                <div>
                                    <label class="flex items-center gap-1.5 text-sm font-medium mb-2">
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        Jenis Kegiatan
                                    </label>
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <button
                                            v-for="opt in jenisOptions"
                                            :key="opt.value"
                                            type="button"
                                            @click="selectJenis(opt.value)"
                                            :class="[
                                                'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2',
                                                form.jenis === opt.value
                                                    ? 'text-white border-transparent shadow-md scale-105'
                                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                                            ]"
                                            :style="form.jenis === opt.value ? { backgroundColor: opt.color } : {}"
                                        >
                                            {{ opt.label }}
                                        </button>
                                        <button
                                            type="button"
                                            @click="customJenisInput = !customJenisInput; if(customJenisInput) form.jenis = ''"
                                            :class="[
                                                'px-3 py-1.5 rounded-lg text-sm font-medium transition-all border-2 border-dashed',
                                                customJenisInput
                                                    ? 'border-blue-400 text-blue-600 bg-blue-50 dark:bg-blue-950/30'
                                                    : 'border-gray-300 dark:border-gray-600 text-muted-foreground hover:border-gray-400'
                                            ]"
                                        >
                                            ✨ Custom
                                        </button>
                                    </div>
                                    <input
                                        v-if="customJenisInput"
                                        v-model="form.jenis"
                                        type="text"
                                        class="w-full px-4 py-2.5 rounded-xl border bg-card focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                                        placeholder="Ketik nama jenis kegiatan baru..."
                                    />
                                </div>

                                <!-- Warna with Swatches -->
                                <div>
                                    <label class="flex items-center gap-1.5 text-sm font-medium mb-2">
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                        Warna Event
                                    </label>
                                    <div class="flex flex-wrap gap-2 mb-3">
                                        <button
                                            v-for="color in colorSwatches"
                                            :key="color"
                                            type="button"
                                            @click="form.warna = color"
                                            class="w-8 h-8 rounded-lg transition-all hover:scale-110 border-2"
                                            :class="activeColor === color ? 'border-gray-900 dark:border-white scale-110 shadow-lg' : 'border-transparent'"
                                            :style="{ backgroundColor: color }"
                                        ></button>
                                        <label class="w-8 h-8 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 cursor-pointer hover:border-gray-400 transition-colors flex items-center justify-center overflow-hidden relative">
                                            <span class="text-xs">🎨</span>
                                            <input
                                                type="color"
                                                class="absolute inset-0 opacity-0 cursor-pointer"
                                                :value="activeColor"
                                                @input="form.warna = ($event.target as HTMLInputElement).value"
                                            />
                                        </label>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                                        <div class="w-4 h-4 rounded" :style="{ backgroundColor: activeColor }"></div>
                                        <span>{{ form.warna || 'Default dari jenis' }} — {{ activeColor }}</span>
                                    </div>
                                </div>

                                <!-- Tahun Akademik -->
                                <div>
                                    <label class="flex items-center gap-1.5 text-sm font-medium mb-2">
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        Tahun Akademik
                                    </label>
                                    <select
                                        v-model="form.tahun_akademik_id"
                                        class="w-full px-4 py-3 rounded-xl border bg-card focus:ring-2 focus:ring-blue-500 outline-none"
                                    >
                                        <option v-for="ta in tahunAkademikOptions" :key="ta.id" :value="ta.id">
                                            {{ ta.nama }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Deskripsi -->
                                <div>
                                    <label class="flex items-center gap-1.5 text-sm font-medium mb-2">
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                        Deskripsi
                                        <span class="text-xs text-muted-foreground font-normal">(opsional)</span>
                                    </label>
                                    <textarea
                                        v-model="form.deskripsi"
                                        rows="3"
                                        class="w-full px-4 py-3 rounded-xl border bg-card focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                                        placeholder="Keterangan tambahan tentang kegiatan ini..."
                                    ></textarea>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-end gap-3 pt-5 border-t">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-5 py-2.5 rounded-xl border hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors font-medium"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="px-6 py-2.5 text-white rounded-xl transition-all disabled:opacity-50 font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                                        :style="{ backgroundColor: activeColor }"
                                    >
                                        <span v-if="form.processing" class="flex items-center gap-2">
                                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            Menyimpan...
                                        </span>
                                        <span v-else>{{ editingItem ? '💾 Simpan Perubahan' : '🚀 Tambah Event' }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>

</template>
