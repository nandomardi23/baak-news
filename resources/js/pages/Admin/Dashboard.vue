<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Stats {
    total_mahasiswa: number;
    mahasiswa_aktif: number;
    total_dosen: number;
    dosen_aktif: number;
    total_prodi: number;
    pengajuan_pending: number;
    pengajuan_hari_ini: number;
    pengajuan_bulan_ini: number;
}

interface Pengajuan {
    id: number;
    mahasiswa: {
        nim: string;
        nama: string;
    };
    jenis_surat: string;
    status: string;
    status_label: string;
    status_badge: string;
    created_at: string;
}

interface ProdiStat {
    nama: string;
    total: number;
}

interface AngkatanStat {
    angkatan: string;
    total: number;
}

interface IpkStat {
    range: string;
    total: number;
}

interface MonthlyPengajuan {
    bulan: string;
    total: number;
}

const props = defineProps<{
    stats: Stats;
    mahasiswaPerProdi: ProdiStat[];
    suratPerStatus: Record<string, number>;
    recentPengajuan: Pengajuan[];
    pengajuanPerJenis: Record<string, number>;
    mahasiswaPerAngkatan: AngkatanStat[];
    ipkDistribution: IpkStat[];
    monthlyPengajuan: MonthlyPengajuan[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
];

const getBadgeClass = (badge: string) => {
    const classes: Record<string, string> = {
        warning: 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400',
        success: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400',
        danger: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        info: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    };
    return classes[badge] || 'bg-gray-100 text-gray-800';
};

const statusLabels: Record<string, { label: string; class: string }> = {
    pending: { label: 'Menunggu', class: 'bg-amber-500' },
    processing: { label: 'Diproses', class: 'bg-blue-500' },
    approved: { label: 'Disetujui', class: 'bg-emerald-500' },
    rejected: { label: 'Ditolak', class: 'bg-red-500' },
    printed: { label: 'Dicetak', class: 'bg-purple-500' },
};

const totalSurat = computed(() => {
    return Object.values(props.suratPerStatus).reduce((a, b) => a + b, 0);
});

const maxProdiTotal = computed(() => {
    return Math.max(...props.mahasiswaPerProdi.map(p => p.total), 1);
});

const maxAngkatanTotal = computed(() => {
    return Math.max(...props.mahasiswaPerAngkatan.map(a => a.total), 1);
});

const maxIpkTotal = computed(() => {
    return Math.max(...props.ipkDistribution.map(i => i.total), 1);
});

const maxMonthlyTotal = computed(() => {
    return Math.max(...props.monthlyPengajuan.map(m => m.total), 1);
});

const ipkColors: Record<string, string> = {
    '3.50 - 4.00': 'from-emerald-500 to-green-500',
    '3.00 - 3.49': 'from-blue-500 to-cyan-500',
    '2.50 - 2.99': 'from-amber-500 to-yellow-500',
    '2.00 - 2.49': 'from-orange-500 to-red-400',
    '< 2.00': 'from-red-500 to-pink-500',
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Stats Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Mahasiswa</p>
                            <p class="text-3xl font-bold">{{ stats.total_mahasiswa }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m9 5.197v1" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-muted-foreground mt-2">{{ stats.mahasiswa_aktif }} aktif</p>
                </div>

                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Dosen</p>
                            <p class="text-3xl font-bold text-indigo-600">{{ stats.total_dosen }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-muted-foreground mt-2">{{ stats.dosen_aktif }} aktif</p>
                </div>

                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Pengajuan Pending</p>
                            <p class="text-3xl font-bold text-amber-600">{{ stats.pengajuan_pending }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-muted-foreground mt-2">Menunggu diproses</p>
                </div>

                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Bulan Ini</p>
                            <p class="text-3xl font-bold text-emerald-600">{{ stats.pengajuan_bulan_ini }}</p>
                        </div>
                        <div class="h-12 w-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-muted-foreground mt-2">{{ stats.pengajuan_hari_ini }} hari ini</p>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Mahasiswa per Prodi -->
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Mahasiswa per Program Studi</h3>
                    <div class="space-y-3">
                        <div v-for="prodi in mahasiswaPerProdi.slice(0, 6)" :key="prodi.nama" class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="truncate max-w-[200px]">{{ prodi.nama }}</span>
                                <span class="font-medium">{{ prodi.total }}</span>
                            </div>
                            <div class="h-2 bg-muted rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all"
                                    :style="{ width: `${(prodi.total / maxProdiTotal) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Surat per Status -->
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Status Pengajuan Surat</h3>
                    <div class="flex items-center justify-center mb-4">
                        <div class="text-center">
                            <p class="text-4xl font-bold">{{ totalSurat }}</p>
                            <p class="text-sm text-muted-foreground">Total Surat</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="(count, status) in suratPerStatus" :key="status" 
                            class="flex items-center gap-2 p-2 rounded-lg bg-muted/50">
                            <div :class="[statusLabels[status]?.class || 'bg-gray-500', 'w-3 h-3 rounded-full']" />
                            <div class="flex-1">
                                <p class="text-sm font-medium">{{ statusLabels[status]?.label || status }}</p>
                                <p class="text-lg font-bold">{{ count }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- New Charts Row: Angkatan & IPK -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Mahasiswa per Angkatan -->
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Mahasiswa per Angkatan</h3>
                    <div class="space-y-3">
                        <div v-for="item in mahasiswaPerAngkatan" :key="item.angkatan" class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium">{{ item.angkatan }}</span>
                                <span class="font-bold">{{ item.total }}</span>
                            </div>
                            <div class="h-3 bg-muted rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full transition-all"
                                    :style="{ width: `${(item.total / maxAngkatanTotal) * 100}%` }"
                                />
                            </div>
                        </div>
                        <p v-if="mahasiswaPerAngkatan.length === 0" class="text-center text-muted-foreground py-4">Belum ada data</p>
                    </div>
                </div>

                <!-- IPK Distribution -->
                <div class="rounded-xl border bg-card p-6 shadow-sm">
                    <h3 class="text-lg font-semibold mb-4">Distribusi IPK Mahasiswa</h3>
                    <div class="space-y-3">
                        <div v-for="item in ipkDistribution" :key="item.range" class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium">IPK {{ item.range }}</span>
                                <span class="font-bold">{{ item.total }} mahasiswa</span>
                            </div>
                            <div class="h-3 bg-muted rounded-full overflow-hidden">
                                <div 
                                    :class="['h-full bg-gradient-to-r rounded-full transition-all', ipkColors[item.range] || 'from-gray-500 to-gray-400']"
                                    :style="{ width: `${(item.total / maxIpkTotal) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend Chart -->
            <div class="rounded-xl border bg-card p-6 shadow-sm">
                <h3 class="text-lg font-semibold mb-4">📈 Tren Pengajuan Surat (12 Bulan Terakhir)</h3>
                <div class="flex items-end gap-2 h-48 overflow-x-auto pb-2">
                    <div
                        v-for="item in monthlyPengajuan"
                        :key="item.bulan"
                        class="flex flex-col items-center gap-1 min-w-[50px]"
                    >
                        <span class="text-xs font-medium">{{ item.total }}</span>
                        <div
                            class="w-8 bg-gradient-to-t from-blue-600 to-indigo-400 rounded-t transition-all"
                            :style="{ height: `${Math.max((item.total / maxMonthlyTotal) * 140, 4)}px` }"
                        ></div>
                        <span class="text-xs text-muted-foreground text-center whitespace-nowrap">
                            {{ item.bulan.split(' ')[0] }}
                        </span>
                    </div>
                </div>
                <p class="text-sm text-muted-foreground mt-2 text-center">
                    Total: {{ monthlyPengajuan.reduce((a, b) => a + b.total, 0) }} pengajuan dalam 12 bulan terakhir
                </p>
            </div>

            <!-- Quick Links -->
            <div class="grid gap-4 md:grid-cols-3">
                <Link
                    href="/admin/surat?status=pending"
                    class="rounded-xl border bg-card p-6 shadow-sm hover:shadow-md transition group"
                >
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center group-hover:bg-amber-200 dark:group-hover:bg-amber-900/50 transition">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Proses Pengajuan</h3>
                            <p class="text-sm text-muted-foreground">{{ stats.pengajuan_pending }} menunggu</p>
                        </div>
                    </div>
                </Link>

                <Link
                    href="/admin/mahasiswa"
                    class="rounded-xl border bg-card p-6 shadow-sm hover:shadow-md transition group"
                >
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Data Mahasiswa</h3>
                            <p class="text-sm text-muted-foreground">Lihat semua data</p>
                        </div>
                    </div>
                </Link>

                <Link
                    href="/admin/pejabat"
                    class="rounded-xl border bg-card p-6 shadow-sm hover:shadow-md transition group"
                >
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center group-hover:bg-emerald-200 dark:group-hover:bg-emerald-900/50 transition">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Data Pejabat</h3>
                            <p class="text-sm text-muted-foreground">Kelola pejabat</p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Recent Pengajuan -->
            <div class="rounded-xl border bg-card shadow-sm">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Pengajuan Terbaru</h2>
                        <Link href="/admin/surat" class="text-sm text-primary hover:underline">
                            Lihat Semua
                        </Link>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Mahasiswa</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Jenis Surat</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Tanggal</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="item in recentPengajuan" :key="item.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium">{{ item.mahasiswa.nama }}</p>
                                        <p class="text-sm text-muted-foreground">{{ item.mahasiswa.nim }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ item.jenis_surat }}</td>
                                <td class="px-6 py-4">
                                    <span :class="getBadgeClass(item.status_badge)" class="px-2 py-1 rounded-full text-xs font-medium">
                                        {{ item.status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground">{{ item.created_at }}</td>
                                <td class="px-6 py-4">
                                    <Link :href="`/admin/surat/${item.id}`" class="text-primary hover:underline text-sm">
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="recentPengajuan.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada pengajuan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
