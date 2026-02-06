<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

interface Semester {
    id: number;
    id_semester: string;
    nama_semester: string;
    tahun: number;
    semester: string;
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    is_active: boolean;
}

defineProps<{
    semesters: Semester[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Tahun Akademik', href: '/admin/akademik/semester' },
];

// Sync state per semester
const syncingStates = ref<Record<string, boolean>>({});
const syncingKrsStates = ref<Record<string, boolean>>({});
const syncProgress = ref<Record<string, { synced: number; total: number; message: string }>>({});
const syncErrors = ref<Record<string, string>>({});

// Auto sync all state
const autoSyncing = ref(false);
const autoSyncProgress = ref({ current: 0, total: 0, currentSemester: '', totalSynced: 0 });
const autoSyncError = ref('');

async function syncNilaiSemester(semesterId: string, semesterName: string) {
    syncingStates.value[semesterId] = true;
    syncErrors.value[semesterId] = '';
    syncProgress.value[semesterId] = { synced: 0, total: 0, message: 'Menghubungi API...' };
    
    let offset = 0;
    let hasMore = true;
    let totalSynced = 0;
    
    try {
        while (hasMore) {
            const response = await axios.post('/admin/sync/nilai-semester', {
                semester_id: semesterId,
                offset: offset
            });
            
            const data = response.data.data;
            totalSynced += data.synced;
            
            syncProgress.value[semesterId] = {
                synced: totalSynced,
                total: data.total_from_api,
                message: `${totalSynced} nilai tersimpan...`
            };
            
            hasMore = data.has_more;
            offset = data.next_offset || 0;
        }
        
        syncProgress.value[semesterId] = {
            synced: totalSynced,
            total: totalSynced,
            message: `✅ Selesai! ${totalSynced} nilai berhasil disync`
        };
        
        return totalSynced;
    } catch (error: any) {
        syncErrors.value[semesterId] = error.response?.data?.message || error.message || 'Gagal sync';
        syncProgress.value[semesterId] = {
            synced: totalSynced,
            total: 0,
            message: '❌ Error'
        };
        throw error;
    } finally {
        syncingStates.value[semesterId] = false;
    }
}

async function syncKrsSemester(semesterId: string, semesterName: string) {
    const key = semesterId + '_krs';
    syncingKrsStates.value[semesterId] = true;
    syncErrors.value[key] = '';
    syncProgress.value[key] = { synced: 0, total: 0, message: 'Menghubungi API...' };
    
    let offset = 0;
    let hasMore = true;
    let totalSynced = 0;
    
    try {
        while (hasMore) {
            const response = await axios.post('/admin/sync/krs-semester', {
                semester_id: semesterId,
                offset: offset
            });
            
            const data = response.data.data;
            totalSynced += data.synced;
            
            syncProgress.value[key] = {
                synced: totalSynced,
                total: data.total_from_api,
                message: `${totalSynced} KRS tersimpan...`
            };
            
            hasMore = data.has_more;
            offset = data.next_offset || 0;
        }
        
        syncProgress.value[key] = {
            synced: totalSynced,
            total: totalSynced,
            message: `✅ Selesai! ${totalSynced} KRS berhasil disync`
        };
        
        return totalSynced;
    } catch (error: any) {
        syncErrors.value[key] = error.response?.data?.message || error.message || 'Gagal sync';
        syncProgress.value[key] = {
            synced: totalSynced,
            total: 0,
            message: '❌ Error'
        };
        throw error;
    } finally {
        syncingKrsStates.value[semesterId] = false;
    }
}

async function syncAllSemesters() {
    autoSyncing.value = true;
    autoSyncError.value = '';
    autoSyncProgress.value = { current: 0, total: 0, currentSemester: 'Menghitung semester...', totalSynced: 0 };
    
    try {
        // Get semester range
        const rangeResponse = await axios.post('/admin/sync/nilai-auto');
        const semesters = rangeResponse.data.data.semesters;
        
        autoSyncProgress.value.total = semesters.length;
        
        if (semesters.length === 0) {
            autoSyncError.value = 'Tidak ada semester yang ditemukan';
            return;
        }
        
        let totalSynced = 0;
        
        // Sync each semester
        for (let i = 0; i < semesters.length; i++) {
            const sem = semesters[i];
            autoSyncProgress.value.current = i + 1;
            autoSyncProgress.value.currentSemester = sem.nama;
            
            try {
                const synced = await syncNilaiSemester(sem.id_semester, sem.nama);
                totalSynced += synced;
                autoSyncProgress.value.totalSynced = totalSynced;
            } catch (e) {
                // Continue with next semester even if one fails
                console.error(`Failed to sync ${sem.nama}:`, e);
            }
        }
        
        autoSyncProgress.value.currentSemester = `✅ Selesai! Total ${totalSynced} nilai tersync`;
    } catch (error: any) {
        autoSyncError.value = error.response?.data?.message || error.message || 'Gagal';
    } finally {
        autoSyncing.value = false;
    }
}
</script>

<template>
    <Head title="Tahun Akademik" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-2xl font-bold">Tahun Akademik / Semester</h1>
                    <p class="text-muted-foreground">Sync nilai per semester atau gunakan Auto Sync untuk semua semester sekaligus.</p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="syncAllSemesters"
                        :disabled="autoSyncing"
                        :class="[
                            'px-6 py-3 rounded-lg font-medium transition-colors',
                            autoSyncing
                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800'
                                : 'bg-emerald-600 text-white hover:bg-emerald-700'
                        ]"
                    >
                        <span v-if="autoSyncing" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ autoSyncProgress.current }}/{{ autoSyncProgress.total }}
                        </span>
                        <span v-else>🚀 Auto Sync All</span>
                    </button>
                </div>
            </div>
            
            <!-- Auto Sync Progress Banner -->
            <div v-if="autoSyncing || autoSyncProgress.currentSemester" class="rounded-xl border p-4" :class="autoSyncError ? 'bg-red-50 border-red-200 dark:bg-red-900/20' : 'bg-blue-50 border-blue-200 dark:bg-blue-900/20'">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium" :class="autoSyncError ? 'text-red-800 dark:text-red-200' : 'text-blue-800 dark:text-blue-200'">
                            {{ autoSyncError || autoSyncProgress.currentSemester }}
                        </p>
                        <p v-if="!autoSyncError && autoSyncing" class="text-sm text-blue-600 dark:text-blue-400">
                            Total nilai tersync: {{ autoSyncProgress.totalSynced }}
                        </p>
                    </div>
                    <div v-if="autoSyncing" class="text-sm text-blue-600 dark:text-blue-400">
                        Semester {{ autoSyncProgress.current }} dari {{ autoSyncProgress.total }}
                    </div>
                </div>
            </div>

            <!-- Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="sem in semesters"
                    :key="sem.id"
                    :class="[
                        'rounded-xl border p-6 shadow-sm',
                        sem.is_active ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-900/20 dark:border-emerald-800' : 'bg-card'
                    ]"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-lg">{{ sem.nama_semester }}</h3>
                            <p class="text-sm text-muted-foreground">ID: {{ sem.id_semester }}</p>
                        </div>
                        <span
                            v-if="sem.is_active"
                            class="px-2 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full text-xs font-medium"
                        >
                            Aktif
                        </span>
                    </div>
                    <dl class="text-sm space-y-1 mb-4">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Periode</dt>
                            <dd>{{ sem.tanggal_mulai || '-' }} s/d {{ sem.tanggal_selesai || '-' }}</dd>
                        </div>
                    </dl>
                    
                    <!-- Sync Buttons -->
                    <div class="border-t pt-4 space-y-2">
                        <button
                            @click="syncKrsSemester(sem.id_semester, sem.nama_semester)"
                            :disabled="syncingKrsStates[sem.id_semester] || autoSyncing"
                            :class="[
                                'w-full px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                syncingKrsStates[sem.id_semester] || autoSyncing
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800'
                                    : 'bg-indigo-600 text-white hover:bg-indigo-700'
                            ]"
                        >
                            <span v-if="syncingKrsStates[sem.id_semester]" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Syncing KRS...
                            </span>
                            <span v-else>📚 Sync KRS</span>
                        </button>

                        <!-- KRS Progress -->
                        <div v-if="syncProgress[sem.id_semester + '_krs']" class="text-xs text-center mb-2">
                            <span :class="syncErrors[sem.id_semester + '_krs'] ? 'text-red-600' : 'text-muted-foreground'">
                                {{ syncProgress[sem.id_semester + '_krs'].message }}
                            </span>
                             <div v-if="syncErrors[sem.id_semester + '_krs']" class="text-red-600">
                                {{ syncErrors[sem.id_semester + '_krs'] }}
                            </div>
                        </div>

                        <div class="border-t my-2"></div>

                        <button
                            @click="syncNilaiSemester(sem.id_semester, sem.nama_semester)"
                            :disabled="syncingStates[sem.id_semester] || autoSyncing"
                            :class="[
                                'w-full px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                syncingStates[sem.id_semester] || autoSyncing
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-800'
                                    : 'bg-blue-600 text-white hover:bg-blue-700'
                            ]"
                        >
                            <span v-if="syncingStates[sem.id_semester]" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Syncing...
                            </span>
                            <span v-else>🔄 Sync Nilai</span>
                        </button>
                        
                        <!-- Progress -->
                        <div v-if="syncProgress[sem.id_semester]" class="mt-2 text-xs text-center">
                            <span :class="syncErrors[sem.id_semester] ? 'text-red-600' : 'text-muted-foreground'">
                                {{ syncProgress[sem.id_semester].message }}
                            </span>
                        </div>
                        
                        <!-- Error -->
                        <div v-if="syncErrors[sem.id_semester]" class="mt-2 text-xs text-center text-red-600">
                            {{ syncErrors[sem.id_semester] }}
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="semesters.length === 0" class="rounded-xl border bg-card p-12 text-center">
                <p class="text-muted-foreground">Belum ada data semester. Silakan sync dari Neo Feeder.</p>
            </div>
        </div>
    </AppLayout>
</template>


