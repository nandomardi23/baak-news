<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Mahasiswa {
    nim: string;
    nama: string;
    prodi: string | null;
}

interface Pejabat {
    id: number;
    nama: string;
    jabatan: string;
}

interface Pengajuan {
    id: number;
    nomor_surat: string | null;
    mahasiswa: Mahasiswa;
    pejabat: Pejabat | null;
    jenis_surat: string;
    jenis_surat_label: string;
    keperluan: string | null;
    status: string;
    status_label: string;
    status_badge: string;
    processed_by: string | null;
    processed_at: string | null;
    created_at: string;
}

interface Pagination {
    data: Pengajuan[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    pengajuan: Pagination;
    filters: {
        status?: string;
        jenis?: string;
        search?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Pengajuan Surat', href: '/admin/surat' },
];

const search = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedJenis = ref(props.filters.jenis || '');

const applyFilters = () => {
    router.get('/admin/surat', {
        search: search.value || undefined,
        status: selectedStatus.value || undefined,
        jenis: selectedJenis.value || undefined,
    }, { preserveState: true });
};

const getBadgeClass = (badge: string) => {
    const classes: Record<string, string> = {
        warning: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        success: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        danger: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    };
    return classes[badge] || 'bg-gray-100 text-gray-800';
};

const deleteSurat = (id: number) => {
    if (confirm('Hapus pengajuan surat ini?')) {
        router.delete(`/admin/surat/${id}`);
    }
};
</script>

<template>
    <Head title="Pengajuan Surat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold">Pengajuan Surat</h1>
                <p class="text-muted-foreground">Total: {{ pengajuan.total }} pengajuan</p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium mb-1">Cari</label>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Nama atau NIM..."
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                        @keyup.enter="applyFilters"
                    />
                </div>
                <div class="w-40">
                    <label class="block text-sm font-medium mb-1">Jenis Surat</label>
                    <select
                        v-model="selectedJenis"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">Semua</option>
                        <option value="aktif_kuliah">Surat Aktif Kuliah</option>
                        <option value="krs">KRS</option>
                        <option value="khs">KHS</option>
                        <option value="transkrip">Transkrip</option>
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select
                        v-model="selectedStatus"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                        <option value="">Semua</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="printed">Printed</option>
                    </select>
                </div>
                <button
                    @click="applyFilters"
                    class="px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90"
                >
                    Filter
                </button>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Nomor</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Mahasiswa</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Jenis</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Pejabat</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Tanggal</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="item in pengajuan.data" :key="item.id" class="hover:bg-muted/50">
                                <td class="px-4 py-4 font-mono text-sm">{{ item.nomor_surat || '-' }}</td>
                                <td class="px-4 py-4">
                                    <div>
                                        <p class="font-medium">{{ item.mahasiswa.nama }}</p>
                                        <p class="text-sm text-muted-foreground">{{ item.mahasiswa.nim }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm">{{ item.jenis_surat_label }}</td>
                                <td class="px-4 py-4">
                                    <div v-if="item.pejabat">
                                        <p class="text-sm font-medium">{{ item.pejabat.nama }}</p>
                                        <p class="text-xs text-muted-foreground">{{ item.pejabat.jabatan }}</p>
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground">-</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span :class="getBadgeClass(item.status_badge)" class="px-2 py-1 rounded-full text-xs font-medium">
                                        {{ item.status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-muted-foreground">{{ item.created_at }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-1">
                                        <!-- Detail -->
                                        <Link
                                            :href="`/admin/surat/${item.id}`"
                                            class="p-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition"
                                            title="Detail"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <!-- Print (only if approved/printed) -->
                                        <a
                                            v-if="item.status === 'approved' || item.status === 'printed'"
                                            :href="`/admin/surat/${item.id}/print`"
                                            target="_blank"
                                            class="p-2 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 transition"
                                            title="Cetak"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                            </svg>
                                        </a>
                                        <!-- Delete -->
                                        <button
                                            @click="deleteSurat(item.id)"
                                            class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition"
                                            title="Hapus"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="pengajuan.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    Tidak ada pengajuan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pengajuan.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman {{ pengajuan.current_page }} dari {{ pengajuan.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in pengajuan.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded text-sm',
                                link.active ? 'bg-primary text-primary-foreground' : 'border hover:bg-muted',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
