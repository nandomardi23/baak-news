<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Prodi {
    id: number;
    id_prodi: string;
    kode_prodi: string;
    nama_prodi: string;
    jenjang: string;
    jenis_program: string;
    akreditasi: string | null;
    is_active: boolean;
}

defineProps<{
    prodiList: Prodi[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Program Studi', href: '/admin/akademik/prodi' },
];
</script>

<template>
    <Head title="Program Studi" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Program Studi</h1>
                    <p class="text-muted-foreground">Data program studi dari Neo Feeder</p>
                </div>
                <div class="px-4 py-2 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded-lg text-sm">
                    Data dari Neo Feeder
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Kode</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Nama Program Studi</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-muted-foreground">Jenjang</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-muted-foreground">Jenis</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-muted-foreground">Akreditasi</th>
                                <th class="px-6 py-3 text-center text-sm font-medium text-muted-foreground">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="prodi in prodiList" :key="prodi.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4 font-mono text-sm">{{ prodi.kode_prodi }}</td>
                                <td class="px-6 py-4 font-medium">{{ prodi.nama_prodi }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 rounded text-xs font-medium">
                                        {{ prodi.jenjang }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm capitalize">{{ prodi.jenis_program }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="prodi.akreditasi" class="px-2 py-1 bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 rounded text-xs font-medium">
                                        {{ prodi.akreditasi }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="prodi.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-800'"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ prodi.is_active ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="prodiList.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada data program studi.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
