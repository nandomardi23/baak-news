<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface LinkType {
    url: string | null;
    label: string;
    active: boolean;
}

interface CommonPagination {
    links: LinkType[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

defineProps<{
    pagination: CommonPagination;
}>();
</script>

<template>
    <div class="flex items-center justify-between">
        <div class="text-xs text-slate-500">
            Menampilkan <span class="font-medium text-slate-900">{{ pagination.from || 0 }}</span> sampai <span class="font-medium text-slate-900">{{ pagination.to || 0 }}</span> dari <span class="font-medium text-slate-900">{{ pagination.total }}</span> data
        </div>
        <div class="flex items-center gap-1">
            <template v-for="(link, i) in pagination.links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="h-8 min-w-[32px] px-3 flex items-center justify-center rounded-lg text-xs font-medium transition-colors"
                    :class="[
                        link.active 
                            ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30' 
                            : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                    ]"
                >
                    <span v-if="link.label.includes('&laquo;')"><ChevronLeft class="size-3.5" /></span>
                    <span v-else-if="link.label.includes('&raquo;')"><ChevronRight class="size-3.5" /></span>
                    <span v-else>{{ link.label }}</span>
                </Link>
                <div 
                    v-else 
                    class="h-8 min-w-[32px] px-3 flex items-center justify-center rounded-lg text-xs font-medium border border-slate-100 text-slate-300 bg-slate-50 cursor-not-allowed"
                >
                    <span v-if="link.label.includes('&laquo;')"><ChevronLeft class="size-3.5" /></span>
                    <span v-else-if="link.label.includes('&raquo;')"><ChevronRight class="size-3.5" /></span>
                    <span v-else>{{ link.label }}</span>
                </div>
            </template>
        </div>
    </div>
</template>
