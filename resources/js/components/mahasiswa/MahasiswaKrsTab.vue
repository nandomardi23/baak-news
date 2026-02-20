<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface KrsDetail {
    kode: string;
    nama: string;
    sks: number;
    kelas: string;
    dosen_pengajar: string[] | null;
    nama_dosen: string | null;
}

interface Krs {
    id: number;
    tahun_akademik_id: number;
    semester: string;
    total_sks: number;
    details: KrsDetail[];
}

const props = defineProps<{
    krs: Krs[];
    mahasiswaId: number;
}>();

const isSyncingKrs = ref(false);
const syncKrs = () => {
    if (!confirm('Update Data KRS dari Neo Feeder?')) return;
    
    isSyncingKrs.value = true;
    router.post(`/admin/mahasiswa/${props.mahasiswaId}/sync-krs`, {}, {
        preserveScroll: true,
        onFinish: () => {
            isSyncingKrs.value = false;
        },
    });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-end gap-4">
            <button
                @click="syncKrs"
                :disabled="isSyncingKrs"
                class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 disabled:opacity-50 flex items-center gap-2"
            >
                <svg v-if="isSyncingKrs" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSyncingKrs ? 'Syncing...' : 'Sync Data KRS' }}
            </button>
        </div>

        <div v-for="krsItem in krs" :key="krsItem.id" class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <div class="p-4 bg-muted/50 border-b flex justify-between items-center">
                <div>
                    <h3 class="font-semibold">{{ krsItem.semester }}</h3>
                    <span class="text-sm text-muted-foreground">Total: {{ krsItem.total_sks }} SKS</span>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="`/admin/mahasiswa/${mahasiswaId}/krs/${krsItem.tahun_akademik_id}/print`"
                        target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak KRS
                    </a>
                    <a
                        :href="`/admin/mahasiswa/${mahasiswaId}/kartu-ujian/${krsItem.tahun_akademik_id}/print`"
                        target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded hover:bg-amber-700 transition"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Kartu Ujian
                    </a>
                </div>
            </div>
            <table class="w-full">
                <thead class="bg-muted/30">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">No</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Kode</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Mata Kuliah</th>
                        <th class="px-4 py-2 text-center text-sm font-medium">SKS</th>
                        <th class="px-4 py-2 text-center text-sm font-medium">Kelas</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Dosen Pengajar</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="(detail, idx) in krsItem.details" :key="idx">
                        <td class="px-4 py-2 text-sm">{{ idx + 1 }}</td>
                        <td class="px-4 py-2 text-sm font-mono">{{ detail.kode }}</td>
                        <td class="px-4 py-2 text-sm">{{ detail.nama }}</td>
                        <td class="px-4 py-2 text-sm text-center">{{ detail.sks }}</td>
                        <td class="px-4 py-2 text-sm text-center">{{ detail.kelas }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">
                            <div v-if="detail.dosen_pengajar && detail.dosen_pengajar.length > 0" class="flex flex-col gap-0.5">
                                <span v-for="(dsn, i) in detail.dosen_pengajar" :key="i" class="text-xs">
                                    {{ dsn }}
                                </span>
                            </div>
                            <span v-else>{{ detail.nama_dosen || '-' }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="krs.length === 0" class="text-center py-12 text-muted-foreground">
            Tidak ada data KRS
        </div>
    </div>
</template>
