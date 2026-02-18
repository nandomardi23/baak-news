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
        total_api: number | null;
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

    const routeMapping: Record<string, any> = {
        'referensi': { url: 'referensi', limit: 100, label: 'Referensi Umum' },
        'wilayah': { url: 'referensi', limit: 100, label: 'Data Wilayah' },
        'prodi': { url: 'prodi', limit: 100, label: 'Program Studi' },
        'kurikulum': { url: 'kurikulum', limit: 100, label: 'Kurikulum' },
        'matakuliah': { url: 'matakuliah', limit: 100, label: 'Mata Kuliah' },
        'mahasiswa': { url: 'mahasiswa', limit: 100, label: 'Mahasiswa' },
        'biodata': { url: 'biodata', limit: 100, label: 'Biodata Mahasiswa' },
        'aktivitas': { url: 'aktivitas', limit: 100, label: 'Aktivitas Kuliah' },
        'krs': { url: 'krs', limit: 100, label: 'KRS Mahasiswa' },
        'nilai': { url: 'nilai', limit: 100, label: 'Nilai Perkuliahan' },
        'kelaskuliah': { url: 'kelas-kuliah', limit: 100, label: 'Kelas Kuliah' },
        'dosenpengajar': { url: 'dosen-pengajar', limit: 100, label: 'Dosen Pengajar' },
        'dosen': { url: 'dosen', limit: 100, label: 'Dosen' },
        'semester': { url: 'semester', limit: 100, label: 'Semester' },
        'ajardosen': { url: 'ajar-dosen', limit: 100, label: 'Ajar Dosen' },
        'bimbingan': { url: 'bimbingan-mahasiswa', limit: 100, label: 'Bimbingan Mahasiswa' },
        'uji': { url: 'uji-mahasiswa', limit: 100, label: 'Uji Mahasiswa' },
        'aktivitasmahasiswa': { url: 'aktivitas-mahasiswa', limit: 100, label: 'Aktivitas Mahasiswa' },
        'anggotaaktivitas': { url: 'anggota-aktivitas-mahasiswa', limit: 100, label: 'Anggota Aktivitas' },
        'konversi': { url: 'konversi-kampus-merdeka', limit: 100, label: 'Konversi MBKM' },
    };

    /**
     * Initialize Accumulator for a type
     */
    const initAccumulator = (type: string) => {
        if (!accumulatedStats[type]) {
            accumulatedStats[type] = {
                total_synced: 0,
                total_failed: 0,
                total_api: null,
                progress: 0,
                errors: []
            };
        }
    };

    /**
     * Generic Sync Function
     */
    const syncData = async (type: string, offset = 0, idSemester?: string, syncSince?: string) => {
        if (!syncStates[type] || isStopping.value) return;

        // --- Step 1: Initialization & Get Count (Only on first call) ---
        if (offset === 0) {
            isStopping.value = false;
            syncStates[type].loading = true;
            syncStates[type].result = null;

            // Reset accumulator
            initAccumulator(type);

            try {
                const routeConfig = routeMapping[type];
                if (!routeConfig) throw new Error(`Unknown sync type: ${type}`);
                
                const endpointUrl = routeConfig.url;

                // Special handling for legacy 'referensi' sub-types sync
                if (type === 'referensi') {
                    // ... (keep existing referensi logic)
                     const subTypes = ['agama', 'jenis_tinggal', 'alat_transportasi', 'pekerjaan', 'penghasilan', 'kebutuhan_khusus', 'pembiayaan'];
                     let totalSynced = 0;
                     
                     for (let i = 0; i < subTypes.length; i++) {
                         if (isStopping.value) break;
                         const sub = subTypes[i];
                         const subProgress = Math.round(((i + 1) / subTypes.length) * 100);
                         
                         const res = await axios.post(route('admin.sync.referensi'), { 
                             sub_type: sub,
                             sync_since: null, // Force full sync for references
                             only_count: false 
                         });
                         
                         // Fix: Extract .data from response.data
                         const data = res.data.data || {};
                         totalSynced += data.synced || 0;
                             
                         syncStates[type].result = {
                             success: true,
                             message: `Syncing ${sub.replace('_', ' ')}...`,
                             synced: totalSynced,
                             progress: subProgress
                         };
                         
                         accumulatedStats[type].total_synced = totalSynced;
                         accumulatedStats[type].total_api = totalSynced; 
                         accumulatedStats[type].progress = subProgress;
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

                // --- Fetch Total Count First ---
                const params: any = { only_count: true };
                if (['wilayah'].includes(type)) params.type = 'wilayah';
                if (idSemester) params.id_semester = idSemester;
                if (syncSince) params.sync_since = syncSince;

                const countRes = await axios.post(route('admin.sync.' + endpointUrl), params);
                
                if (countRes.data.success) {
                    const totalCount = countRes.data.data.total || 0;
                    accumulatedStats[type].total_api = totalCount;
                    
                    syncStates[type].result = {
                        success: true,
                        message: 'Memulai sinkronisasi...',
                        synced: 0,
                        total_all: totalCount,
                        progress: 0
                    };
                    
                    if (totalCount === 0) {
                        console.warn("Count returned 0, but proceeding to fetch check.");
                    }
                }
            } catch (error: any) {
                console.error("Failed to get count:", error);
            }
        }

        // --- Step 2: Sync Data Batches ---
        try {
            const routeConfig = routeMapping[type];
            if (!routeConfig) throw new Error(`Unknown sync type: ${type}`);
            const endpointUrl = routeConfig.url;
            const limit = routeConfig.limit || 100;

            // Prepare params
            const params: any = { offset, limit: limit }; 
            
            // Adjust limits for heavy/light endpoints
            if (['wilayah'].includes(type)) {
                params.type = 'wilayah';
            }
            
            if (idSemester) params.id_semester = idSemester;
            // Force full sync for Wilayah to ensure data retrieval
            if (syncSince && type !== 'wilayah') params.sync_since = syncSince;

            const response = await axios.post(route('admin.sync.' + endpointUrl), params);

            if (response.data.success) {
                const result = response.data.data;
                const fullResult: SyncResult = {
                    success: true,
                    message: response.data.message || 'Sync successful',
                    ...result
                };
                
                syncStates[type].result = fullResult;

                // Update Accumulator
                const acc = accumulatedStats[type];
                
                acc.total_synced += (result.synced || 0) + (result.updated || 0) + (result.inserted || 0);
                acc.total_failed += (result.failed || 0);
                
                // If we got a total from API in this batch response, check if it's different/better than our initial count
                // But usually we prefer our initial "GetCount" if available.
                // However, sometimes GetCount fails and we rely on partials.
                // Keep the max seen total
                if (result.total_all > (acc.total_api ?? 0)) {
                    acc.total_api = result.total_all;
                }
                
                // Recalculate progress based on accumulated synced / total_api
                if (acc.total_api && acc.total_api > 0) {
                     acc.progress = Math.min(100, Math.round(((acc.total_synced + offset) / acc.total_api) * 100));
                     // Note: offset logic above is imperfect if standard batch size varies, 
                     // but commonly next_offset is used.
                     // Better: use result.progress if available, OR calc locally
                     // The backend returns progress based on offset / total. 
                     // Let's rely on backend progress if plausible, else valid local calc.
                     if (result.progress !== undefined) {
                         acc.progress = result.progress;
                     }
                }

                if (result.errors && result.errors.length > 0) {
                     acc.errors.push(...result.errors);
                }

                // Recursive call if has_more
                if (result.has_more && result.next_offset && !isStopping.value) {
                    await syncData(type, result.next_offset, idSemester, syncSince);
                } else {
                    syncStates[type].loading = false;
                    // Final update on completion
                    if (!isStopping.value) {
                        syncStates[type].result = {
                            ...syncStates[type].result!,
                            progress: 100,
                            message: 'Sinkronisasi Selesai',
                        };
                        accumulatedStats[type].progress = 100;
                    } else {
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
