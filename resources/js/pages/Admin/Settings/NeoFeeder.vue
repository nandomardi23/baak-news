<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';

interface Settings {
    url: string;
    username: string;
    password: string;
    has_password: boolean;
}

interface SyncResult {
    success: boolean;
    message: string;
    data?: {
        total_from_api: number;
        synced: number;
        failed: number;
        inserted?: number;
        updated?: number;
        skipped?: number;
        errors: string[];
        batch_size?: number;
        offset?: number;
        next_offset?: number | null;
        has_more?: boolean;
        progress?: number;
    };
}

const props = defineProps<{
    settings: Settings;
}>();

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

const syncStates = reactive<Record<string, { loading: boolean; result: SyncResult | null }>>({
    prodi: { loading: false, result: null },
    semester: { loading: false, result: null },
    matakuliah: { loading: false, result: null },
    mahasiswa: { loading: false, result: null },
    biodata: { loading: false, result: null },
    dosen: { loading: false, result: null },
    nilai: { loading: false, result: null },
    krs: { loading: false, result: null },
    aktivitas: { loading: false, result: null },
    kelaskuliah: { loading: false, result: null },
    dosenpengajar: { loading: false, result: null },
    ajardosen: { loading: false, result: null },
    bimbingan: { loading: false, result: null },
    uji: { loading: false, result: null },
    aktivitasmahasiswa: { loading: false, result: null },
    anggotaaktivitas: { loading: false, result: null },
    konversi: { loading: false, result: null },
});

// Accumulated counters for paginated syncs
const accumulatedStats = reactive<Record<string, { synced: number; failed: number }>>({
    prodi: { synced: 0, failed: 0 },
    semester: { synced: 0, failed: 0 },
    matakuliah: { synced: 0, failed: 0 },
    mahasiswa: { synced: 0, failed: 0 },
    biodata: { synced: 0, failed: 0 },
    dosen: { synced: 0, failed: 0 },
    nilai: { synced: 0, failed: 0 },
    krs: { synced: 0, failed: 0 },
    aktivitas: { synced: 0, failed: 0 },
    kelaskuliah: { synced: 0, failed: 0 },
    dosenpengajar: { synced: 0, failed: 0 },
    ajardosen: { synced: 0, failed: 0 },
    bimbingan: { synced: 0, failed: 0 },
    uji: { synced: 0, failed: 0 },
    aktivitasmahasiswa: { synced: 0, failed: 0 },
    anggotaaktivitas: { synced: 0, failed: 0 },
    konversi: { synced: 0, failed: 0 },
});

// Sync All State
const isSyncingAll = ref(false);
const currentSyncIndex = ref(-1);
const syncAllProgress = ref(0);
const syncAllErrors = ref<string[]>([]);

const syncOrder = ['prodi', 'semester', 'matakuliah', 'mahasiswa', 'dosen', 'biodata', 'nilai', 'krs', 'aktivitas', 'kelaskuliah', 'dosenpengajar', 'ajardosen', 'bimbingan', 'uji', 'aktivitasmahasiswa', 'anggotaaktivitas', 'konversi'];

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

const syncData = async (type: string, offset: number = 0): Promise<boolean> => {
    const state = syncStates[type];
    // All sync types now support pagination
    const isPaginated = true;
    
    
    state.loading = true;
    
    // Reset accumulated stats at start of new sync
    if (offset === 0) {
        state.result = null;
        if (isPaginated && accumulatedStats[type]) {
            accumulatedStats[type].synced = 0;
            accumulatedStats[type].failed = 0;
        }
    }

    try {
        // Map type to correct route path
        const routeMapping: Record<string, string> = {
            'dosenpengajar': 'dosen-pengajar',
            'kelaskuliah': 'kelas-kuliah',
            'ajardosen': 'ajar-dosen',
            'bimbingan': 'bimbingan-mahasiswa',
            'uji': 'uji-mahasiswa',
            'aktivitasmahasiswa': 'aktivitas-mahasiswa',
            'anggotaaktivitas': 'anggota-aktivitas-mahasiswa',
            'konversi': 'konversi-kampus-merdeka',
        };
        const routePath = routeMapping[type] || type;
        const url = isPaginated
            ? `/admin/sync/${routePath}?offset=${offset}`
            : `/admin/sync/${routePath}`;
            
        const payload: any = {};

        const response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(payload),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        const data = await response.json();
        
        // For paginated types, accumulate the counts
        if (isPaginated && data.success && data.data && accumulatedStats[type]) {
            accumulatedStats[type].synced += data.data.synced || 0;
            accumulatedStats[type].failed += data.data.failed || 0;
            
            // Update result with accumulated totals
            state.result = {
                ...data,
                data: {
                    ...data.data,
                    synced: accumulatedStats[type].synced,
                    failed: accumulatedStats[type].failed,
                }
            };
        } else {
            state.result = data;
        }
        
        if (data.success && data.data?.has_more && data.data?.next_offset !== null) {
            // Wait and continue pagination
            await new Promise(resolve => setTimeout(resolve, 500));
            return await syncData(type, data.data.next_offset);
        } else {
            state.loading = false;
            return data.success;
        }
    } catch (error) {
        state.result = {
            success: false,
            message: 'Gagal sync: ' + (error instanceof Error ? error.message : 'Unknown error'),
        };
        state.loading = false;
        return false;
    }
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
        syncStates[type].result = null;
    }
    
    for (let i = 0; i < syncOrder.length; i++) {
        currentSyncIndex.value = i;
        const type = syncOrder[i];
        
        const success = await syncData(type);
        
        if (!success) {
            const syncType = syncTypes.find(s => s.type === type);
            syncAllErrors.value.push(`${syncType?.label || type}: ${syncStates[type].result?.message || 'Gagal'}`);
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
    // Note: This only prevents starting the next sync, current one will complete
    isSyncingAll.value = false;
    currentSyncIndex.value = -1;
};

const syncTypes = [
    {
        type: 'prodi',
        label: 'Program Studi',
        description: 'Data program studi dari Neo Feeder',
        icon: '🎓',
        color: 'from-violet-500 to-purple-500',
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
        type: 'dosen',
        label: 'Dosen',
        description: 'Data dosen dari Neo Feeder',
        icon: '👨‍🏫',
        color: 'from-indigo-500 to-blue-500',
    },
    {
        type: 'nilai',
        label: 'Nilai',
        description: 'Nilai/KHS mahasiswa',
        icon: '📊',
        color: 'from-green-500 to-emerald-500',
    },
    {
        type: 'krs',
        label: 'KRS',
        description: 'Riwayat KRS mahasiswa',
        icon: '📝',
        color: 'from-cyan-500 to-blue-500',
    },
    {
        type: 'aktivitas',
        label: 'Aktivitas',
        description: 'IPK dan SKS tempuh',
        icon: '📈',
        color: 'from-fuchsia-500 to-pink-500',
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
        description: 'Konversi nilai aktivitas MBKM',
        icon: '🔄',
        color: 'from-emerald-500 to-teal-500',
    },
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


                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="sync in syncTypes"
                                :key="sync.type"
                                class="group relative overflow-hidden rounded-xl border bg-card p-4 transition-all hover:shadow-lg hover:border-indigo-300 dark:hover:border-indigo-700"
                            >
                                <!-- Gradient Accent -->
                                <div :class="['absolute top-0 left-0 h-1 w-full bg-linear-to-r', sync.color]"></div>
                                
                                <div class="flex flex-col h-full">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">{{ sync.icon }}</span>
                                            <h4 class="font-semibold">{{ sync.label }}</h4>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-muted-foreground mb-4 flex-1">{{ sync.description }}</p>

                                    <!-- Sync Button -->
                                    <button
                                        @click="syncData(sync.type)"
                                        :disabled="syncStates[sync.type].loading || !settings.has_password || isSyncingAll"
                                        :class="['w-full px-4 py-2.5 text-white font-medium rounded-lg transition-all disabled:opacity-50 flex items-center justify-center gap-2 text-sm shadow-lg', 
                                                 'bg-linear-to-r hover:shadow-xl', sync.color]"
                                    >
                                        <svg
                                            v-if="syncStates[sync.type].loading"
                                            class="w-4 h-4 animate-spin"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span v-if="syncStates[sync.type].loading && syncStates[sync.type].result?.data?.progress">
                                            {{ syncStates[sync.type].result?.data?.progress }}%
                                        </span>
                                        <span v-else>{{ syncStates[sync.type].loading ? 'Syncing...' : 'Sync' }}</span>
                                    </button>

                                    <!-- Live Progress Bar (shown during syncing) -->
                                    <div v-if="syncStates[sync.type].loading && syncStates[sync.type].result?.data" class="mt-3 space-y-2">
                                        <!-- Total Count Display -->
                                        <div v-if="syncStates[sync.type].result?.data?.total_from_api" 
                                            class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-2 text-center">
                                            <span class="text-indigo-600 dark:text-indigo-400 font-medium text-xs">
                                                📊 Total dari API: {{ syncStates[sync.type].result?.data?.total_from_api }} data
                                            </span>
                                        </div>
                                        
                                        <!-- Progress Bar -->
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-xs">
                                                <span class="text-muted-foreground">Progress</span>
                                                <span class="font-bold text-indigo-600">{{ syncStates[sync.type].result?.data?.progress || 0 }}%</span>
                                            </div>
                                            <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
                                                <div 
                                                    :class="['h-2 rounded-full transition-all bg-linear-to-r', sync.color]"
                                                    :style="{ width: (syncStates[sync.type].result?.data?.progress || 0) + '%' }"
                                                ></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Result (shown after sync complete) -->
                                    <div v-if="syncStates[sync.type].result && !syncStates[sync.type].loading" class="mt-3">
                                        <!-- Final Progress Bar -->
                                        <div v-if="syncStates[sync.type].result?.data?.progress" class="mb-2">
                                            <div class="flex justify-between text-xs mb-1">
                                                <span class="text-muted-foreground">Progress</span>
                                                <span class="font-medium">{{ syncStates[sync.type].result?.data?.progress }}%</span>
                                            </div>
                                            <div class="w-full bg-muted rounded-full h-1.5 overflow-hidden">
                                                <div 
                                                    :class="['h-1.5 rounded-full transition-all bg-linear-to-r', sync.color]"
                                                    :style="{ width: syncStates[sync.type].result?.data?.progress + '%' }"
                                                ></div>
                                            </div>
                                        </div>

                                        <!-- Stats Grid -->
                                        <div v-if="syncStates[sync.type].result?.data" class="space-y-2 text-xs">
                                            <!-- Total from API (always show for clarity) -->
                                            <div v-if="syncStates[sync.type].result?.data?.total_from_api" 
                                                class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-2 text-center">
                                                <span class="text-indigo-600 dark:text-indigo-400 font-medium">
                                                    Total: {{ syncStates[sync.type].result?.data?.total_from_api }} data
                                                </span>
                                            </div>
                                            
                                            <!-- Detailed Stats (Insert/Update/Skip) - 3 columns -->
                                            <div v-if="syncStates[sync.type].result?.data?.inserted !== undefined || 
                                                        syncStates[sync.type].result?.data?.updated !== undefined || 
                                                        syncStates[sync.type].result?.data?.skipped !== undefined" 
                                                class="grid grid-cols-3 gap-1">
                                                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-lg p-1.5 text-center">
                                                    <span class="block font-bold text-sm">{{ syncStates[sync.type].result?.data?.inserted || 0 }}</span>
                                                    <span class="text-emerald-600 dark:text-emerald-400 text-[10px]">✚ Baru</span>
                                                </div>
                                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-1.5 text-center">
                                                    <span class="block font-bold text-sm">{{ syncStates[sync.type].result?.data?.updated || 0 }}</span>
                                                    <span class="text-blue-600 dark:text-blue-400 text-[10px]">↻ Update</span>
                                                </div>
                                                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-1.5 text-center">
                                                    <span class="block font-bold text-sm">{{ syncStates[sync.type].result?.data?.skipped || 0 }}</span>
                                                    <span class="text-gray-500 dark:text-gray-400 text-[10px]">⏸ Sama</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Fallback: Simple Success/Failed Grid (for endpoints without detailed stats) -->
                                            <div v-else class="grid grid-cols-2 gap-2">
                                                <div class="bg-muted/50 rounded-lg p-2 text-center">
                                                    <span class="block font-bold text-base">{{ syncStates[sync.type].result?.data?.synced }}</span>
                                                    <span class="text-emerald-600 dark:text-emerald-400">✓ Berhasil</span>
                                                </div>
                                                <div class="bg-muted/50 rounded-lg p-2 text-center">
                                                    <span class="block font-bold text-base">{{ syncStates[sync.type].result?.data?.failed || 0 }}</span>
                                                    <span class="text-red-500">✗ Gagal</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Failed count (if any, show separately) -->
                                            <div v-if="(syncStates[sync.type].result?.data?.inserted !== undefined) && (syncStates[sync.type].result?.data?.failed || 0) > 0" 
                                                class="bg-red-50 dark:bg-red-900/20 rounded-lg p-1.5 text-center">
                                                <span class="text-red-600 dark:text-red-400 font-medium">
                                                    ✗ {{ syncStates[sync.type].result?.data?.failed }} Gagal
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Status Message -->
                                        <div v-else :class="[
                                            'rounded-lg p-2 text-xs text-center mt-2',
                                            syncStates[sync.type].result?.success
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                                : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        ]">
                                            {{ syncStates[sync.type].result?.message }}
                                        </div>

                                        <!-- Errors -->
                                        <div v-if="syncStates[sync.type].result?.data?.errors?.length" class="mt-2 text-xs">
                                            <p class="font-medium text-red-600 dark:text-red-400">{{ syncStates[sync.type].result?.data?.errors?.length }} Error(s)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
