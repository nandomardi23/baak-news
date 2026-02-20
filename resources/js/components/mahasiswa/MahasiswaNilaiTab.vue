<script setup lang="ts">
interface NilaiItem {
    kode: string;
    nama: string;
    sks: number;
    nilai_huruf: string;
    nilai_angka: number;
    nilai_indeks: number;
}

interface NilaiGroup {
    tahun_akademik_id: number;
    semester: string;
    list: NilaiItem[];
}

defineProps<{
    nilai: NilaiGroup[];
    mahasiswaId: number;
}>();
</script>

<template>
    <div class="space-y-6">
        <div v-for="nilaiGroup in nilai" :key="nilaiGroup.semester" class="rounded-xl border bg-card shadow-sm overflow-hidden">
            <div class="p-4 bg-muted/50 border-b flex justify-between items-center">
                <h3 class="font-semibold">{{ nilaiGroup.semester }}</h3>
                <a
                    :href="`/admin/mahasiswa/${mahasiswaId}/khs/${nilaiGroup.tahun_akademik_id}/print`"
                    target="_blank"
                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 transition"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak KHS
                </a>
            </div>
            <table class="w-full">
                <thead class="bg-muted/30">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">No</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Kode</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Mata Kuliah</th>
                        <th class="px-4 py-2 text-center text-sm font-medium">SKS</th>
                        <th class="px-4 py-2 text-center text-sm font-medium">Nilai</th>
                        <th class="px-4 py-2 text-center text-sm font-medium">Indeks</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr v-for="(item, idx) in nilaiGroup.list" :key="idx">
                        <td class="px-4 py-2 text-sm">{{ idx + 1 }}</td>
                        <td class="px-4 py-2 text-sm font-mono">{{ item.kode }}</td>
                        <td class="px-4 py-2 text-sm">{{ item.nama }}</td>
                        <td class="px-4 py-2 text-sm text-center">{{ item.sks }}</td>
                        <td class="px-4 py-2 text-sm text-center font-semibold">{{ item.nilai_huruf }}</td>
                        <td class="px-4 py-2 text-sm text-center">{{ item.nilai_indeks !== null ? Number(item.nilai_indeks).toFixed(2) : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="nilai.length === 0" class="text-center py-12 text-muted-foreground">
            Tidak ada data nilai
        </div>
    </div>
</template>
