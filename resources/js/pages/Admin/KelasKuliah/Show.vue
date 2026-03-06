<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Users, BookOpen, Clock, GraduationCap } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

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
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kelas Kuliah', href: '/admin/kelas-kuliah' },
    { title: 'Detail Kelas', href: '#' },
];
</script>

<template>
    <Head :title="`Detail Kelas - ${kelasKuliah.nama_kelas_kuliah}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <Link href="/admin/kelas-kuliah">
                        <Button variant="outline" size="icon" class="mt-1">
                            <ArrowLeft class="w-4 h-4" />
                        </Button>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ kelasKuliah.nama_kelas_kuliah }}</h1>
                            <Badge variant="outline" class="font-mono">{{ kelasKuliah.kode_mata_kuliah }}</Badge>
                        </div>
                        <p class="text-slate-500 mt-1">{{ kelasKuliah.nama_mata_kuliah }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                
                <!-- Class Info Card -->
                <Card class="md:col-span-1 h-fit">
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-slate-500" />
                            Informasi Kelas
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Program Studi</p>
                            <p class="font-medium">{{ kelasKuliah.prodi }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Semester</p>
                            <p class="font-medium">{{ kelasKuliah.semester }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">SKS</p>
                                <p class="font-medium">{{ kelasKuliah.sks }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Kapasitas</p>
                                <p class="font-medium">{{ kelasKuliah.kapasitas }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Peserta</p>
                            <div class="flex items-center gap-2">
                                <Users class="w-4 h-4 text-slate-400" />
                                <span class="font-medium">{{ kelasKuliah.total_peserta }} Mahasiswa</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Main Content Column -->
                <div class="md:col-span-2 space-y-6">
                    
                    <!-- Dosen Pengajar Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <GraduationCap class="w-5 h-5 text-slate-500" />
                                Dosen Pengajar (Team Teaching)
                            </CardTitle>
                            <CardDescription>Daftar dosen yang mengampu mata kuliah ini beserta beban ajar.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Nama Dosen</TableHead>
                                        <TableHead>NIDN</TableHead>
                                        <TableHead class="text-center">SKS Ajar</TableHead>
                                        <TableHead class="text-center">Rencana TM</TableHead>
                                        <TableHead class="text-center">Realisasi TM</TableHead>
                                        <TableHead>Jenis Evaluasi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="dosen in kelasKuliah.dosen_pengajar" :key="dosen.id">
                                        <TableCell class="font-medium">{{ dosen.nama }}</TableCell>
                                        <TableCell class="font-mono text-xs text-slate-500">{{ dosen.nidn || '-' }}</TableCell>
                                        <TableCell class="text-center">{{ dosen.sks_substansi }}</TableCell>
                                        <TableCell class="text-center">{{ dosen.rencana_tm }}</TableCell>
                                        <TableCell class="text-center">{{ dosen.realisasi_tm }}</TableCell>
                                        <TableCell>{{ dosen.evaluasi || '-' }}</TableCell>
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

                    <!-- Peserta Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <Users class="w-5 h-5 text-slate-500" />
                                Peserta Kelas
                            </CardTitle>
                            <CardDescription>Mahasiswa yang mengambil mata kuliah ini (KRS).</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-12">No</TableHead>
                                        <TableHead>NIM</TableHead>
                                        <TableHead>Nama Mahasiswa</TableHead>
                                        <TableHead>Angkatan</TableHead>
                                        <TableHead>Program Studi</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(mhs, idx) in kelasKuliah.peserta" :key="mhs.id">
                                        <TableCell class="text-slate-500">{{ idx + 1 }}</TableCell>
                                        <TableCell class="font-mono">{{ mhs.nim }}</TableCell>
                                        <TableCell class="font-medium">{{ mhs.nama }}</TableCell>
                                        <TableCell>{{ mhs.angkatan }}</TableCell>
                                        <TableCell>{{ mhs.prodi }}</TableCell>
                                    </TableRow>
                                    <TableRow v-if="kelasKuliah.peserta.length === 0">
                                        <TableCell colspan="5" class="text-center text-slate-500 py-8">
                                            Belum ada peserta terdaftar.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                </div>
            </div>
        </div>
    </AppLayout>
</template>
