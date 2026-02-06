<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pengajuan Surat', href: '/admin/surat' },
    { title: 'Detail', href: '#' },
];

const rejectForm = useForm({
    catatan: '',
});

const showRejectModal = ref(false);

const approve = () => {
    if (confirm('Setujui pengajuan surat ini?')) {
        router.post(`/admin/surat/${props.surat.id}/approve`);
    }
};

const reject = () => {
    rejectForm.post(`/admin/surat/${props.surat.id}/reject`, {
        onSuccess: () => {
            showRejectModal.value = false;
        },
    });
};

const deleteSurat = () => {
    if (confirm('Hapus pengajuan ini?')) {
        router.delete(`/admin/surat/${props.surat.id}`);
    }
};

const getBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        approved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        printed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Detail Pengajuan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Hero Header -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 p-8 text-white shadow-xl">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48Y2lyY2xlIGN4PSIzMCIgY3k9IjMwIiByPSIyIi8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold tracking-tight">Detail Pengajuan Surat</h1>
                                <p class="text-white/70 text-sm">{{ surat.jenis_surat_label }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-4">
                            <div class="flex items-center gap-2 text-sm text-white/80">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ surat.mahasiswa.nama }}
                            </div>
                            <div class="text-white/40">•</div>
                            <div class="flex items-center gap-2 text-sm text-white/80 font-mono">
                                {{ surat.mahasiswa.nim }}
                            </div>
                        </div>
                    </div>
                    <span :class="getBadgeClass(surat.status)" class="px-4 py-2 rounded-full text-sm font-bold shadow-lg backdrop-blur-sm">
                        {{ surat.status_label }}
                    </span>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Data Mahasiswa (Takes 2 cols) -->
                <div class="lg:col-span-2 rounded-2xl border bg-card p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold">Data Mahasiswa</h3>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-y-4 gap-x-8">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">NIM</dt>
                            <dd class="font-mono text-lg font-bold">{{ surat.mahasiswa.nim }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Nama Lengkap</dt>
                            <dd class="font-semibold text-lg">{{ surat.mahasiswa.nama }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Tempat, Tgl Lahir</dt>
                            <dd>{{ surat.mahasiswa.ttl || '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Program Studi</dt>
                            <dd>{{ surat.mahasiswa.prodi || '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Angkatan</dt>
                            <dd>{{ surat.mahasiswa.angkatan || '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Status Akademik</dt>
                            <dd>
                                <span v-if="surat.mahasiswa.status === 'A'" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Aktif
                                </span>
                                <span v-else class="text-muted-foreground">{{ surat.mahasiswa.status || '-' }}</span>
                            </dd>
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t">
                        <Link
                            :href="`/admin/mahasiswa/${surat.mahasiswa.id}`"
                            class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition group"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat Detail Mahasiswa
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </Link>
                    </div>
                </div>

                <!-- Data Pengajuan -->
                <div class="rounded-2xl border bg-card p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center text-purple-600 dark:text-purple-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold">Info Pengajuan</h3>
                    </div>
                    <dl class="space-y-4">
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Jenis Surat</dt>
                            <dd class="font-semibold">{{ surat.jenis_surat_label }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Keperluan</dt>
                            <dd>{{ surat.keperluan || '-' }}</dd>
                        </div>
                        <div class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Tanggal Diajukan</dt>
                            <dd class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ surat.created_at }}
                            </dd>
                        </div>
                        <div v-if="surat.processed_by" class="space-y-1">
                            <dt class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Diproses Oleh</dt>
                            <dd class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ surat.processed_by }}
                            </dd>
                            <dd class="text-sm text-muted-foreground">{{ surat.processed_at }}</dd>
                        </div>
                    </dl>

                    <div v-if="surat.catatan" class="mt-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-700/50">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-400">Catatan Admin</p>
                        </div>
                        <p class="text-sm text-amber-700 dark:text-amber-300">{{ surat.catatan }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions Panel -->
            <div class="rounded-2xl border bg-card p-6 shadow-sm">
                <h3 class="text-sm font-bold text-muted-foreground uppercase tracking-wider mb-4">Aksi</h3>
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Pending Actions -->
                    <template v-if="surat.status === 'pending'">
                        <button
                            @click="approve"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-700 transition shadow-lg shadow-emerald-500/25 transform hover:-translate-y-0.5"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Setujui Pengajuan
                        </button>
                        <button
                            @click="showRejectModal = true"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white font-semibold rounded-xl hover:from-red-600 hover:to-rose-700 transition shadow-lg shadow-red-500/25 transform hover:-translate-y-0.5"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Tolak
                        </button>
                    </template>

                    <!-- Approved/Printed Actions -->
                    <template v-if="surat.status === 'approved' || surat.status === 'printed'">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-slate-100 dark:bg-slate-800/50 rounded-xl p-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-muted-foreground shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <select
                                    v-model="selectedSignerId"
                                    class="px-3 py-2 border rounded-lg text-sm bg-background focus:ring-2 focus:ring-blue-500 focus:border-transparent min-w-[200px]"
                                >
                                    <option :value="null">-- Pilih Pejabat Penandatangan --</option>
                                    <option v-for="p in pejabatList" :key="p.id" :value="p.id">
                                        {{ p.label }}
                                    </option>
                                </select>
                            </div>
                            <a
                                :href="`/admin/surat/${surat.id}/print` + (selectedSignerId ? `?signer_id=${selectedSignerId}` : '')"
                                target="_blank"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition shadow-lg shadow-blue-500/25 transform hover:-translate-y-0.5"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Cetak Surat
                            </a>
                        </div>
                    </template>

                    <div class="flex-1"></div>

                    <button
                        @click="deleteSurat"
                        class="inline-flex items-center gap-2 px-5 py-2.5 border-2 border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 font-medium rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus
                    </button>

                    <Link 
                        href="/admin/surat" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 border-2 rounded-xl hover:bg-muted transition font-medium"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali
                    </Link>
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
                <div v-if="showRejectModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div class="bg-card rounded-2xl p-6 w-full max-w-md shadow-2xl transform">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center text-red-600 dark:text-red-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Tolak Pengajuan</h3>
                                <p class="text-sm text-muted-foreground">Berikan alasan penolakan</p>
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-semibold mb-2">Catatan (opsional)</label>
                            <textarea
                                v-model="rejectForm.catatan"
                                rows="4"
                                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
                                placeholder="Tuliskan alasan penolakan pengajuan ini..."
                            ></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button
                                @click="showRejectModal = false"
                                class="px-5 py-2.5 border-2 rounded-xl hover:bg-muted transition font-medium"
                            >
                                Batal
                            </button>
                            <button
                                @click="reject"
                                :disabled="rejectForm.processing"
                                class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:opacity-50 transition font-semibold"
                            >
                                <span v-if="rejectForm.processing">Memproses...</span>
                                <span v-else>Tolak Pengajuan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
