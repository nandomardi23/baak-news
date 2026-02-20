<script setup lang="ts">
interface SyncType {
    type: string;
    label: string;
    description: string;
    icon: string;
    color: string;
}

interface SyncState {
    loading: boolean;
    result: { success: boolean; message?: string } | null;
}

interface AccumulatedStat {
    total_synced: number;
    total_failed: number;
    total_api: number | null;
    progress: number;
    errors: string[];
}

const props = defineProps<{
    syncType: SyncType;
    syncState: SyncState | undefined;
    accumulatedStat: AccumulatedStat | undefined;
    disabled: boolean;
}>();

const emit = defineEmits<{
    (e: 'sync'): void;
    (e: 'showErrors', title: string, errors: string[]): void;
}>();
</script>

<template>
    <div
        :class="[
            'group relative flex flex-col rounded-2xl border bg-card/50 backdrop-blur-sm p-4 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-indigo-300',
            syncState?.loading ? 'ring-2 ring-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/10' : ''
        ]"
    >
        <!-- Card Header -->
        <div class="flex items-start justify-between mb-3">
            <div :class="['flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br text-white shadow-lg shrink-0', syncType.color]">
                <span class="text-xl">{{ syncType.icon }}</span>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center gap-2">
                 <!-- Error Button -->
                <button
                    v-if="(accumulatedStat?.errors?.length ?? 0) > 0"
                    @click="emit('showErrors', syncType.label, accumulatedStat?.errors ?? [])"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-red-500 hover:bg-red-50 transition-all tooltip"
                    title="Lihat Error"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>

                <!-- Sync Button -->
                <button
                    @click="emit('sync')"
                    :disabled="syncState?.loading || disabled"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/80 hover:bg-indigo-500 hover:text-white transition-all disabled:opacity-30"
                >
                <svg v-if="syncState?.loading" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>
        </div>

        <!-- Content -->
        <div class="flex-1 space-y-1">
            <h4 class="font-bold tracking-tight text-sm">{{ syncType.label }}</h4>
            <p class="text-[10px] leading-tight text-muted-foreground line-clamp-2">
                {{ syncType.description }}
            </p>
        </div>

        <!-- Progress Detail -->
        <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
            <div v-if="syncState?.loading" class="space-y-1.5">
                <div class="flex justify-between text-[9px] font-medium">
                    <span class="text-indigo-600 animate-pulse">{{ accumulatedStat?.total_synced || 0 }} synced</span>
                    <span>{{ accumulatedStat?.progress || 0 }}%</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1 overflow-hidden">
                    <div 
                        class="h-1 rounded-full bg-linear-to-r from-indigo-500 to-purple-500 transition-all duration-500"
                        :style="{ width: (accumulatedStat?.progress || 0) + '%' }"
                    ></div>
                </div>
            </div>

            <div v-else-if="syncState?.result" class="space-y-1.5">
                <div class="flex items-center justify-between text-[10px]">
                    <div class="flex items-center gap-1 font-bold" :class="syncState?.result?.success ? 'text-emerald-600' : 'text-red-500'">
                        <svg v-if="syncState?.result?.success" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ syncState?.result?.success ? 'Selesai' : 'Gagal' }}
                    </div>
                    <span class="text-[9px] text-muted-foreground font-mono">
                        {{ accumulatedStat?.total_synced || 0 }}/{{ accumulatedStat?.total_api ?? '?' }}
                    </span>
                </div>
            </div>

            <div v-else class="text-[9px] text-muted-foreground italic text-center">
                Ready
            </div>
        </div>
    </div>
</template>
