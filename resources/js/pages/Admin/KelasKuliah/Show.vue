<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Users, BookOpen, Clock, GraduationCap } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

interface DosenPengajar {
    id: number;
    nama: string;
    nidn: string | null;
    sks_substansi: number;
    rencana_tm: number;
    realisasi_tm: number;
    evaluasi: string | null;
}

interface Peserta {
    id: number;
    nim: string;
    nama: string;
    angkatan: string;
    prodi: string;
}

interface KelasKuliah {
    id: number;
    id_kelas_kuliah: string;
    nama_kelas_kuliah: string;
    kode_mata_kuliah: string;
    nama_mata_kuliah: string;
    sks: number;
    kapasitas: number;
    prodi: string;
    semester: string;
    dosen_pengajar: DosenPengajar[];
    peserta: Peserta[];
    total_peserta: number;
}

const props = defineProps<{
    kelasKuliah: KelasKuliah;
    peserta: any; // Add paginated peserta prop
    filters: Record<string, any>;
}>();

const pesertaColumns = [
    { key: 'nim', label: 'NIM', sortable: true },
    { key: 'nama', label: 'Nama Mahasiswa', sortable: true },
    { key: 'angkatan', label: 'Angkatan', sortable: true },
    { key: 'prodi', label: 'Program Studi', sortable: true },
];

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kelas Kuliah', href: '/admin/kelas-kuliah' },
    { title: 'Detail Kelas', href: '#' },
];
</script>

<template>
    <Head :title="`Detail Kelas - ${kelasKuliah.nama_kelas_kuliah}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col pb-10 w-full max-w-7xl mx-auto">
            
            <!-- Modern Header Section -->
            <div class="bg-gradient-to-r from-slate-900 to-indigo-900 text-white px-6 py-10 lg:px-10 lg:py-12 rounded-b-3xl sm:rounded-b-[3rem] shadow-lg mb-8 relative overflow-hidden">
                <!-- Decorative pattern -->
                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
                
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-start justify-between gap-6">
                    <div class="flex items-start gap-5">
                        <Link href="/admin/kelas-kuliah">
                            <Button variant="outline" size="icon" class="mt-1 shrink-0 bg-white/10 hover:bg-white/20 border-white/20 text-white shadow-sm backdrop-blur-sm">
                                <ArrowLeft class="w-4 h-4" />
                            </Button>
                        </Link>
                        <div>
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h1 class="text-3xl font-bold tracking-tight text-white/95">{{ kelasKuliah.nama_kelas_kuliah }}</h1>
                                <Badge variant="secondary" class="font-mono bg-indigo-500/20 text-indigo-100 border-indigo-500/30 backdrop-blur-md px-3 py-1 text-sm shadow-sm">{{ kelasKuliah.kode_mata_kuliah }}</Badge>
                            </div>
                            <p class="text-indigo-200 text-lg flex items-center gap-2">
                                <BookOpen class="w-5 h-5 opacity-70" />
                                {{ kelasKuliah.nama_mata_kuliah }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6 px-6 lg:px-10">
                
                <!-- Class Info Card (Top) -->
                <Card class="w-full shadow-sm border-slate-200/60 overflow-hidden">
                    <CardHeader class="border-b bg-slate-50/50 pb-4">
                        <CardTitle class="text-base font-bold flex items-center gap-2.5 text-slate-800">
                            <div class="p-2 bg-indigo-100 text-indigo-600 rounded-md">
                                <Clock class="w-4 h-4" />
                            </div>
                            Informasi Kelas
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="grid grid-cols-1 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                            <div class="p-4 flex flex-col gap-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Program Studi</p>
                                <p class="font-medium text-slate-700">{{ kelasKuliah.prodi }}</p>
                            </div>
                            <div class="p-4 flex flex-col gap-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Semester</p>
                                <p class="font-medium text-slate-700">{{ kelasKuliah.semester }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 divide-x divide-slate-100 bg-slate-50/50 md:col-span-1">
                                <div class="p-4 flex flex-col gap-1">
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">SKS</p>
                                    <p class="font-bold text-slate-700 text-lg">{{ kelasKuliah.sks }}</p>
                                </div>
                                <div class="p-4 flex flex-col gap-1">
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kapasitas</p>
                                    <p class="font-bold text-slate-700 text-lg">{{ kelasKuliah.kapasitas }}</p>
                                </div>
                            </div>
                            
                            <!-- Highlighted Stat -->
                            <div class="p-4 bg-indigo-50/50 border-t-2 md:border-t-0 md:border-l-2 border-indigo-100 md:col-span-1">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Total Peserta</p>
                                    <Users class="w-4 h-4 text-indigo-400" />
                                </div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-indigo-700 tracking-tight">{{ kelasKuliah.total_peserta }}</span>
                                    <span class="text-sm font-medium text-indigo-600/70">Mahasiswa</span>
                                </div>
                                <!-- Progress bar visualization for capacity -->
                                <div class="w-full bg-indigo-200/50 rounded-full h-1.5 mt-3 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" :style="{ width: `${Math.min((kelasKuliah.total_peserta / (kelasKuliah.kapasitas || 1)) * 100, 100)}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Tabs Section (Bottom) -->
                <div class="w-full">
                    <Tabs defaultValue="dosen" class="w-full">
                        <TabsList class="mb-4">
                            <TabsTrigger value="dosen" class="flex items-center gap-2">
                                <GraduationCap class="w-4 h-4" />
                                Dosen Pengajar
                            </TabsTrigger>
                            <TabsTrigger value="peserta" class="flex items-center gap-2">
                                <Users class="w-4 h-4" />
                                Peserta Kelas
                            </TabsTrigger>
                        </TabsList>
                        
                        <TabsContent value="dosen">
                            <Card class="shadow-sm border-slate-200/60 overflow-hidden">
                                <CardHeader class="border-b bg-slate-50/50 pb-4">
                                    <CardTitle class="text-base font-bold flex items-center gap-2.5 text-slate-800">
                                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-md">
                                            <GraduationCap class="w-4 h-4" />
                                        </div>
                                        Dosen Pengajar (Team Teaching)
                                    </CardTitle>
                                    <CardDescription>Daftar dosen yang mengampu mata kuliah ini beserta beban ajar.</CardDescription>
                                </CardHeader>
                                <CardContent class="p-0">
                                    <Table>
                                        <TableHeader class="bg-transparent">
                                            <TableRow class="hover:bg-transparent">
                                                <TableHead class="font-semibold text-slate-600 h-10">Nama Dosen</TableHead>
                                                <TableHead class="font-semibold text-slate-600 h-10">NIDN</TableHead>
                                                <TableHead class="font-semibold text-slate-600 h-10 text-center">SKS Ajar</TableHead>
                                                <TableHead class="font-semibold text-slate-600 h-10 text-center">Rencana TM</TableHead>
                                                <TableHead class="font-semibold text-slate-600 h-10 text-center">Realisasi TM</TableHead>
                                                <TableHead class="font-semibold text-slate-600 h-10">Jenis Evaluasi</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="dosen in kelasKuliah.dosen_pengajar" :key="dosen.id">
                                                <TableCell class="font-medium text-slate-700 py-3">{{ dosen.nama }}</TableCell>
                                                <TableCell class="font-mono text-xs text-slate-500 py-3">{{ dosen.nidn || '-' }}</TableCell>
                                                <TableCell class="text-center font-semibold text-slate-700 py-3">{{ dosen.sks_substansi }}</TableCell>
                                                <TableCell class="text-center py-3">{{ dosen.rencana_tm }}</TableCell>
                                                <TableCell class="text-center py-3">{{ dosen.realisasi_tm }}</TableCell>
                                                <TableCell class="py-3 text-slate-600">{{ dosen.evaluasi || '-' }}</TableCell>
                                            </TableRow>
                                            <TableRow v-if="kelasKuliah.dosen_pengajar.length === 0">
                                                <TableCell colspan="6" class="text-center text-slate-500 py-8">
                                                    Belum ada data dosen pengajar. Silahkan lakukan Sinkronisasi Dosen Pengajar.
                                                </TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            </Card>
                        </TabsContent>

                        <TabsContent value="peserta">
                            <Card class="overflow-hidden shadow-sm border-slate-200/60">
                                <CardHeader class="border-b bg-slate-50/50 pb-4">
                                    <CardTitle class="text-base font-bold flex items-center gap-2.5 text-slate-800">
                                        <div class="p-2 bg-indigo-100 text-indigo-600 rounded-md">
                                            <Users class="w-4 h-4" />
                                        </div>
                                        Peserta Kelas
                                    </CardTitle>
                                    <CardDescription>Mahasiswa yang mengambil mata kuliah ini (KRS).</CardDescription>
                                </CardHeader>
                                <div class="px-6 py-4">
                                    <SmartTable
                                        :data="peserta"
                                        :columns="pesertaColumns"
                                        :search="filters?.search"
                                        title="Data Peserta Kelas"
                                    />
                                </div>
                            </Card>
                        </TabsContent>
                    </Tabs>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
