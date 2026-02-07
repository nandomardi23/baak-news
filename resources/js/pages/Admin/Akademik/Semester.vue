<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { Button } from '@/components/ui/button';
import {
    DataTable,
    TableHeader,
    TableRow,
    TableCell,
} from '@/components/ui/datatable';
import { 
    RefreshCcw, 
    BookOpen, 
    Calendar, 
    CheckCircle2, 
    AlertCircle, 
    Play,
    Loader2
} from 'lucide-vue-next';

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
            message: `Selesai! ${totalSynced} nilai`
        };
        
        return totalSynced;
    } catch (error: any) {
        syncErrors.value[semesterId] = error.response?.data?.message || error.message || 'Gagal sync';
        syncProgress.value[semesterId] = {
            synced: totalSynced,
            total: 0,
            message: 'Error'
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
            message: `Selesai! ${totalSynced} KRS`
        };
        
        return totalSynced;
    } catch (error: any) {
        syncErrors.value[key] = error.response?.data?.message || error.message || 'Gagal sync';
        syncProgress.value[key] = {
            synced: totalSynced,
            total: 0,
            message: 'Error'
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
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Tahun Akademik</h1>
                    <p class="text-slate-500 mt-1">Kelola data tahun akademik dan sinkronisasi nilai/KRS.</p>
                </div>
            </div>

            <!-- Auto Sync Progress Card -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 translateY-2"
                enter-to-class="opacity-100 translateY-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translateY-0"
                leave-to-class="opacity-0 translateY-2"
            >
                <div v-if="autoSyncing || autoSyncProgress.currentSemester" 
                    class="rounded-xl border p-4 shadow-sm"
                    :class="autoSyncError ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-100'"
                >
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center shrink-0"
                            :class="autoSyncError ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'">
                            <AlertCircle v-if="autoSyncError" class="w-5 h-5" />
                            <Loader2 v-else-if="autoSyncing" class="w-5 h-5 animate-spin" />
                            <CheckCircle2 v-else class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-sm" 
                                :class="autoSyncError ? 'text-red-900' : 'text-blue-900'">
                                {{ autoSyncError ? 'Sync Gagal' : (autoSyncing ? 'Sedang Sinkronisasi Otomatis...' : 'Sinkronisasi Selesai') }}
                            </h4>
                            <p class="text-sm mt-0.5" 
                                :class="autoSyncError ? 'text-red-700' : 'text-blue-700'">
                                {{ autoSyncError || autoSyncProgress.currentSemester }}
                            </p>
                        </div>
                        <div v-if="autoSyncing && !autoSyncError" class="text-right">
                            <span class="text-2xl font-bold text-blue-700">{{ autoSyncProgress.current }}</span>
                            <span class="text-sm text-blue-500">/{{ autoSyncProgress.total }}</span>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Standardized DataTable -->
            <DataTable>
                <!-- Toolbar Slot -->
                <template #toolbar>
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <Calendar class="w-4 h-4" />
                        </div>
                        <h3 class="text-base font-bold text-slate-700">Daftar Semester</h3>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 items-center w-full sm:w-auto ml-auto">
                        <Button
                            @click="syncAllSemesters"
                            :disabled="autoSyncing"
                            variant="default"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm"
                        >
                            <Loader2 v-if="autoSyncing" class="w-4 h-4 mr-2 animate-spin" />
                            <Play v-else class="w-4 h-4 mr-2" />
                            {{ autoSyncing ? 'Processing...' : 'Auto Sync All' }}
                        </Button>
                    </div>
                </template>

                <!-- Table Header -->
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <TableHeader class="w-[80px] pl-6">ID</TableHeader>
                        <TableHeader>Semester</TableHeader>
                        <TableHeader>Periode Akademik</TableHeader>
                        <TableHeader class="text-center w-[120px]">Status</TableHeader>
                        <TableHeader class="text-right w-[250px] pr-6">Aksi Sinkronisasi</TableHeader>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="divide-y divide-slate-100">
                    <TableRow v-for="sem in semesters" :key="sem.id" class="group hover:bg-slate-50/50 transition-all duration-200">
                        <TableCell class="font-mono text-xs text-slate-500 pl-6">{{ sem.id_semester }}</TableCell>
                        <TableCell>
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-900">{{ sem.nama_semester }}</span>
                                <span class="text-xs text-slate-500 font-medium">Tahun {{ sem.tahun }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="text-slate-600">
                             <div class="flex items-center gap-3 text-sm font-medium">
                                <span class="text-slate-700">{{ sem.tanggal_mulai || '-' }}</span>
                                <span class="text-slate-300">→</span>
                                <span class="text-slate-700">{{ sem.tanggal_selesai || '-' }}</span>
                            </div>
                        </TableCell>
                        <TableCell class="text-center">
                            <span v-if="sem.is_active" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm">
                                • Aktif
                            </span>
                            <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                History
                            </span>
                        </TableCell>
                        <TableCell class="text-right pr-6">
                            <div class="flex flex-row gap-2 justify-end items-center">
                                <!-- Status Messages (absolute or tooltip could be better, but sticky to button for now) -->
                                
                                <div class="flex items-center gap-2">
                                     <!-- Sync KRS -->
                                    <div class="relative">
                                        <div v-if="syncProgress[sem.id_semester + '_krs']" class="absolute -top-5 right-0 whitespace-nowrap text-[10px] font-medium text-indigo-600 animate-pulse bg-indigo-50 px-1.5 py-0.5 rounded">
                                            {{ syncProgress[sem.id_semester + '_krs'].message }}
                                        </div>
                                        <Button 
                                            size="sm" 
                                            variant="outline"
                                            :disabled="syncingKrsStates[sem.id_semester] || autoSyncing"
                                            @click="syncKrsSemester(sem.id_semester, sem.nama_semester)"
                                            class="h-8 text-xs font-medium border-slate-200 text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 transition-all shadow-sm"
                                        >
                                            <Loader2 v-if="syncingKrsStates[sem.id_semester]" class="w-3.5 h-3.5 mr-1.5 animate-spin text-indigo-600" />
                                            <BookOpen v-else class="w-3.5 h-3.5 mr-1.5 text-indigo-500" />
                                            Sync KRS
                                        </Button>
                                    </div>
                                    
                                     <!-- Sync Nilai -->
                                    <div class="relative">
                                        <div v-if="syncProgress[sem.id_semester]" class="absolute -top-5 right-0 whitespace-nowrap text-[10px] font-medium text-blue-600 animate-pulse bg-blue-50 px-1.5 py-0.5 rounded">
                                            {{ syncProgress[sem.id_semester].message }}
                                        </div>
                                        <Button 
                                            size="sm" 
                                            variant="outline"
                                            :disabled="syncingStates[sem.id_semester] || autoSyncing"
                                            @click="syncNilaiSemester(sem.id_semester, sem.nama_semester)"
                                            class="h-8 text-xs font-medium border-slate-200 text-slate-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 transition-all shadow-sm"
                                        >
                                            <Loader2 v-if="syncingStates[sem.id_semester]" class="w-3.5 h-3.5 mr-1.5 animate-spin text-blue-600" />
                                            <RefreshCcw v-else class="w-3.5 h-3.5 mr-1.5 text-blue-500" />
                                            Sync Nilai
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div v-if="syncErrors[sem.id_semester] || syncErrors[sem.id_semester + '_krs']" class="text-[10px] font-medium text-red-600 mt-1 text-right bg-red-50 px-2 py-1 rounded inline-block">
                                {{ syncErrors[sem.id_semester] || syncErrors[sem.id_semester + '_krs'] }}
                            </div>
                        </TableCell>
                    </TableRow>
                    
                    <TableRow v-if="semesters.length === 0">
                        <TableCell colspan="5" class="h-48 text-center text-slate-500">
                            Belum ada data semester. Silakan lakukan sinkronisasi data referensi dari Neo Feeder.
                        </TableCell>
                    </TableRow>
                </tbody>
            </DataTable>
        </div>
    </AppLayout>
</template>
