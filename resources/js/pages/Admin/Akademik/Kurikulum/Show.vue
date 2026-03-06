<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, BookOpen } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';

interface Kurikulum {
    id: number;
    nama_kurikulum: string;
    prodi: string;
    semester: string;
    jumlah_sks_lulus: number;
    jumlah_sks_wajib: number;
    jumlah_sks_pilihan: number;
}

interface MatkulKurikulum {
    id: number;
    kode_matkul: string;
    nama_matkul: string;
    semester: number;
    sks_mata_kuliah: number;
    sks_tatap_muka: number;
    sks_praktek: number;
    sks_praktek_lapangan: number;
    sks_simulasi: number;
    apakah_wajib: string;
}

const props = defineProps<{
    kurikulum: Kurikulum;
    matkulKurikulum: any; // paginated
    filters: Record<string, any>;
}>();

const matkulColumns = [
    { key: 'semester', label: 'Smt', sortable: true, align: 'center' as const },
    { key: 'kode_matkul', label: 'Kode MK', sortable: true },
    { key: 'nama_matkul', label: 'Mata Kuliah', sortable: true },
    { key: 'sks_mata_kuliah', label: 'SKS Total', sortable: true, align: 'center' as const },
    { key: 'sks_detail', label: 'TM / PR / LP / SM', align: 'center' as const },
    { key: 'apakah_wajib', label: 'Sifat', sortable: true },
];

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Aspek Akademik', href: '#' },
    { title: 'Kurikulum', href: '/admin/akademik/kurikulum' },
    { title: 'Detail Kurikulum', href: '#' },
];
</script>

<template>
    <Head :title="`Detail Kurikulum - ${kurikulum.nama_kurikulum}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col pb-10 w-full max-w-7xl mx-auto">
            
            <!-- Modern Header Section -->
            <div class="bg-gradient-to-r from-slate-900 to-indigo-900 text-white px-6 py-10 lg:px-10 lg:py-12 rounded-b-3xl sm:rounded-b-[3rem] shadow-lg mb-8 relative overflow-hidden">
                <!-- Decorative pattern -->
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
                
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                    <div class="flex items-start gap-5">
                        <Link href="/admin/akademik/kurikulum">
                            <Button variant="outline" size="icon" class="mt-1 shrink-0 bg-white/10 hover:bg-white/20 border-white/20 text-white shadow-sm backdrop-blur-sm">
                                <ArrowLeft class="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h1 class="text-3xl font-bold tracking-tight text-white/95">{{ kurikulum.nama_kurikulum }}</h1>
                                <Badge variant="secondary" class="font-mono bg-indigo-500/20 text-indigo-100 border-indigo-500/30 backdrop-blur-md px-3 py-1 text-sm shadow-sm">{{ kurikulum.semester }}</Badge>
                            </div>
                            <p class="text-indigo-200 text-lg flex items-center gap-2">
                                <BookOpen class="w-5 h-5 opacity-70" />
                                {{ kurikulum.prodi }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-4 px-6 lg:px-10">
                
                <!-- Kurikulum Info Card -->
                <Card class="md:col-span-1 h-fit shadow-sm border-slate-200/60 overflow-hidden">
                    <CardHeader class="border-b bg-slate-50/50 pb-4">
                        <CardTitle class="text-base font-bold flex items-center gap-2.5 text-slate-800">
                            <div class="p-2 bg-indigo-100 text-indigo-600 rounded-md">
                                <BookOpen class="w-4 h-4" />
                            </div>
                            Ringkasan Kurikulum
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="grid grid-cols-1 divide-y divide-slate-100">
                            <!-- Detail Row -->
                            <div class="p-4 flex flex-col gap-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Program Studi</p>
                                <p class="font-medium text-slate-700">{{ kurikulum.prodi }}</p>
                            </div>
                            <div class="p-4 flex flex-col gap-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Mulai Berlaku</p>
                                <p class="font-medium text-slate-700">{{ kurikulum.semester }}</p>
                            </div>
                            
                            <!-- Highlighted Stat -->
                            <div class="p-4 bg-indigo-50/50">
                                <p class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-2">Total SKS Lulus</p>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-indigo-700 tracking-tight">{{ kurikulum.jumlah_sks_lulus }}</span>
                                    <span class="text-sm font-medium text-indigo-600/70">SKS</span>
                                </div>
                            </div>

                            <!-- Split Stats -->
                            <div class="grid grid-cols-2 divide-x divide-slate-100">
                                <div class="p-4 flex flex-col gap-1">
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SKS Wajib</p>
                                    <p class="font-bold text-slate-700 text-lg">{{ kurikulum.jumlah_sks_wajib }}</p>
                                </div>
                                <div class="p-4 flex flex-col gap-1">
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SKS Pilihan</p>
                                    <p class="font-bold text-slate-700 text-lg">{{ kurikulum.jumlah_sks_pilihan }}</p>
                                </div>
                            </div>

                            <!-- Total MK -->
                            <div class="p-4 border-t-2 border-slate-100 bg-slate-50">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-slate-600">Total Mata Kuliah</p>
                                    <Badge variant="secondary" class="bg-white border text-slate-800 shadow-sm px-2.5 py-0.5">
                                        {{ matkulKurikulum.total ?? matkulKurikulum.data?.length ?? matkulKurikulum.length }} Matkul
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Mata Kuliah Table -->
                <div class="md:col-span-3 space-y-6">
                    <Card class="overflow-hidden shadow-sm border-slate-200/60">
                        <CardHeader class="border-b bg-slate-50/50 pb-4">
                            <CardTitle class="text-base font-bold flex items-center gap-2 text-slate-800">
                                <BookOpen class="w-5 h-5 text-slate-500" />
                                Daftar Mata Kuliah
                            </CardTitle>
                            <CardDescription>Mata kuliah yang terdaftar dalam kurikulum ini.</CardDescription>
                        </CardHeader>
                        <div class="px-6 py-4">
                            <SmartTable
                                :data="matkulKurikulum"
                                :columns="matkulColumns"
                                :search="filters?.search"
                                title="Daftar Mata Kuliah"
                            >
                                <template #cell-sks_mata_kuliah="{ row }">
                                    <span class="font-bold">{{ row.sks_mata_kuliah }}</span>
                                </template>
                                <template #cell-sks_detail="{ row }">
                                    <span class="text-xs text-slate-500">
                                        {{ row.sks_tatap_muka }} / {{ row.sks_praktek }} / {{ row.sks_praktek_lapangan }} / {{ row.sks_simulasi }}
                                    </span>
                                </template>
                                <template #cell-apakah_wajib="{ row }">
                                    <Badge :variant="row.apakah_wajib === 'Wajib' ? 'default' : 'secondary'" :class="row.apakah_wajib === 'Wajib' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-slate-100 text-slate-600'">
                                        {{ row.apakah_wajib }}
                                    </Badge>
                                </template>
                            </SmartTable>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
