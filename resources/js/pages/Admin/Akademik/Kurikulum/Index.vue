<script setup lang="ts">
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';

defineOptions({ layout: AppLayout });
const { setBreadcrumbs } = useBreadcrumbs();
import AppLayout from '@/layouts/AppLayout.vue';

import { Head, router, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import SmartTable from '@/components/ui/datatable/SmartTable.vue';
import { Eye, BookOpen } from 'lucide-vue-next';

interface Kurikulum {
    id: number;
    id_kurikulum: string;
    nama_kurikulum: string;
    prodi: string;
    semester: string;
    jumlah_sks_lulus: number;
    jumlah_sks_wajib: number;
    jumlah_sks_pilihan: number;
}

const props = defineProps<{
    kurikulum: any;
    prodiList: Record<string, string>;
    filters: {
        search?: string;
        prodi?: string;
    };
}>();

setBreadcrumbs([
    { title: 'Dashboard', href: '/admin' },
    { title: 'Aspek Akademik', href: '#' },
    { title: 'Kurikulum', href: '/admin/akademik/kurikulum' },
]);

const columns = [
    { key: 'nama_kurikulum', label: 'Nama Kurikulum', sortable: true },
    { key: 'prodi', label: 'Program Studi', sortable: false },
    { key: 'semester', label: 'Mulai Berlaku (Semester)', sortable: true },
    { key: 'sks_total', label: 'SKS Lulus / W / P', sortable: false, align: 'center' as const },
    { key: 'actions', label: 'Aksi', align: 'center' as const },
];

</script>

<template>
    <Head title="Kurikulum" />

    
        <div class="flex h-full flex-1 flex-col gap-8 p-6 w-full max-w-7xl mx-auto lg:p-10">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Kurikulum Program Studi</h1>
                    <p class="text-slate-500 mt-1">Data kurikulum dan mata kuliah yang disinkronisasi dari Neo Feeder.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <BookOpen class="w-4 h-4" />
                        <span>{{ kurikulum.total }} Kurikulum</span>
                    </div>
                </div>
            </div>

            <SmartTable
                :data="kurikulum"
                :columns="columns"
                :search="filters.search"
                :filters="{ prodi: filters.prodi }"
                title="Filter Kurikulum"
            >
                <template #filters>
                    <!-- Prodi Filter -->
                    <div class="w-full sm:w-64">
                        <Select 
                            :model-value="filters.prodi || 'all'" 
                            @update:model-value="(val) => router.get('/admin/akademik/kurikulum', { ...filters, prodi: val === 'all' ? null : String(val) }, { preserveState: true })"
                        >
                            <SelectTrigger class="h-9 w-full">
                                <SelectValue placeholder="Pilih Program Studi" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Prodi</SelectItem>
                                <SelectItem v-for="(nama, id) in prodiList" :key="id" :value="String(id)">
                                    {{ nama }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <template #cell-sks_total="{ row }">
                    <div class="text-xs">
                        <span class="font-bold border px-1 rounded">{{ row.jumlah_sks_lulus }}</span> / 
                        <span class="text-slate-500">{{ row.jumlah_sks_wajib }}</span> / 
                        <span class="text-slate-500">{{ row.jumlah_sks_pilihan }}</span>
                    </div>
                </template>

                <template #cell-actions="{ row }">
                    <div class="flex items-center justify-center gap-2">
                        <Link :href="`/admin/akademik/kurikulum/${row.id}`">
                            <Button variant="ghost" size="icon" class="h-8 w-8 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50">
                                <Eye class="w-4 h-4" />
                            </Button>
                        </Link>
                    </div>
                </template>
            </SmartTable>
        </div>
    
</template>
