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
    <Head title="Dashboard Overview" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="relative min-h-screen">
            <!-- Decorative Background -->
            <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-blue-50/50 to-transparent -z-10"></div>
            <div class="absolute -top-10 left-10 w-64 h-64 bg-blue-400/10 rounded-full blur-3xl -z-10"></div>
            <div class="absolute top-10 right-10 w-64 h-64 bg-indigo-400/10 rounded-full blur-3xl -z-10"></div>

            <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full">
                
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Dashboard</h1>
                        <p class="text-slate-500 mt-1">Overview statistik dan aktivitas terkini.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Mahasiswa -->
                    <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 transition-all hover:shadow-[0_8px_30px_-4px_rgba(6,81,237,0.1)] hover:-translate-y-1">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Mahasiswa</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-bold text-slate-900">{{ stats.total_mahasiswa }}</span>
                                </div>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center transition-colors group-hover:bg-blue-600 group-hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m9 5.197v1" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                                {{ stats.mahasiswa_aktif }}
                            </span>
                            <span class="text-xs text-slate-400">status aktif</span>
                        </div>
                    </div>

                    <!-- Total Dosen -->
                    <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 transition-all hover:shadow-[0_8px_30px_-4px_rgba(6,81,237,0.1)] hover:-translate-y-1">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Dosen</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-bold text-slate-900">{{ stats.total_dosen }}</span>
                                </div>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                {{ stats.dosen_aktif }}
                            </span>
                            <span class="text-xs text-slate-400">dosen aktif</span>
                        </div>
                    </div>

                    <!-- Pengajuan This Month -->
                    <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 transition-all hover:shadow-[0_8px_30px_-4px_rgba(6,81,237,0.1)] hover:-translate-y-1">
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Pengajuan Bulan Ini</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-bold text-slate-900">{{ stats.pengajuan_bulan_ini }}</span>
                                </div>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center transition-colors group-hover:bg-purple-600 group-hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-full flex items-center gap-1">
                                +{{ stats.pengajuan_hari_ini }}
                            </span>
                            <span class="text-xs text-slate-400">hari ini</span>
                        </div>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="group relative overflow-hidden rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 transition-all hover:shadow-[0_8px_30px_-4px_rgba(6,81,237,0.1)] hover:-translate-y-1">
                        <div class="absolute -right-6 -top-6 w-20 h-20 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                        <div class="flex items-center justify-between relative z-10">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Menunggu Proses</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-bold text-slate-900">{{ stats.pengajuan_pending }}</span>
                                </div>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center transition-colors group-hover:bg-amber-500 group-hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2 relative z-10">
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                Action Needed
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Main Grid Section -->
                <div class="grid gap-8 lg:grid-cols-3">
                    
                    <!-- Left Column: Trend & Recent -->
                    <div class="lg:col-span-2 space-y-8">
                        
                        <!-- Trend Chart -->
                        <div class="rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Analisis Pengajuan</h3>
                                    <p class="text-sm text-slate-500">Tren pengajuan surat 12 bulan terakhir</p>
                                </div>
                                <div class="px-3 py-1 bg-slate-50 rounded-lg text-xs font-medium text-slate-600 border border-slate-100">
                                    Yearly View
                                </div>
                            </div>
                            
                            <!-- Custom CSS Bar Chart -->
                            <div class="flex items-end justify-between h-64 gap-2 pt-4 pb-2">
                                <div
                                    v-for="item in monthlyPengajuan"
                                    :key="item.bulan"
                                    class="flex flex-col items-center gap-2 flex-1 group"
                                >
                                    <div class="relative w-full flex justify-center h-full items-end">
                                        <!-- Tooltip -->
                                        <div class="absolute -top-10 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-xs py-1 px-2 rounded -translate-y-1 pointer-events-none whitespace-nowrap z-10">
                                            {{ item.total }} Pengajuan
                                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-slate-900 rotate-45"></div>
                                        </div>
                                        <!-- Bar -->
                                        <div
                                            class="w-full max-w-[40px] bg-gradient-to-t from-blue-500 to-indigo-400 rounded-t-lg transition-all duration-300 group-hover:from-blue-600 group-hover:to-indigo-500 group-hover:shadow-lg group-hover:shadow-blue-500/20"
                                            :style="{ height: `${Math.max((item.total / maxMonthlyTotal) * 100, 4)}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-[10px] sm:text-xs font-medium text-slate-400 group-hover:text-slate-600 transition-colors">
                                        {{ item.bulan.split(' ')[0].substring(0, 3) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Table -->
                        <div class="rounded-2xl bg-white shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden">
                            <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">Pengajuan Terbaru</h3>
                                    <p class="text-sm text-slate-500">Daftar pengajuan surat yang baru masuk</p>
                                </div>
                                <Link href="/admin/surat" class="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                    Lihat Semua
                                </Link>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100 text-left">
                                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Mahasiswa</th>
                                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Jenis Surat</th>
                                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider text-right">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <tr v-for="item in recentPengajuan" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold">
                                                        {{ item.mahasiswa.nama.charAt(0) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold text-slate-900">{{ item.mahasiswa.nama }}</p>
                                                        <p class="text-xs text-slate-500">{{ item.mahasiswa.nim }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-slate-700 font-medium">{{ item.jenis_surat }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span 
                                                    class="px-2.5 py-1 rounded-full text-xs font-medium border"
                                                    :class="{
                                                        'bg-amber-50 text-amber-700 border-amber-100': item.status_badge === 'warning',
                                                        'bg-emerald-50 text-emerald-700 border-emerald-100': item.status_badge === 'success',
                                                        'bg-red-50 text-red-700 border-red-100': item.status_badge === 'danger',
                                                        'bg-blue-50 text-blue-700 border-blue-100': item.status_badge === 'info'
                                                    }"
                                                >
                                                    {{ item.status_label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-xs font-medium text-slate-500">{{ item.created_at }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Stats & Breakdown -->
                    <div class="space-y-8">
                        
                        <!-- Status Breakdown (Donut-like visual) -->
                        <div class="rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
                            <h3 class="text-lg font-bold text-slate-900 mb-6">Status Surat</h3>
                            
                            <div class="flex flex-col items-center justify-center py-6">
                                <!-- CSS Donut Chart Simulation -->
                                <div class="relative w-48 h-48 rounded-full bg-slate-50 flex items-center justify-center">
                                    <!-- Segments would normally be a conic-gradient, here we simplify for safety/demo -->
                                    <div 
                                        class="absolute inset-0 rounded-full"
                                        :style="{
                                            background: `conic-gradient(
                                                #F59E0B 0% ${((suratPerStatus.pending || 0) / totalSurat) * 100 || 0}%,
                                                #3B82F6 ${((suratPerStatus.pending || 0) / totalSurat) * 100 || 0}% ${(((suratPerStatus.pending || 0) + (suratPerStatus.processing || 0)) / totalSurat) * 100 || 0}%,
                                                #10B981 ${(((suratPerStatus.pending || 0) + (suratPerStatus.processing || 0)) / totalSurat) * 100 || 0}% 100%
                                            )` 
                                        }"
                                    ></div>
                                    <!-- Inner White Circle -->
                                    <div class="absolute inset-4 bg-white rounded-full flex flex-col items-center justify-center shadow-inner z-10">
                                        <span class="text-4xl font-bold text-slate-900">{{ totalSurat }}</span>
                                        <span class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Surat</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-6">
                                <div v-for="(count, status) in suratPerStatus" :key="status" class="flex items-center gap-3">
                                    <div :class="[statusLabels[status]?.class || 'bg-gray-500', 'w-3 h-3 rounded-full flex-shrink-0 ring-2 ring-white shadow-sm']" />
                                    <div>
                                        <p class="text-xs text-slate-500 capitalize">{{ statusLabels[status]?.label }}</p>
                                        <p class="text-sm font-bold text-slate-900">{{ count }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Prodi List -->
                        <div class="rounded-2xl bg-white p-6 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100">
                            <h3 class="text-lg font-bold text-slate-900 mb-4">Top Program Studi</h3>
                            <div class="space-y-5">
                                <div v-for="(prodi, index) in mahasiswaPerProdi.slice(0, 5)" :key="prodi.nama" class="space-y-2">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="font-medium text-slate-700 truncate pr-4">{{ prodi.nama }}</span>
                                        <span class="font-bold text-slate-900">{{ prodi.total }}</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full rounded-full transition-all duration-500"
                                            :class="[
                                                index === 0 ? 'bg-blue-500' : 
                                                index === 1 ? 'bg-indigo-500' :
                                                index === 2 ? 'bg-purple-500' :
                                                'bg-slate-400'
                                            ]"
                                            :style="{ width: `${(prodi.total / maxProdiTotal) * 100}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <Link href="/admin/surat" class="p-4 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-500/30 hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all text-center">
                                <p class="font-bold text-lg">Kelola Surat</p>
                                <p class="text-blue-100 text-xs mt-1">Lihat pengajuan</p>
                            </Link>
                            <Link href="/admin/mahasiswa" class="p-4 rounded-xl bg-white border border-slate-100 text-slate-700 hover:bg-slate-50 hover:border-slate-200 transition-all text-center shadow-sm">
                                <p class="font-bold text-lg">Mahasiswa</p>
                                <p class="text-slate-500 text-xs mt-1">Database</p>
                            </Link>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
