<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import { computed } from 'vue';

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

const props = defineProps<{
    pagination: CommonPagination;
}>();

// Smart page windowing: show max 5 page numbers with ellipsis
const visiblePages = computed(() => {
    const current = props.pagination.current_page;
    const last = props.pagination.last_page;
    const pages: (number | 'ellipsis-start' | 'ellipsis-end')[] = [];

    if (last <= 5) {
        // Show all pages if 5 or fewer
        for (let i = 1; i <= last; i++) pages.push(i);
    } else {
        // Always show first page
        pages.push(1);

        if (current > 3) {
            pages.push('ellipsis-start');
        }

        // Pages around current
        const start = Math.max(2, current - 1);
        const end = Math.min(last - 1, current + 1);

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        if (current < last - 2) {
            pages.push('ellipsis-end');
        }

        // Always show last page
        pages.push(last);
    }

    return pages;
});

// Helper to get URL for a specific page number
const getPageUrl = (page: number) => {
    // Find the link for this page from Laravel's links array
    const link = props.pagination.links.find(
        l => !l.label.includes('Previous') && !l.label.includes('Next') &&
             !l.label.includes('&laquo;') && !l.label.includes('&raquo;') &&
             l.label.trim() === String(page)
    );
    if (link?.url) return link.url;

    // Fallback: construct URL from first/last page link
    const anyLink = props.pagination.links.find(l => l.url);
    if (anyLink?.url) {
        const url = new URL(anyLink.url);
        url.searchParams.set('page', String(page));
        return url.pathname + url.search;
    }
    return null;
};

// Get prev/next URLs from Laravel links
const prevUrl = computed(() => props.pagination.links[0]?.url ?? null);
const nextUrl = computed(() => props.pagination.links[props.pagination.links.length - 1]?.url ?? null);
const firstUrl = computed(() => getPageUrl(1));
const lastUrl = computed(() => getPageUrl(props.pagination.last_page));
</script>

<template>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Info -->
        <p class="text-xs text-slate-500 tabular-nums">
            <span class="font-semibold text-slate-700">{{ pagination.from || 0 }}</span>
            –
            <span class="font-semibold text-slate-700">{{ pagination.to || 0 }}</span>
            dari
            <span class="font-semibold text-slate-700">{{ pagination.total }}</span>
        </p>

        <!-- Controls -->
        <div class="flex items-center gap-1" v-if="pagination.last_page > 1">
            <!-- First Page -->
            <Link
                v-if="pagination.current_page > 2 && pagination.last_page > 5 && firstUrl"
                :href="firstUrl"
                preserve-scroll
                preserve-state
                class="h-8 w-8 flex items-center justify-center rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                title="Halaman pertama"
            >
                <ChevronsLeft class="w-4 h-4" />
            </Link>

            <!-- Previous -->
            <Link
                v-if="prevUrl"
                :href="prevUrl"
                preserve-scroll
                preserve-state
                class="h-8 w-8 flex items-center justify-center rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                title="Sebelumnya"
            >
                <ChevronLeft class="w-4 h-4" />
            </Link>
            <div
                v-else
                class="h-8 w-8 flex items-center justify-center rounded-md text-slate-200 cursor-not-allowed"
            >
                <ChevronLeft class="w-4 h-4" />
            </div>

            <!-- Page Numbers -->
            <template v-for="(page, i) in visiblePages" :key="i">
                <!-- Ellipsis -->
                <span
                    v-if="page === 'ellipsis-start' || page === 'ellipsis-end'"
                    class="h-8 w-8 flex items-center justify-center text-slate-300 text-xs select-none"
                >
                    •••
                </span>

                <!-- Active Page -->
                <span
                    v-else-if="page === pagination.current_page"
                    class="h-8 min-w-[32px] px-2 flex items-center justify-center rounded-md text-xs font-bold bg-slate-900 text-white shadow-sm"
                >
                    {{ page }}
                </span>

                <!-- Inactive Page -->
                <Link
                    v-else-if="getPageUrl(page as number)"
                    :href="getPageUrl(page as number)!"
                    preserve-scroll
                    preserve-state
                    class="h-8 min-w-[32px] px-2 flex items-center justify-center rounded-md text-xs font-medium text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors"
                >
                    {{ page }}
                </Link>
            </template>

            <!-- Next -->
            <Link
                v-if="nextUrl"
                :href="nextUrl"
                preserve-scroll
                preserve-state
                class="h-8 w-8 flex items-center justify-center rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                title="Selanjutnya"
            >
                <ChevronRight class="w-4 h-4" />
            </Link>
            <div
                v-else
                class="h-8 w-8 flex items-center justify-center rounded-md text-slate-200 cursor-not-allowed"
            >
                <ChevronRight class="w-4 h-4" />
            </div>

            <!-- Last Page -->
            <Link
                v-if="pagination.current_page < pagination.last_page - 1 && pagination.last_page > 5 && lastUrl"
                :href="lastUrl"
                preserve-scroll
                preserve-state
                class="h-8 w-8 flex items-center justify-center rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                title="Halaman terakhir"
            >
                <ChevronsRight class="w-4 h-4" />
            </Link>
        </div>
    </div>
</template>
