<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { useNeoFeederSync } from '@/composables/useNeoFeederSync';

interface Settings {
    url: string;
    username: string;
    password: string;
    has_password: boolean;
}

const props = defineProps<{
    settings: Settings;
    semesters: Array<{ id_semester: string; nama_semester: string }>;
}>();

const { syncStates, accumulatedStats, syncData, cancelAllSyncs } = useNeoFeederSync();
const selectedSemester = ref<string>('');

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Sinkronisasi Neo Feeder', href: '#' },
];

const form = useForm({
    url: props.settings.url,
    username: props.settings.username,
    password: '',
});

const testResult = ref<{ success: boolean; message: string } | null>(null);
const isTesting = ref(false);

// Sync All State
const isSyncingAll = ref(false);
const currentSyncIndex = ref(-1);
const syncAllProgress = ref(0);
const syncAllErrors = ref<string[]>([]);

const syncOrder = [
    // Langkah 1: Data Master (Pondasi)
    'referensi', 'wilayah', 'prodi', 'semester', 'dosen', 'mahasiswa',
    // Langkah 2: Struktur Pendidikan
    'kurikulum', 'matakuliah', 'biodata',
    // Langkah 3: Perkuliahan (Kritikal)
    'kelaskuliah', 'dosenpengajar', 'krs',
    // Langkah 4: Hasil & Aktivitas Tambahan
    'nilai', 'aktivitas', 'ajardosen', 'bimbingan', 'uji', 'aktivitasmahasiswa', 'anggotaaktivitas', 'konversi'
];

const submit = () => {
    form.post('/admin/settings/neofeeder');
};

const getXsrfToken = (): string => {
    const token = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];
    return token ? decodeURIComponent(token) : '';
};

const testConnection = async () => {
    isTesting.value = true;
    testResult.value = null;

    try {
        const response = await fetch('/admin/settings/neofeeder/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        testResult.value = await response.json();
    } catch (error) {
        testResult.value = {
            success: false,
            message: 'Gagal terhubung ke server: ' + (error instanceof Error ? error.message : 'Unknown error'),
        };
    } finally {
        isTesting.value = false;
    }
};

const syncSince = ref<string>('');

// Error Modal
const errorModalOpen = ref(false);
const selectedErrors = ref<string[]>([]);
const errorModalTitle = ref('');

const openErrorModal = (title: string, errors: string[]) => {
    errorModalTitle.value = title;
    selectedErrors.value = errors;
    errorModalOpen.value = true;
};

const closeErrorModal = () => {
    errorModalOpen.value = false;
    selectedErrors.value = [];
};

// Sync All - Sequential
const syncAll = async () => {
    if (isSyncingAll.value) return;
    
    isSyncingAll.value = true;
    currentSyncIndex.value = 0;
    syncAllProgress.value = 0;
    syncAllErrors.value = [];
    
    // Reset all results
    for (const type of syncOrder) {
        if(syncStates[type]) syncStates[type].result = null;
    }
    
    for (let i = 0; i < syncOrder.length; i++) {
        if (!isSyncingAll.value) break;
        currentSyncIndex.value = i;
        const type = syncOrder[i];
        
        // This now waits for the full recursive sync
        // Pass syncSince value
        await syncData(type, 0, selectedSemester.value, syncSince.value);
        
        if (!isSyncingAll.value) break;
        
        // Check for errors in the accumulator
        const acc = accumulatedStats[type];
        if (acc && (acc.total_failed > 0 || acc.errors.length > 0)) {
            const syncType = syncTypes.find(s => s.type === type);
            if (acc.total_synced === 0 && acc.total_failed > 0) {
                syncAllErrors.value.push(`${syncType?.label || type}: Gagal total (${acc.total_failed} errors)`);
            }
        }
        
        syncAllProgress.value = Math.round(((i + 1) / syncOrder.length) * 100);
        
        // Small delay between syncs
        if (i < syncOrder.length - 1) {
            await new Promise(resolve => setTimeout(resolve, 300));
        }
    }
    
    currentSyncIndex.value = -1;
    isSyncingAll.value = false;
};

const stopSyncAll = () => {
    isSyncingAll.value = false;
    currentSyncIndex.value = -1;
    cancelAllSyncs();
};

const syncTypes = [
     {
        type: 'referensi',
        label: 'Referensi Umum',
        description: 'Agama, Alat Transportasi, Pekerjaan, dll.',
        icon: '📚',
        color: 'from-gray-500 to-slate-500',
    },
    {
        type: 'wilayah',
        label: 'Data Wilayah',
        description: 'Negara, Propinsi, Kabupaten, Kecamatan',
        icon: '🌍',
        color: 'from-blue-400 to-indigo-400',
    },
    {
        type: 'prodi',
        label: 'Program Studi',
        description: 'Data program studi dari Neo Feeder',
        icon: '🎓',
        color: 'from-violet-500 to-purple-500',
    },
     {
        type: 'kurikulum',
        label: 'Kurikulum',
        description: 'Data Kurikulum & Matkul Kurikulum',
        icon: '📖',
        color: 'from-emerald-400 to-teal-400',
    },
    {
        type: 'semester',
        label: 'Semester',
        description: 'Data tahun akademik/semester',
        icon: '📅',
        color: 'from-blue-500 to-cyan-500',
    },
    {
        type: 'matakuliah',
        label: 'Mata Kuliah',
        description: 'Data mata kuliah per prodi',
        icon: '📚',
        color: 'from-emerald-500 to-teal-500',
    },
    {
        type: 'dosen',
        label: 'Dosen',
        description: 'Data dosen dari Neo Feeder',
        icon: '👨‍🏫',
        color: 'from-indigo-500 to-blue-500',
    },
    {
        type: 'mahasiswa',
        label: 'Mahasiswa',
        description: 'Data mahasiswa (sync prodi & semester dulu)',
        icon: '👨‍🎓',
        color: 'from-amber-500 to-orange-500',
    },
    {
        type: 'biodata',
        label: 'Biodata',
        description: 'Data orang tua, NIK, alamat lengkap',
        icon: '📋',
        color: 'from-pink-500 to-rose-500',
    },
    {
        type: 'kelaskuliah',
        label: 'Kelas Kuliah',
        description: 'Data kelas kuliah per semester',
        icon: '🏫',
        color: 'from-teal-500 to-cyan-500',
    },
    {
        type: 'dosenpengajar',
        label: 'Dosen Pengajar',
        description: 'Data dosen pengajar per kelas',
        icon: '👨‍🏫',
        color: 'from-rose-500 to-red-500',
    },
     {
        type: 'krs',
        label: 'KRS',
        description: 'Riwayat KRS mahasiswa',
        icon: '📝',
        color: 'from-cyan-500 to-blue-500',
    },
    {
        type: 'nilai',
        label: 'Nilai',
        description: 'Nilai/KHS mahasiswa',
        icon: '📊',
        color: 'from-green-500 to-emerald-500',
    },
    {
        type: 'aktivitas',
        label: 'Aktivitas Kuliah',
        description: 'IPK dan SKS tempuh',
        icon: '📈',
        color: 'from-fuchsia-500 to-pink-500',
    },
    {
        type: 'ajardosen',
        label: 'Ajar Dosen (Real)',
        description: 'Aktivitas mengajar dosen (rekom. BKD/Sister)',
        icon: '👨‍🏫',
        color: 'from-orange-500 to-red-500',
    },
    {
        type: 'bimbingan',
        label: 'Bimbingan Mhs',
        description: 'Bimbingan Tugas Akhir/Skripsi',
        icon: '👥',
        color: 'from-teal-500 to-green-500',
    },
    {
        type: 'uji',
        label: 'Uji Mahasiswa',
        description: 'Penguji Sidang/Tugas Akhir',
        icon: '📝',
        color: 'from-cyan-500 to-blue-500',
    },
    {
        type: 'aktivitasmahasiswa',
        label: 'Aktivitas Mhs',
        description: 'MBKM, KKN, PKL, Prestasi, dll.',
        icon: '🏆',
        color: 'from-violet-500 to-fuchsia-500',
    },
    {
        type: 'anggotaaktivitas',
        label: 'Anggota Aktivitas',
        description: 'Peserta aktivitas mahasiswa',
        icon: '🧑‍🤝‍🧑',
        color: 'from-fuchsia-500 to-pink-500',
    },
    {
        type: 'konversi',
        label: 'Konversi MBKM',
        description: 'Data konversi nilai MBKM',
        icon: '🔄',
        color: 'from-indigo-500 to-purple-500',
    }
];

const syncGroups = [
    {
        title: 'Langkah 1: Data Master',
        description: 'Sync data dasar yang dibutuhkan oleh modul lain',
        types: ['referensi', 'wilayah', 'prodi', 'semester', 'dosen', 'mahasiswa']
    },
    {
        title: 'Langkah 2: Struktur & Detail',
        description: 'Mapping kurikulum dan biodata lengkap',
        types: ['kurikulum', 'matakuliah', 'biodata']
    },
    {
        title: 'Langkah 3: Perkuliahan',
        description: 'Data jadwal, pengajar, dan pengambilan mata kuliah',
        types: ['kelaskuliah', 'dosenpengajar', 'krs']
    },
    {
        title: 'Langkah 4: Hasil & Aktivitas',
        description: 'Nilai, AKM, BKD, dan konversi MBKM',
        types: ['nilai', 'aktivitas', 'ajardosen', 'bimbingan', 'uji', 'aktivitasmahasiswa', 'anggotaaktivitas', 'konversi']
    }
];

const connectionStatus = computed(() => {
    if (testResult.value === null) return 'idle';
    return testResult.value.success ? 'connected' : 'error';
});
</script>

<template>
    <Head title="Sinkronisasi Neo Feeder" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Modern Header -->
            <div class="relative overflow-hidden rounded-2xl bg-linear-to-r from-indigo-600 via-purple-600 to-pink-600 p-8 text-white shadow-xl">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMtOS45NDEgMC0xOCA4LjA1OS0xOCAxOHM4LjA1OSAxOCAxOCAxOGMzLjU4NiAwIDYuOTI5LTEuMDU4IDkuNzQ3LTIuODc1IiBzdHJva2U9IiNmZmYiIHN0cm9rZS1vcGFjaXR5PSIuMDUiIHN0cm9rZS13aWR0aD0iMiIvPjwvZz48L3N2Zz4=')] opacity-20"></div>
                <div class="relative">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm shadow-lg">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight">Sinkronisasi Neo Feeder</h1>
                            <p class="mt-1 text-white/80">Kelola dan sinkronkan data dari API Neo Feeder DIKTI</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-5 gap-6">
                <!-- Left Column: Settings (2 cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Credentials Card -->
                        <div class="rounded-2xl border bg-card/50 backdrop-blur-sm p-6 shadow-lg space-y-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-linear-to-br from-indigo-500 to-purple-500 text-white shadow-lg">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Kredensial API</h3>
                                    <p class="text-xs text-muted-foreground">Konfigurasi koneksi Neo Feeder</p>
                                </div>
                            </div>

                            <!-- URL -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">
                                    URL WebService <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-muted-foreground">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" />
                                        </svg>
                                    </div>
                                    <input
                                        v-model="form.url"
                                        type="url"
                                        class="w-full pl-10 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-background transition-all"
                                        placeholder="http://feeder.dikti.go.id/ws/live2.php"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.url" class="text-red-500 text-sm">{{ form.errors.url }}</p>
                            </div>

                            <!-- Username -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">
                                    Username <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-muted-foreground">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input
                                        v-model="form.username"
                                        type="text"
                                        class="w-full pl-10 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-background transition-all"
                                        placeholder="Username Neo Feeder"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.username" class="text-red-500 text-sm">{{ form.errors.username }}</p>
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-muted-foreground">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        class="w-full pl-10 pr-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-background transition-all"
                                        :placeholder="settings.has_password ? '••••••••' : 'Password Neo Feeder'"
                                    />
                                </div>
                                <div v-if="settings.has_password" class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Password sudah dikonfigurasi
                                </div>
                                <p v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</p>
                            </div>
                        </div>

                        <!-- Connection Status Card -->
                        <div class="rounded-2xl border bg-card/50 backdrop-blur-sm p-5 shadow-lg">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-medium">Status Koneksi</h4>
                                <div class="flex items-center gap-2">
                                    <span :class="[
                                        'h-2.5 w-2.5 rounded-full transition-colors',
                                        connectionStatus === 'connected' ? 'bg-emerald-500 animate-pulse' : 
                                        connectionStatus === 'error' ? 'bg-red-500' : 'bg-gray-400'
                                    ]"></span>
                                    <span class="text-sm text-muted-foreground">
                                        {{ connectionStatus === 'connected' ? 'Terhubung' : 
                                           connectionStatus === 'error' ? 'Gagal' : 'Belum diuji' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Test Result -->
                            <div v-if="testResult" :class="[
                                'rounded-xl p-4 mb-4 transition-all',
                                testResult.success
                                    ? 'bg-linear-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-200 dark:border-emerald-800'
                                    : 'bg-linear-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20 border border-red-200 dark:border-red-800'
                            ]">
                                <div class="flex items-center gap-3">
                                    <div :class="[
                                        'flex h-8 w-8 items-center justify-center rounded-lg',
                                        testResult.success ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'
                                    ]">
                                        <svg v-if="testResult.success" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <span :class="testResult.success ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300'" class="font-medium">
                                        {{ testResult.message }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 px-5 py-3 bg-linear-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/25 disabled:opacity-50 flex items-center justify-center gap-2"
                                >
                                    <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                                </button>
                                <button
                                    type="button"
                                    @click="testConnection"
                                    :disabled="isTesting || !form.url || !form.username"
                                    class="px-5 py-3 border-2 rounded-xl hover:bg-muted/50 transition-all disabled:opacity-50 flex items-center gap-2 font-medium"
                                >
                                    <svg v-if="isTesting" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ isTesting ? 'Testing...' : 'Test' }}
                                </button>
                            </div>
                        </div>

                        <!-- Warning -->
                        <div v-if="!settings.has_password" class="rounded-xl p-4 bg-linear-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-200 dark:border-amber-800">
                            <div class="flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-amber-800 dark:text-amber-300">Kredensial Belum Lengkap</p>
                                    <p class="text-sm text-amber-700 dark:text-amber-400">Simpan kredensial terlebih dahulu untuk melakukan sinkronisasi.</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Sync Grid (3 cols) -->
                <div class="lg:col-span-3">
                    <div class="rounded-2xl border bg-card/50 backdrop-blur-sm p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-linear-to-br from-blue-500 to-cyan-500 text-white shadow-lg">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Sinkronisasi Data</h3>
                                    <p class="text-xs text-muted-foreground">Pilih data yang ingin disinkronkan</p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-4 justify-end">
                                <!-- Date Picker (Sync Since) -->
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                     <label class="text-xs font-medium text-muted-foreground whitespace-nowrap">Sejak Tanggal:</label>
                                     <input 
                                        type="date" 
                                        v-model="syncSince"
                                        class="text-xs bg-background border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition-all shadow-sm"
                                     />
                                </div>

                                <!-- Semester Selector -->
                                <div v-if="semesters && semesters.length > 0" class="flex flex-col sm:flex-row sm:items-center gap-2">
                                    <label class="text-xs font-medium text-muted-foreground whitespace-nowrap">Semester:</label>
                                    <select 
                                        v-model="selectedSemester" 
                                        class="text-xs bg-background border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 transition-all min-w-[140px] shadow-sm"
                                    >
                                        <option value="">Semua Semester</option>
                                        <option v-for="sem in semesters" :key="sem.id_semester" :value="sem.id_semester">
                                            {{ sem.nama_semester }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Sync All Button -->
                                <button
                                    v-if="!isSyncingAll"
                                    @click="syncAll"
                                    :disabled="!settings.has_password"
                                    class="px-5 py-2.5 bg-linear-to-r from-emerald-500 to-teal-500 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all shadow-lg shadow-emerald-500/25 disabled:opacity-50 flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Sync Semua
                                </button>
                                <button
                                    v-else
                                    @click="stopSyncAll"
                                    class="px-5 py-2.5 bg-linear-to-r from-red-500 to-rose-500 text-white font-medium rounded-xl hover:from-red-600 hover:to-rose-600 transition-all shadow-lg shadow-red-500/25 flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                    </svg>
                                    Stop
                                </button>
                            </div>
                        </div>

                        <!-- Sync All Progress Panel -->
                        <div v-if="isSyncingAll || syncAllProgress > 0" class="mb-6 rounded-xl border bg-linear-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <svg v-if="isSyncingAll" class="w-5 h-5 animate-spin text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <svg v-else class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-medium">
                                        {{ isSyncingAll ? 'Sinkronisasi Berjalan...' : 'Sinkronisasi Selesai' }}
                                    </span>
                                </div>
                                <span class="text-sm font-bold text-indigo-600">{{ syncAllProgress }}%</span>
                            </div>
                            
                            <!-- Overall Progress Bar -->
                            <div class="w-full bg-white dark:bg-black/20 rounded-full h-2.5 overflow-hidden mb-3">
                                <div 
                                    class="h-2.5 rounded-full transition-all bg-linear-to-r from-indigo-500 to-purple-500"
                                    :style="{ width: syncAllProgress + '%' }"
                                ></div>
                            </div>
                            
                            <!-- Current Sync Status -->
                            <div v-if="isSyncingAll && currentSyncIndex >= 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="(type, index) in syncOrder"
                                    :key="type"
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-lg transition-all',
                                        index < currentSyncIndex ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                        index === currentSyncIndex ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 ring-2 ring-indigo-500 animate-pulse' :
                                        'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
                                    ]"
                                >
                                    {{ syncTypes.find(s => s.type === type)?.icon }}
                                    {{ syncTypes.find(s => s.type === type)?.label }}
                                    <span v-if="index < currentSyncIndex">✓</span>
                                    <span v-if="index === currentSyncIndex && syncStates[type]?.loading">...</span>
                                </span>
                            </div>
                            
                            <!-- Errors -->
                            <div v-if="syncAllErrors.length > 0" class="mt-3 p-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 text-xs">
                                <p class="font-medium mb-1">{{ syncAllErrors.length }} Error(s):</p>
                                <ul class="list-disc list-inside">
                                    <li v-for="(err, i) in syncAllErrors" :key="i">{{ err }}</li>
                                </ul>
                            </div>
                        </div>


                        <div class="space-y-10">
                            <div v-for="group in syncGroups" :key="group.title" class="space-y-4">
                                <div class="border-l-4 border-indigo-500 pl-4 py-1">
                                    <h4 class="font-bold text-lg">{{ group.title }}</h4>
                                    <p class="text-sm text-muted-foreground">{{ group.description }}</p>
                                </div>
                                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div
                                        v-for="st in syncTypes.filter(s => group.types.includes(s.type))"
                                        :key="st.type"
                                        :class="[
                                            'group relative flex flex-col rounded-2xl border bg-card/50 backdrop-blur-sm p-4 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-indigo-300',
                                            syncStates[st.type]?.loading ? 'ring-2 ring-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/10' : ''
                                        ]"
                                    >
                                        <!-- Card Header -->
                                        <div class="flex items-start justify-between mb-3">
                                            <div :class="['flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br text-white shadow-lg shrink-0', st.color]">
                                                <span class="text-xl">{{ st.icon }}</span>
                                            </div>
                                            
                                            <!-- Actions -->
                                            <div class="flex items-center gap-2">
                                                 <!-- Error Button -->
                                                <button
                                                    v-if="accumulatedStats[st.type]?.errors?.length > 0"
                                                    @click="openErrorModal(st.label, accumulatedStats[st.type].errors)"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition-all tooltip"
                                                    title="Lihat Error"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </button>

                                                <!-- Sync Button -->
                                                <button
                                                    @click="syncData(st.type, 0, selectedSemester, syncSince)"
                                                    :disabled="syncStates[st.type]?.loading || !settings.has_password || isSyncingAll"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/80 hover:bg-indigo-500 hover:text-white transition-all disabled:opacity-30"
                                                >
                                                <svg v-if="syncStates[st.type]?.loading" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                            </button>
                                        </div>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 space-y-1">
                                            <h4 class="font-bold tracking-tight text-sm">{{ st.label }}</h4>
                                            <p class="text-[10px] leading-tight text-muted-foreground line-clamp-2">
                                                {{ st.description }}
                                            </p>
                                        </div>

                                        <!-- Progress Detail -->
                                        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                                            <div v-if="syncStates[st.type]?.loading" class="space-y-1.5">
                                                <div class="flex justify-between text-[9px] font-medium">
                                                    <span class="text-indigo-600 animate-pulse">{{ accumulatedStats[st.type]?.total_synced || 0 }} synced</span>
                                                    <span>{{ accumulatedStats[st.type]?.progress || 0 }}%</span>
                                                </div>
                                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1 overflow-hidden">
                                                    <div 
                                                        class="h-1 rounded-full bg-linear-to-r from-indigo-500 to-purple-500 transition-all duration-500"
                                                        :style="{ width: (accumulatedStats[st.type]?.progress || 0) + '%' }"
                                                    ></div>
                                                </div>
                                            </div>

                                            <div v-else-if="syncStates[st.type]?.result" class="space-y-1.5">
                                                <div class="flex items-center justify-between text-[10px]">
                                                    <div class="flex items-center gap-1 font-bold" :class="syncStates[st.type]?.result?.success ? 'text-emerald-600' : 'text-red-500'">
                                                        <svg v-if="syncStates[st.type]?.result?.success" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        {{ syncStates[st.type]?.result?.success ? 'Selesai' : 'Gagal' }}
                                                    </div>
                                                    <span class="text-[9px] text-muted-foreground font-mono">
                                                        {{ accumulatedStats[st.type]?.total_synced || 0 }}/{{ accumulatedStats[st.type]?.total_api || '?' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div v-else class="text-[9px] text-muted-foreground italic text-center">
                                                Ready
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Error Detail Modal -->
        <div v-if="errorModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-card w-full max-w-2xl rounded-2xl shadow-2xl border flex flex-col max-h-[80vh]">
                <div class="p-4 border-b flex justify-between items-center">
                    <h3 class="font-semibold text-lg">Error Log: {{ errorModalTitle }}</h3>
                    <button @click="closeErrorModal" class="p-1 hover:bg-muted rounded-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4 overflow-y-auto flex-1">
                    <ul class="space-y-2">
                        <li v-for="(err, idx) in selectedErrors" :key="idx" class="text-sm text-red-600 font-mono p-2 bg-red-50 rounded border border-red-100 break-all">
                            {{ err }}
                        </li>
                    </ul>
                </div>
                <div class="p-4 border-t bg-muted/20 flex justify-end">
                     <button @click="closeErrorModal" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
