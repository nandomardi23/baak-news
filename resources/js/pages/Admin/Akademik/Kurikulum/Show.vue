<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, BookOpen } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

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
    matkulKurikulum: MatkulKurikulum[];
}>();

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
        <div class="flex h-full flex-1 flex-col gap-8 p-6 lg:p-10 w-full max-w-7xl mx-auto">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <Link href="/admin/akademik/kurikulum">
                        <Button variant="outline" size="icon" class="mt-1">
                            <ArrowLeft class="w-4 h-4" />
                        </Button>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ kurikulum.nama_kurikulum }}</h1>
                            <Badge variant="outline" class="font-mono">{{ kurikulum.semester }}</Badge>
                        </div>
                        <p class="text-slate-500 mt-1">{{ kurikulum.prodi }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-4">
                
                <!-- Kurikulum Info Card -->
                <Card class="md:col-span-1 h-fit">
                    <CardHeader>
                        <CardTitle class="text-lg flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-slate-500" />
                            Info Kurikulum
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Program Studi</p>
                            <p class="font-medium">{{ kurikulum.prodi }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Mulai Berlaku</p>
                            <p class="font-medium">{{ kurikulum.semester }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total SKS Lulus</p>
                            <p class="font-medium text-xl">{{ kurikulum.jumlah_sks_lulus }} SKS</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-500">SKS Wajib</p>
                                <p class="font-medium">{{ kurikulum.jumlah_sks_wajib }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">SKS Pilihan</p>
                                <p class="font-medium">{{ kurikulum.jumlah_sks_pilihan }}</p>
                            </div>
                        </div>
                        <div class="mt-4 border-t pt-4">
                            <p class="text-sm font-medium text-slate-500 mb-1">Total Mata Kuliah</p>
                            <p class="font-bold text-lg text-indigo-600">{{ matkulKurikulum.length }} Matkul</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Mata Kuliah Table -->
                <div class="md:col-span-3 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg flex items-center gap-2">
                                <BookOpen class="w-5 h-5 text-slate-500" />
                                Daftar Mata Kuliah
                            </CardTitle>
                            <CardDescription>Mata kuliah yang terdaftar dalam kurikulum ini.</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-16">Smt</TableHead>
                                        <TableHead>Kode MK</TableHead>
                                        <TableHead>Mata Kuliah</TableHead>
                                        <TableHead class="text-center">SKS Total</TableHead>
                                        <TableHead class="text-center">TM / PR / LP / SM</TableHead>
                                        <TableHead>Sifat</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="matkul in matkulKurikulum" :key="matkul.id">
                                        <TableCell class="font-medium text-slate-500 text-center">{{ matkul.semester }}</TableCell>
                                        <TableCell class="font-mono text-sm">{{ matkul.kode_matkul }}</TableCell>
                                        <TableCell class="font-medium">{{ matkul.nama_matkul }}</TableCell>
                                        <TableCell class="text-center font-bold">{{ matkul.sks_mata_kuliah }}</TableCell>
                                        <TableCell class="text-center text-xs text-slate-500">
                                            {{ matkul.sks_tatap_muka }} / {{ matkul.sks_praktek }} / {{ matkul.sks_praktek_lapangan }} / {{ matkul.sks_simulasi }}
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="matkul.apakah_wajib === 'Wajib' ? 'default' : 'secondary'">
                                                {{ matkul.apakah_wajib }}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="matkulKurikulum.length === 0">
                                        <TableCell colspan="6" class="text-center text-slate-500 py-8">
                                            Belum ada mata kuliah dalam kurikulum ini. Silahkan lakukan Sinkronisasi Matkul Kurikulum.
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
