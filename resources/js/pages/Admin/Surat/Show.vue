<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

interface Surat {
    id: number;
    mahasiswa: {
        id: number;
        nim: string;
        nama: string;
        ttl: string;
        prodi: string | null;
        angkatan: string | null;
        status: string | null;
    };
    jenis_surat: string;
    jenis_surat_label: string;
    keperluan: string | null;
    data_tambahan: Record<string, any> | null;
    status: string;
    status_label: string;
    catatan: string | null;
    processed_by: string | null;
    processed_at: string | null;
    created_at: string;
}

interface Pejabat {
    id: number;
    nama: string;
    jabatan: string;
    label: string;
}

const props = defineProps<{
    surat: Surat;
    pejabatList: Pejabat[];
}>();

const selectedSignerId = ref<number | null>(null);

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pengajuan Surat', href: '/admin/surat' },
    { title: 'Detail', href: '#' },
]);

const rejectForm = useForm({
    catatan: '',
});

const showRejectModal = ref(false);

const approve = () => {
    Swal.fire({
        title: 'Konfirmasi Persetujuan',
        text: 'Setujui pengajuan surat ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Setujui!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(`/admin/surat/${props.surat.id}/approve`);
        }
    });
};

const reject = () => {
    rejectForm.post(`/admin/surat/${props.surat.id}/reject`, {
        onSuccess: () => {
            showRejectModal.value = false;
        },
    });
};

const deleteSurat = () => {
    Swal.fire({
        title: 'Apakah anda yakin?',
        text: 'Hapus pengajuan ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/surat/${props.surat.id}`);
        }
    });
};

const statusConfig = computed(() => {
    const configs: Record<string, { bg: string; text: string; ring: string; dot: string; icon: string }> = {
        pending: {
            bg: 'bg-amber-50 dark:bg-amber-950/40',
            text: 'text-amber-700 dark:text-amber-400',
            ring: 'ring-amber-200 dark:ring-amber-800',
            dot: 'bg-amber-500',
            icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        approved: {
            bg: 'bg-emerald-50 dark:bg-emerald-950/40',
            text: 'text-emerald-700 dark:text-emerald-400',
            ring: 'ring-emerald-200 dark:ring-emerald-800',
            dot: 'bg-emerald-500',
            icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        rejected: {
            bg: 'bg-red-50 dark:bg-red-950/40',
            text: 'text-red-700 dark:text-red-400',
            ring: 'ring-red-200 dark:ring-red-800',
            dot: 'bg-red-500',
            icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        printed: {
            bg: 'bg-blue-50 dark:bg-blue-950/40',
            text: 'text-blue-700 dark:text-blue-400',
            ring: 'ring-blue-200 dark:ring-blue-800',
            dot: 'bg-blue-500',
            icon: 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z',
        },
    };
    return configs[props.surat.status] || configs.pending;
});

const initials = computed(() => {
    return props.surat.mahasiswa.nama
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0])
        .join('')
        .toUpperCase();
});
</script>

<template>
    <Head title="Detail Pengajuan" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4 md:p-6 max-w-6xl mx-auto w-full">
        <!-- Back Button -->
        <div>
            <Link
                href="/admin/surat"
                class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition-colors group"
            >
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke daftar
            </Link>
        </div>

        <!-- Top Section: Student Profile + Status -->
        <div class="rounded-2xl border bg-card overflow-hidden shadow-sm">
            <div class="p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <!-- Student Info -->
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold shadow-lg shadow-indigo-500/20 shrink-0">
                            {{ initials }}
                        </div>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight">{{ surat.mahasiswa.nama }}</h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="font-mono text-sm text-muted-foreground">{{ surat.mahasiswa.nim }}</span>
                                <span class="text-muted-foreground/40">•</span>
                                <span class="text-sm text-muted-foreground">{{ surat.mahasiswa.prodi || '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Badge -->
                    <div :class="[statusConfig.bg, statusConfig.text, statusConfig.ring]" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold ring-1 self-start">
                        <span :class="statusConfig.dot" class="w-2 h-2 rounded-full animate-pulse"></span>
                        {{ surat.status_label }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-5 gap-5">
            <!-- Left Column: Student Details (3 cols) -->
            <div class="lg:col-span-3 flex flex-col gap-5">
                <!-- Student Data Card -->
                <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-muted/30">
                        <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Data Mahasiswa</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div class="space-y-1">
                                <dt class="text-xs text-muted-foreground font-medium">NIM</dt>
                                <dd class="font-mono font-semibold text-base">{{ surat.mahasiswa.nim }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs text-muted-foreground font-medium">Nama Lengkap</dt>
                                <dd class="font-semibold text-base">{{ surat.mahasiswa.nama }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs text-muted-foreground font-medium">Tempat, Tgl Lahir</dt>
                                <dd>{{ surat.mahasiswa.ttl || '-' }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs text-muted-foreground font-medium">Program Studi</dt>
                                <dd>{{ surat.mahasiswa.prodi || '-' }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs text-muted-foreground font-medium">Angkatan</dt>
                                <dd>{{ surat.mahasiswa.angkatan || '-' }}</dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-xs text-muted-foreground font-medium">Status Akademik</dt>
                                <dd>
                                    <span v-if="surat.mahasiswa.status === 'A'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                    <span v-else class="text-muted-foreground">{{ surat.mahasiswa.status || '-' }}</span>
                                </dd>
                            </div>
                        </div>
                        <div class="mt-5 pt-4 border-t">
                            <Link
                                :href="`/admin/mahasiswa/${surat.mahasiswa.id}`"
                                class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition group"
                            >
                                Lihat profil lengkap
                                <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Catatan Admin (Conditionally shown) -->
                <div v-if="surat.catatan" class="rounded-2xl border border-amber-200 dark:border-amber-800/50 bg-amber-50/80 dark:bg-amber-950/30 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-amber-200 dark:border-amber-800/50 bg-amber-100/50 dark:bg-amber-900/20">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-400 uppercase tracking-wider">Catatan Penolakan</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-sm text-amber-800 dark:text-amber-300 leading-relaxed">{{ surat.catatan }}</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Pengajuan Info + Actions (2 cols) -->
            <div class="lg:col-span-2 flex flex-col gap-5">
                <!-- Info Pengajuan Card -->
                <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-muted/30">
                        <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Info Pengajuan</h3>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="space-y-1">
                            <dt class="text-xs text-muted-foreground font-medium">Jenis Surat</dt>
                            <dd class="font-semibold">{{ surat.jenis_surat_label }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs text-muted-foreground font-medium">Keperluan</dt>
                            <dd>{{ surat.keperluan || '-' }}</dd>
                        </div>

                        <div class="border-t pt-4 space-y-1">
                            <dt class="text-xs text-muted-foreground font-medium">Tanggal Diajukan</dt>
                            <dd class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ surat.created_at }}
                            </dd>
                        </div>

                        <div v-if="surat.processed_by" class="border-t pt-4 space-y-1">
                            <dt class="text-xs text-muted-foreground font-medium">Diproses Oleh</dt>
                            <dd class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ surat.processed_by }}
                            </dd>
                            <dd class="text-xs text-muted-foreground ml-6">{{ surat.processed_at }}</dd>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="rounded-2xl border bg-card shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-muted/30">
                        <h3 class="text-sm font-semibold text-muted-foreground uppercase tracking-wider">Aksi</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <!-- Pending Actions -->
                        <template v-if="surat.status === 'pending'">
                            <button
                                @click="approve"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl transition-all active:scale-[0.98]"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Setujui Pengajuan
                            </button>
                            <button
                                @click="showRejectModal = true"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 border-2 border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 font-semibold rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition-all active:scale-[0.98]"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Tolak Pengajuan
                            </button>
                        </template>

                        <!-- Approved/Printed Actions -->
                        <template v-if="surat.status === 'approved' || surat.status === 'printed'">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-xs text-muted-foreground font-medium mb-1.5 block">Pejabat Penandatangan</label>
                                    <select
                                        v-model="selectedSignerId"
                                        class="w-full px-3 py-2.5 border rounded-xl text-sm bg-background focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                                    >
                                        <option :value="null">-- Pilih Pejabat --</option>
                                        <option v-for="p in pejabatList" :key="p.id" :value="p.id">
                                            {{ p.label }}
                                        </option>
                                    </select>
                                </div>
                                <a
                                    :href="`/admin/surat/${surat.id}/print` + (selectedSignerId ? `?signer_id=${selectedSignerId}` : '')"
                                    target="_blank"
                                    class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all active:scale-[0.98]"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Cetak Surat
                                </a>
                            </div>
                        </template>

                        <!-- Divider -->
                        <div class="border-t my-1"></div>

                        <!-- Delete -->
                        <button
                            @click="deleteSurat"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition font-medium"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus Pengajuan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                <div class="bg-card rounded-2xl w-full max-w-md shadow-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b bg-red-50 dark:bg-red-950/30">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/40 rounded-xl flex items-center justify-center text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Tolak Pengajuan</h3>
                                <p class="text-sm text-muted-foreground">Berikan alasan penolakan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="mb-5">
                            <label class="block text-sm font-medium mb-1.5">Catatan (opsional)</label>
                            <textarea
                                v-model="rejectForm.catatan"
                                rows="4"
                                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none text-sm"
                                placeholder="Tuliskan alasan penolakan pengajuan ini..."
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                @click="showRejectModal = false"
                                class="px-5 py-2.5 border rounded-xl hover:bg-muted transition font-medium text-sm"
                            >
                                Batal
                            </button>
                            <button
                                @click="reject"
                                :disabled="rejectForm.processing"
                                class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:opacity-50 transition font-semibold text-sm"
                            >
                                <span v-if="rejectForm.processing">Memproses...</span>
                                <span v-else>Tolak Pengajuan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
