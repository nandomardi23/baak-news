<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, BookOpen, Users, GraduationCap, Calendar } from 'lucide-vue-next';

interface Mahasiswa {
    id: number;
    nim: string | null;
    nama: string | null;
    nama_dosen: string | null;
}

interface KelasKuliah {
    id: number;
    id_kelas_kuliah: string;
    nama_kelas_kuliah: string | null;
    kode_mata_kuliah: string | null;
    nama_mata_kuliah: string | null;
    sks: number | null;
    kapasitas: number | null;
    prodi: string | null;
    semester: string | null;
    dosen_pengajar: string | null;
    mahasiswa: Mahasiswa[];
}

const props = defineProps<{
    kelasKuliah: KelasKuliah;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Kelas Kuliah', href: '/admin/kelas-kuliah' },
    { title: props.kelasKuliah.nama_kelas_kuliah || 'Detail', href: '#' },
];
</script>

<template>
    <Head :title="`Kelas: ${kelasKuliah.nama_kelas_kuliah}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 lg:p-10 w-full">
            
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link href="/admin/kelas-kuliah">
                        <Button variant="outline" size="icon" class="h-9 w-9">
                            <ArrowLeft class="w-4 h-4" />
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ kelasKuliah.nama_kelas_kuliah }}</h1>
                        <p class="text-slate-500 mt-1">{{ kelasKuliah.kode_mata_kuliah }} - {{ kelasKuliah.nama_mata_kuliah }}</p>
                    </div>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-blue-50 p-3">
                            <BookOpen class="h-5 w-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">SKS</p>
                            <p class="text-xl font-bold text-slate-900">{{ kelasKuliah.sks || '-' }}</p>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <Users class="h-5 w-5 text-emerald-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Peserta</p>
                            <p class="text-xl font-bold text-slate-900">{{ kelasKuliah.mahasiswa.length }} / {{ kelasKuliah.kapasitas || '∞' }}</p>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-purple-50 p-3">
                            <GraduationCap class="h-5 w-5 text-purple-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Dosen Pengajar</p>
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ kelasKuliah.dosen_pengajar || '-' }}</p>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-rose-50 p-3">
                            <GraduationCap class="h-5 w-5 text-rose-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Program Studi</p>
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ kelasKuliah.prodi || '-' }}</p>
                        </div>
                    </CardContent>
                </Card>
                
                <Card>
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-lg bg-amber-50 p-3">
                            <Calendar class="h-5 w-5 text-amber-600" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-500">Semester</p>
                            <p class="text-lg font-semibold text-slate-900 truncate">{{ kelasKuliah.semester || '-' }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Daftar Mahasiswa -->
            <Card>
                <CardHeader>
                    <CardTitle>Daftar Mahasiswa</CardTitle>
                    <CardDescription>Mahasiswa yang mengambil kelas ini</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="kelasKuliah.mahasiswa.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">No</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">NIM</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama Mahasiswa</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Dosen Pengajar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                <tr v-for="(mhs, index) in kelasKuliah.mahasiswa" :key="mhs.id" class="hover:bg-slate-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-slate-900">{{ mhs.nim || '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-900">{{ mhs.nama || '-' }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ mhs.nama_dosen || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 text-slate-500">
                        <Users class="w-12 h-12 mx-auto mb-4 text-slate-300" />
                        <p>Belum ada mahasiswa yang terdaftar di kelas ini.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
