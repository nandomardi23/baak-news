import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

export interface SyncResult {
    success: boolean;
    message: string;
    total_from_api?: number;
    synced?: number;
    failed?: number;
    inserted?: number;
    updated?: number;
    skipped?: number;
    errors?: string[];
    // Progress fields
    progress?: number;
    batch_size?: number;
    offset?: number;
    next_offset?: number | null;
    has_more?: boolean;
    // API response total (sometimes mapped differently)
    total_all?: number;
}

export interface SyncState {
    loading: boolean;
    result: SyncResult | null;
}

export function useNeoFeederSync() {
    const isStopping = ref(false);

    // Stats Accumulator
    const accumulatedStats = reactive<Record<string, {
        total_synced: number;
        total_failed: number;
        total_api: number;
        progress: number;
        errors: string[];
    }>>({});

    // Sync States
    const syncStates = reactive<Record<string, SyncState>>({
        referensi: { loading: false, result: null },
        wilayah: { loading: false, result: null },
        prodi: { loading: false, result: null },
        kurikulum: { loading: false, result: null },
        matakuliah: { loading: false, result: null }, // Renamed from matkul
        mahasiswa: { loading: false, result: null },
        biodata: { loading: false, result: null },
        aktivitas: { loading: false, result: null }, // Renamed from akm
        krs: { loading: false, result: null },
        nilai: { loading: false, result: null },
        kelaskuliah: { loading: false, result: null }, // Renamed from kelas
        dosenpengajar: { loading: false, result: null }, // Renamed from pengajar
        dosen: { loading: false, result: null },
        semester: { loading: false, result: null },
        ajardosen: { loading: false, result: null },
        bimbingan: { loading: false, result: null },
        uji: { loading: false, result: null },
        aktivitasmahasiswa: { loading: false, result: null },
        anggotaaktivitas: { loading: false, result: null },
        konversi: { loading: false, result: null },
    });

    const routeMapping: Record<string, string> = {
        'referensi': 'referensi',
        'wilayah': 'referensi', // Logic in syncData handles type='wilayah'
        'prodi': 'prodi',
        'kurikulum': 'kurikulum',
        'matakuliah': 'matakuliah',
        'mahasiswa': 'mahasiswa',
        'biodata': 'biodata',
        'aktivitas': 'aktivitas',
        'krs': 'krs',
        'nilai': 'nilai',
        'kelaskuliah': 'kelas-kuliah',
        'dosenpengajar': 'dosen-pengajar',
        'dosen': 'dosen',
        'semester': 'semester',
        'ajardosen': 'ajar-dosen',
        'bimbingan': 'bimbingan-mahasiswa',
        'uji': 'uji-mahasiswa',
        'aktivitasmahasiswa': 'aktivitas-mahasiswa',
        'anggotaaktivitas': 'anggota-aktivitas-mahasiswa',
        'konversi': 'konversi-kampus-merdeka',
    };

    /**
     * Initialize Accumulator for a type
     */
    const initAccumulator = (type: string) => {
        if (!accumulatedStats[type]) {
            accumulatedStats[type] = {
                total_synced: 0,
                total_failed: 0,
                total_api: 0,
                progress: 0,
                errors: []
            };
        }
    };

    /**
     * Generic Sync Function
     */
    const syncData = async (type: string, offset = 0, idSemester?: string) => {
        if (!syncStates[type] || isStopping.value) return;

        // If starting fresh (offset 0), reset states
        if (offset === 0) {
            isStopping.value = false; // Clear stop signal on fresh start
            syncStates[type].loading = true;
            syncStates[type].result = null;
            // Reset accumulator for this session
            accumulatedStats[type] = {
                total_synced: 0,
                total_failed: 0,
                total_api: 0,
                progress: 0,
                errors: []
            };
        }

        try {
            const endpoint = routeMapping[type];
            if (!endpoint) throw new Error(`Unknown sync type: ${type}`);

            // Special handling for referensi (General Reference)
            if (type === 'referensi' && offset === 0) {
                const subTypes = ['agama', 'jenis_tinggal', 'alat_transportasi', 'pekerjaan', 'penghasilan', 'kebutuhan_khusus', 'pembiayaan'];
                let totalSynced = 0;
                
                for (let i = 0; i < subTypes.length; i++) {
                    if (isStopping.value) break;
                    const sub = subTypes[i];
                    // Update progress based on sub-type step
                    const subProgress = Math.round(((i + 1) / subTypes.length) * 100);
                    
                    const res = await axios.post(route('admin.sync.' + endpoint), { sub_type: sub });
                    if (res.data.success) {
                        const subResult = res.data.data;
                        totalSynced += (subResult.synced || 0);
                        
                        // Update UI state for each step
                        syncStates[type].result = {
                            success: true,
                            message: `Syncing ${sub.replace('_', ' ')}...`,
                            synced: totalSynced,
                            progress: subProgress
                        };
                        
                        // Update accumulator
                        initAccumulator(type);
                        accumulatedStats[type].total_synced = totalSynced;
                        accumulatedStats[type].total_api = totalSynced; // Set total_api once finished or during steps
                        accumulatedStats[type].progress = subProgress;
                    }
                }
                
                syncStates[type].loading = false;
                syncStates[type].result = {
                    success: true,
                    message: 'Sync Referensi Umum selesai',
                    synced: totalSynced,
                    progress: 100
                };
                return;
            }

            // Special param for wilayah
            const params: any = { offset, limit: 1000 }; 
            // Adjust limits based on type for optimization
            if (['mahasiswa', 'matakuliah', 'kelaskuliah', 'krs', 'nilai'].includes(type)) params.limit = 2000;
            if (['biodata', 'aktivitas', 'ajardosen'].includes(type)) params.limit = 100;

            if (['wilayah'].includes(type)) {
                params.type = 'wilayah';
                params.limit = 2000;
            }
            
            // Add semester filter if provided
            if (idSemester) {
                params.id_semester = idSemester;
            }

            const response = await axios.post(route('admin.sync.' + endpoint), params);

            if (response.data.success) {
                const result = response.data.data;
                // Add success/message from wrapper to result object if needed, 
                // but usually we trust the data block. 
                // Let's ensure result has success/message for NeoFeeder.vue compat
                const fullResult: SyncResult = {
                    success: true,
                    message: response.data.message || 'Sync successful',
                    ...result
                };
                
                syncStates[type].result = fullResult;

                // Update Accumulator
                initAccumulator(type);
                const acc = accumulatedStats[type];
                
                acc.total_synced += (result.synced || 0) + (result.updated || 0) + (result.inserted || 0);
                acc.total_failed += (result.failed || 0);
                acc.total_api = result.total_all || result.total_from_api || 0;
                acc.progress = result.progress || 0;

                if (result.errors && result.errors.length > 0) {
                     acc.errors.push(...result.errors);
                }

                // Recursive call if has_more
                if (result.has_more && result.next_offset && !isStopping.value) {
                    await syncData(type, result.next_offset, idSemester);
                } else {
                    syncStates[type].loading = false;
                    if (isStopping.value) {
                        syncStates[type].result = {
                            success: false,
                            message: 'Sinkronisasi diberhentikan oleh pengguna',
                            ...result
                        };
                    }
                }
            } else {
                throw new Error(response.data.message);
            }
        } catch (error: any) {
            syncStates[type].loading = false;
            // Set result to error state so UI shows it
            syncStates[type].result = {
                success: false,
                message: error.response?.data?.message || error.message || 'Sync Failed',
                failed: (accumulatedStats[type]?.total_failed || 0) + 1,
                errors: [...(accumulatedStats[type]?.errors || []), error.message]
            };
        }
    };

    const cancelAllSyncs = () => {
        isStopping.value = true;
        // Reset loading states for all
        Object.keys(syncStates).forEach(key => {
            if (syncStates[key].loading) {
                syncStates[key].loading = false;
            }
        });
    };

    return {
        syncStates,
        accumulatedStats,
        syncData,
        routeMapping,
        cancelAllSyncs,
        isStopping
    };
}
