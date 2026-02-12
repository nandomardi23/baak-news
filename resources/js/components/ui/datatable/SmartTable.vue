<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    DataTable,
    TableHeader,
    TableRow,
    TableCell,
    Pagination
} from '@/components/ui/datatable';
import { ArrowUpDown, ArrowUp, ArrowDown, Search, Filter, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { watchDebounced } from '@vueuse/core';
import { ref, watch } from 'vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    align?: 'left' | 'center' | 'right';
    render?: (row: any) => any;
}

interface PaginationData {
    data: any[];
    links: any[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
}

const props = defineProps<{
    data: PaginationData;
    columns: Column[];
    search?: string;
    filters?: Record<string, any>;
    sortField?: string;
    sortDirection?: 'asc' | 'desc';
    title?: string;
}>();

const emit = defineEmits(['update:search', 'update:filters', 'sort']);

// Local state for search to allow debouncing
const localSearch = ref(props.search || '');

// Sync local search with prop if it changes externally
watch(() => props.search, (newVal) => {
    localSearch.value = newVal || '';
});

// Debounce search updates to URL
watchDebounced(
    localSearch,
    (value) => {
        updateParams({ search: value });
    },
    { debounce: 500, maxWait: 1000 }
);

const updateParams = (newParams: Record<string, any>) => {
    const currentParams = new URLSearchParams(window.location.search);
    
    // Update or remove params
    Object.entries(newParams).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') {
            currentParams.delete(key);
        } else {
            currentParams.set(key, String(value));
        }
    });

    // Reset to page 1 on filter/search change
    if ('search' in newParams || Object.keys(props.filters || {}).some(k => k in newParams)) {
        currentParams.delete('page');
    }

    router.get(window.location.pathname, Object.fromEntries(currentParams), {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSort = (field: string) => {
    if (!props.columns.find(c => c.key === field)?.sortable) return;

    let direction = 'asc';
    if (props.sortField === field && props.sortDirection === 'asc') {
        direction = 'desc';
    }

    updateParams({
        sort_field: field,
        sort_direction: direction,
    });
};

const clearFilters = () => {
    localSearch.value = '';
    
    // Reset all filters in URL
    const params = new URLSearchParams(window.location.search);
    params.delete('search');
    params.delete('sort_field');
    params.delete('sort_direction');
    
    if (props.filters) {
        Object.keys(props.filters).forEach(key => params.delete(key));
    }

    router.get(window.location.pathname, Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
};

const hasActiveFilters = computed(() => {
    return !!localSearch.value || 
           (props.filters && Object.values(props.filters).some(v => v !== null && v !== '' && v !== 'all'));
});

// Helper for alignment classes
const getAlignClass = (align?: string) => {
    switch (align) {
        case 'center': return 'text-center';
        case 'right': return 'text-right';
        default: return 'text-left';
    }
};
</script>

<template>
    <DataTable class="w-full">
        <template #toolbar>
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between w-full">
                <!-- Left Side: Title or Filter Badge -->
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <Filter class="w-4 h-4" />
                    </div>
                    <h3 class="text-base font-bold text-slate-700">{{ title || 'Data Table' }}</h3>
                    
                    <!-- Clear Filters Button -->
                    <Button 
                        v-if="hasActiveFilters"
                        variant="ghost" 
                        size="sm" 
                        @click="clearFilters"
                        class="text-red-600 hover:text-red-700 hover:bg-red-50 h-8 text-xs gap-1 ml-2"
                    >
                        <X class="w-3.5 h-3.5" />
                        Reset
                    </Button>
                </div>

                <!-- Right Side: Actions, Custom Filters, Search -->
                <div class="flex flex-col sm:flex-row gap-3 items-center w-full sm:w-auto">
                    <!-- Actions Slot (Export, Create, etc) -->
                    <slot name="actions" />

                    <!-- Custom Filters Slot -->
                    <slot name="filters" />

                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400" />
                        <Input
                            v-model="localSearch"
                            type="text"
                            placeholder="Cari data..."
                            class="pl-9 h-9 w-full focus-visible:ring-1"
                        />
                    </div>
                </div>
            </div>
        </template>

        <thead class="bg-slate-50/50">
            <tr>
                <TableHeader 
                    v-for="col in columns" 
                    :key="col.key"
                    :class="[
                        col.class, 
                        col.sortable ? 'cursor-pointer hover:bg-slate-100' : '',
                        getAlignClass(col.align)
                    ]"
                    @click="col.sortable && handleSort(col.key)"
                >
                    <div class="flex items-center gap-1" :class="{'justify-center': col.align === 'center', 'justify-end': col.align === 'right'}">
                        {{ col.label }}
                        <span v-if="col.sortable" class="ml-1">
                            <ArrowUp v-if="sortField === col.key && sortDirection === 'asc'" class="h-3.5 w-3.5 text-blue-600" />
                            <ArrowDown v-else-if="sortField === col.key && sortDirection === 'desc'" class="h-3.5 w-3.5 text-blue-600" />
                            <ArrowUpDown v-else class="h-3.5 w-3.5 opacity-30" />
                        </span>
                    </div>
                </TableHeader>
            </tr>
        </thead>

        <tbody>
            <TableRow v-for="(row, index) in data.data" :key="row.id || index">
                <TableCell 
                    v-for="col in columns" 
                    :key="col.key"
                    :class="[col.class, getAlignClass(col.align)]"
                >
                    <!-- Scoped Slot for Custom Cell Content -->
                    <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                        <!-- Default Render -->
                         <span v-if="col.render" v-html="col.render(row)"></span>
                         <span v-else>{{ row[col.key] }}</span>
                    </slot>
                </TableCell>
            </TableRow>

            <!-- Empty State -->
            <TableRow v-if="data.data.length === 0">
                <TableCell :colspan="columns.length" class="h-64 text-center">
                    <div class="flex flex-col items-center justify-center text-slate-500">
                        <div class="bg-slate-100 p-4 rounded-full mb-3">
                            <Search class="h-6 w-6 text-slate-400" />
                        </div>
                        <p class="font-medium text-slate-900">Tidak ada data ditemukan</p>
                        <p class="text-sm">Coba ubah filter atau kata kunci pencarian.</p>
                        <Button 
                            variant="link" 
                            class="mt-2 text-blue-600" 
                            @click="clearFilters"
                        >
                            Reset Filter
                        </Button>
                    </div>
                </TableCell>
            </TableRow>
        </tbody>

        <template #pagination>
            <Pagination :pagination="data" />
        </template>
    </DataTable>
</template>
