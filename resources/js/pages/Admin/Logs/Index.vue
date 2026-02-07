<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    DataTable,
    TableHeader,
    TableRow,
    TableCell,
    Pagination,
} from '@/components/ui/datatable';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Search,
    Filter,
    Activity,
    Monitor,
    Clock,
    User as UserIcon,
    Terminal,
    Database,
    FileText,
    CheckCircle,
    XCircle,
    LayoutDashboard
} from 'lucide-vue-next';

interface Log {
    id: number;
    user: string;
    action: string;
    action_badge: string;
    description: string;
    model_type: string | null;
    model_id: number | null;
    ip_address: string | null;
    created_at: string;
    time_ago: string;
}

interface Filters {
    action: string | null;
    search: string | null;
}

const props = defineProps<{
    logs: {
        data: Log[];
        links: any[];
        current_page: number;
        last_page: number;
        total: number;
        from: number;
        to: number;
    };
    actions: string[];
    filters: Filters;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin' },
    { title: 'Log Aktivitas', href: '/admin/logs' },
];

const search = ref(props.filters.search || '');
const selectedAction = ref(props.filters.action || '');

const applyFilters = () => {
    router.get('/admin/logs', {
        search: search.value || undefined,
        action: selectedAction.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
});

watch(selectedAction, applyFilters);

const resetFilters = () => {
    search.value = '';
    selectedAction.value = '';
    router.get('/admin/logs');
};

const getActionLabel = (action: string): string => {
    const labels: Record<string, string> = {
        created: 'Dibuat',
        updated: 'Diperbarui',
        deleted: 'Dihapus',
        approved: 'Disetujui',
        rejected: 'Ditolak',
        printed: 'Dicetak',
        login: 'Login',
        logout: 'Logout',
    };
    return labels[action] || action;
};

const getActionColor = (action: string): string => {
    const colors: Record<string, string> = {
        created: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        approved: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        updated: 'bg-blue-100 text-blue-700 border-blue-200',
        login: 'bg-indigo-100 text-indigo-700 border-indigo-200',
        logout: 'bg-indigo-100 text-indigo-700 border-indigo-200',
        printed: 'bg-violet-100 text-violet-700 border-violet-200',
        deleted: 'bg-red-100 text-red-700 border-red-200',
        rejected: 'bg-red-100 text-red-700 border-red-200',
    };
    return colors[action] || 'bg-slate-100 text-slate-700 border-slate-200';
};

const getActionIcon = (action: string) => {
    const icons: Record<string, any> = {
        created: CheckCircle,
        approved: CheckCircle,
        updated: Activity,
        login: Monitor,
        logout: Monitor,
        printed: FileText,
        deleted: XCircle,
        rejected: XCircle,
    };
    return icons[action] || Terminal;
};

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<template>
    <Head title="Log Aktivitas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 lg:p-10 w-full">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Log Aktivitas</h1>
                    <p class="text-slate-500">Pantau riwayat aktivitas dan perubahan sistem</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                    <Database class="w-4 h-4" />
                    <span class="font-medium text-slate-900">{{ logs.total }}</span> Total Log
                </div>
            </div>

            <!-- Filters & Toolbar -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                <div class="flex flex-1 items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:max-w-xs">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500" />
                        <Input
                            v-model="search"
                            placeholder="Cari deskripsi..."
                            class="pl-9 bg-white"
                        />
                    </div>
                    <div class="w-[180px]">
                        <select
                            v-model="selectedAction"
                            class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">Semua Aksi</option>
                            <option v-for="act in actions" :key="act" :value="act">{{ getActionLabel(act) }}</option>
                        </select>
                    </div>
                </div>
                
                <Button 
                    v-if="search || selectedAction" 
                    variant="ghost" 
                    @click="resetFilters"
                    class="text-slate-500 hover:text-slate-900"
                >
                    Reset Filter
                </Button>
            </div>

            <!-- Main Table -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <DataTable>
                    <thead class="bg-slate-50/80">
                        <TableRow class="hover:bg-transparent">
                            <TableHeader class="w-[50px] pl-6 text-center">#</TableHeader>
                            <TableHeader class="w-[200px]">User</TableHeader>
                            <TableHeader class="w-[140px]">Aksi</TableHeader>
                            <TableHeader>Deskripsi & Objek</TableHeader>
                            <TableHeader class="w-[140px]">IP Address</TableHeader>
                            <TableHeader class="w-[180px] text-right pr-6">Waktu</TableHeader>
                        </TableRow>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <TableRow v-for="(log, index) in logs.data" :key="log.id" class="group hover:bg-slate-50/50 transition-colors">
                             <TableCell class="pl-6 text-center text-xs text-slate-400">
                                {{ logs.from + index }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <Avatar class="h-8 w-8 border border-slate-100 bg-slate-100">
                                        <AvatarFallback class="bg-slate-100 text-slate-600 text-[10px] font-bold">
                                            {{ getInitials(log.user) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span class="text-sm font-medium text-slate-700">{{ log.user }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge 
                                    variant="outline" 
                                    :class="getActionColor(log.action)"
                                    class="gap-1.5 px-2.5 py-0.5 shadow-sm"
                                >
                                    <component :is="getActionIcon(log.action)" class="w-3 h-3" />
                                    {{ getActionLabel(log.action) }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-sm text-slate-700 font-medium">{{ log.description }}</span>
                                    <span v-if="log.model_type" class="text-[10px] font-mono text-slate-400 flex items-center gap-1">
                                        <Database class="w-3 h-3" />
                                        {{ log.model_type.split('\\').pop() }} #{{ log.model_id }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-1.5 text-xs font-mono text-slate-500 bg-slate-50 px-2 py-1 rounded w-fit">
                                    <Monitor class="w-3 h-3" />
                                    {{ log.ip_address || 'Unknown' }}
                                </div>
                            </TableCell>
                            <TableCell class="text-right pr-6">
                                <div class="flex flex-col items-end gap-0.5">
                                    <span class="text-sm font-medium text-slate-700">{{ log.time_ago }}</span>
                                    <span class="text-[10px] text-slate-400 flex items-center gap-1">
                                        <Clock class="w-3 h-3" />
                                        {{ log.created_at }}
                                    </span>
                                </div>
                            </TableCell>
                        </TableRow>

                        <!-- Empty State -->
                        <TableRow v-if="logs.data.length === 0">
                            <TableCell colspan="6" class="h-[400px] text-center p-0">
                                <div class="flex flex-col items-center justify-center h-full text-slate-400">
                                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <LayoutDashboard class="h-8 w-8 opacity-50" />
                                    </div>
                                    <p class="font-medium text-slate-900">Belum ada aktivitas</p>
                                    <p class="text-sm mt-1">Belum ada log aktivitas yang tercatat sistem.</p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </tbody>

                    <template #pagination>
                        <Pagination :pagination="logs" />
                    </template>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
