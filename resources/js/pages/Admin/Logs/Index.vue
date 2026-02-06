<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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
    };
    return labels[action] || action;
};
</script>

<template>
    <Head title="Log Aktivitas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Log Aktivitas</h1>
                    <p class="text-muted-foreground">Riwayat aktivitas sistem ({{ logs.total }} log)</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border bg-card shadow-sm p-4">
                <div class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium mb-1">Cari</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari deskripsi..."
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"
                        />
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-sm font-medium mb-1">Aksi</label>
                        <select
                            v-model="selectedAction"
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary bg-background"
                        >
                            <option value="">Semua Aksi</option>
                            <option v-for="act in actions" :key="act" :value="act">{{ getActionLabel(act) }}</option>
                        </select>
                    </div>
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 border rounded-lg hover:bg-muted"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Waktu</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">User</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Aksi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-muted-foreground">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-muted/50">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium">{{ log.time_ago }}</p>
                                    <p class="text-xs text-muted-foreground">{{ log.created_at }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ log.user }}</td>
                                <td class="px-6 py-4">
                                    <span :class="log.action_badge" class="px-2 py-1 rounded-full text-xs font-medium">
                                        {{ getActionLabel(log.action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm">{{ log.description }}</p>
                                    <p v-if="log.model_type" class="text-xs text-muted-foreground">
                                        {{ log.model_type }} #{{ log.model_id }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-foreground font-mono">
                                    {{ log.ip_address || '-' }}
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-muted-foreground">
                                    Belum ada log aktivitas
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.last_page > 1" class="px-6 py-4 border-t flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Halaman {{ logs.current_page }} dari {{ logs.last_page }}
                    </p>
                    <div class="flex gap-2">
                        <template v-for="link in logs.links" :key="link.label">
                            <button
                                v-if="link.url"
                                @click="router.get(link.url)"
                                :class="[
                                    'px-3 py-1 rounded border text-sm',
                                    link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="px-3 py-1 text-sm text-muted-foreground"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
