<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import LandingLayout from '@/layouts/LandingLayout.vue';
import { useStatusBadge } from '@/composables/useStatusBadge';

interface Mahasiswa {
    id: number;
    nim: string;
    nama: string;
}

interface Pengajuan {
    id: number;
    jenis_surat: string;
    keperluan: string;
    status: string;
    status_label: string;
    status_badge: string;
    catatan: string | null;
    created_at: string;
    processed_at: string | null;
}

defineProps<{
    mahasiswa: Mahasiswa;
    pengajuan: Pengajuan[];
}>();

const { getBadgeClass } = useStatusBadge();
</script>

<template>
    <Head title="Status Pengajuan" />

    <LandingLayout variant="simple">
        <div class="max-w-4xl mx-auto py-12 px-4">
            <!-- Back Button -->
            <Link href="/" class="inline-flex items-center text-slate-500 hover:text-blue-600 mb-8 transition font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Home
            </Link>

            <div class="bg-white rounded-2xl p-8 border border-slate-200 shadow-xl">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 mb-2">Status Pengajuan Surat</h2>
                        <p class="text-slate-500">
                             Mahasiswa: <span class="font-semibold text-slate-700">{{ mahasiswa.nama }}</span> <span class="text-slate-300">|</span> NIM: <span class="font-semibold text-slate-700">{{ mahasiswa.nim }}</span>
                        </p>
                    </div>
                </div>

                <div v-if="pengajuan.length === 0" class="text-center py-16 bg-slate-50 rounded-xl border border-slate-100 border-dashed">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                         <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-slate-500 text-lg font-medium">Belum ada riwayat pengajuan</p>
                    <p class="text-slate-400 text-sm">Silakan ajukan surat baru untuk memulai.</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="item in pengajuan"
                        :key="item.id"
                        class="bg-white rounded-xl p-6 border border-slate-200 hover:border-blue-300 transition shadow-sm group"
                    >
                        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 group-hover:text-blue-600 transition">{{ item.jenis_surat }}</h3>
                                <p class="text-slate-500 text-sm">Keperluan: {{ item.keperluan }}</p>
                            </div>
                            <span
                                :class="getBadgeClass(item.status_badge)"
                                class="px-3 py-1 rounded-full text-xs font-semibold border flex items-center gap-1.5 shrink-0"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ item.status_label }}
                            </span>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 text-sm border-t border-slate-100 pt-4 mt-4">
                            <div>
                                <span class="text-slate-400 block text-xs uppercase tracking-wider font-semibold mb-1">Tanggal Pengajuan</span>
                                <span class="text-slate-700 font-medium">{{ item.created_at }}</span>
                            </div>
                            <div v-if="item.processed_at">
                                <span class="text-slate-400 block text-xs uppercase tracking-wider font-semibold mb-1">Tanggal Diproses</span>
                                <span class="text-slate-700 font-medium">{{ item.processed_at }}</span>
                            </div>
                        </div>

                        <div v-if="item.catatan" class="mt-4 bg-slate-50 rounded-lg p-3 border border-slate-100">
                            <p class="text-slate-400 text-xs font-semibold uppercase mb-1">Catatan Admin:</p>
                            <p class="text-slate-600 text-sm">{{ item.catatan }}</p>
                        </div>

                        <div v-if="item.status === 'approved'" class="mt-4 bg-emerald-50 rounded-lg p-3 border border-emerald-100 flex gap-3 text-sm text-emerald-700">
                            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Surat sudah disetujui. Silakan ambil dokumen fisik di kantor BAAK.</span>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100">
                    <Link
                        :href="`/pengajuan/${mahasiswa.id}`"
                        class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Ajukan Surat Baru
                    </Link>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>
