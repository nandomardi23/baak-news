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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
    SheetClose,
} from '@/components/ui/sheet';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    align?: 'left' | 'center' | 'right';
    render?: (row: any) => any;
}

export interface ActiveFilter {
    key: string;
    label: string;
    valueLabel: string;
}

interface PaginationData {
    data: any[];
    links: any[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page?: number;
}

const props = defineProps<{
    data: PaginationData;
    columns: Column[];
    search?: string;
    filters?: Record<string, any>;
    sortField?: string;
    sortDirection?: 'asc' | 'desc';
    title?: string;
    perPage?: number | string;
    activeFilters?: ActiveFilter[];
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

// Local state for per page
const localPerPage = ref(String(props.perPage || props.data?.per_page || 10));

watch(() => props.data?.per_page, (newVal) => {
    if (newVal) localPerPage.value = String(newVal);
});

const onPerPageChange = (val: string | null | any) => {
    if (val) {
        localPerPage.value = String(val);
        updateParams({ per_page: val, page: null }); // reset page
    }
};

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

    // Reset to page 1 on filter/search/per_page change
    if ('search' in newParams || Object.keys(props.filters || {}).some(k => k in newParams) || 'per_page' in newParams) {
        currentParams.delete('page');
    }

    router.get(window.location.pathname, Object.fromEntries(currentParams), {
        preserveState: true,
        preserveScroll: true,
    });
};

const removeSpecificFilter = (key: string) => {
    updateParams({ [key]: undefined, page: 1 });
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

// Calculate how many specific filters (excluding search) are active
const activeFilterCount = computed(() => {
    if (!props.filters) return 0;
    return Object.values(props.filters).filter(v => v !== null && v !== '' && v !== 'all').length;
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
            <div class="flex flex-col gap-4 w-full">
                <!-- Top Row: Title & Main Actions/Search -->
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between w-full">
                    <!-- Left Side: Title or Filter Badge -->
                    <div class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-2xl bg-linear-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-200/50">
                            <Filter class="w-5 h-5 transition-transform group-hover:scale-110" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 leading-tight uppercase tracking-tight">{{ title || 'Data Table' }}</h3>
                            <p v-if="hasActiveFilters" class="text-[10px] font-bold text-blue-500 uppercase tracking-wider">Filter Aktif</p>
                            <p v-else class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Semua Data</p>
                        </div>
                    </div>

                    <!-- Right Side: Actions, Custom Filters, Search -->
                    <div class="flex flex-col sm:flex-row gap-3 items-center w-full sm:w-auto">
                        <!-- Actions Slot (Export, Create, etc) -->
                        <slot name="actions" />

                        <!-- Custom Filters Drawer Slot -->
                        <Sheet v-if="$slots.filters">
                            <SheetTrigger as-child>
                                <Button 
                                    variant="outline" 
                                    class="gap-2 relative rounded-xl border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 group overflow-hidden h-10 px-4"
                                >
                                    <div class="absolute inset-0 bg-linear-to-br from-blue-600/5 to-indigo-600/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <Filter class="w-4 h-4 text-blue-600 group-hover:scale-110 transition-transform" />
                                    <span class="hidden sm:inline font-bold text-slate-700">Filter</span>
                                    <!-- Badge for active filters count -->
                                    <span v-if="activeFilterCount > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[10px] font-bold text-white shadow-lg ring-2 ring-white animate-in zoom-in duration-300">
                                        {{ activeFilterCount }}
                                    </span>
                                </Button>
                            </SheetTrigger>
                            <SheetContent class="w-full sm:max-w-md overflow-y-auto p-0 flex flex-col border-none shadow-2xl">
                                <div class="p-6 pb-0">
                                    <SheetHeader class="mb-8 p-6 rounded-3xl bg-linear-to-br from-blue-600 to-indigo-700 text-white relative overflow-hidden shadow-xl shadow-blue-100">
                                        <div class="absolute -right-4 -top-4 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                                        <SheetTitle class="text-2xl font-black text-white flex items-center gap-3">
                                            <div class="p-2 rounded-xl bg-white/20 backdrop-blur-md border border-white/20">
                                                <Filter class="w-5 h-5 text-white" />
                                            </div>
                                            Filter Data
                                        </SheetTitle>
                                        <SheetDescription class="text-blue-100 mt-2 font-medium">
                                            Sesuaikan tampilan data dengan filter di bawah ini.
                                        </SheetDescription>
                                    </SheetHeader>
                                </div>
                                
                                <div class="flex-1 px-8 py-2 overflow-y-auto">
                                    <div class="flex flex-col gap-8 py-4">
                                        <!-- Render the scoped filters passed by parents -->
                                        <slot name="filters" />
                                    </div>
                                </div>

                                <div class="p-8 mt-auto flex items-center justify-between gap-4 bg-slate-50 border-t border-slate-100">
                                    <Button 
                                        v-if="hasActiveFilters" 
                                        type="button" 
                                        variant="ghost" 
                                        class="text-slate-500 hover:text-red-600 hover:bg-white rounded-xl px-4 transition-colors font-semibold"
                                        @click="clearFilters"
                                    >
                                        Reset Semua
                                    </Button>
                                    <div v-else></div>

                                    <SheetClose as-child>
                                        <Button type="button" class="bg-blue-600 hover:bg-blue-700 text-white gap-2 rounded-xl px-8 h-12 shadow-lg shadow-blue-200 font-bold transition-all hover:scale-[1.02] active:scale-95">
                                            Terapkan
                                        </Button>
                                    </SheetClose>
                                </div>
                            </SheetContent>
                        </Sheet>

                        <!-- Per Page Select -->
                        <div class="w-[110px] shrink-0">
                            <Select :model-value="localPerPage" @update:model-value="onPerPageChange">
                                <SelectTrigger class="h-10 rounded-xl border-slate-200 shadow-sm transition-all hover:border-blue-200">
                                    <SelectValue placeholder="Baris" />
                                </SelectTrigger>
                                <SelectContent class="rounded-xl shadow-xl border-slate-200">
                                    <SelectItem value="5">5 Baris</SelectItem>
                                    <SelectItem value="10">10 Baris</SelectItem>
                                    <SelectItem value="25">25 Baris</SelectItem>
                                    <SelectItem value="50">50 Baris</SelectItem>
                                    <SelectItem value="100">100 Baris</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full sm:w-64 group">
                            <Search class="absolute left-3.5 top-3 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
                            <Input
                                v-model="localSearch"
                                type="text"
                                placeholder="Cari data..."
                                class="pl-11 h-10 w-full rounded-xl border-slate-200 shadow-sm focus-visible:ring-blue-500 transition-all hover:border-blue-200 bg-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Filter Chips -->
                <div v-if="activeFilters && activeFilters.length > 0" class="flex flex-wrap gap-2 items-center py-2 px-1 border-t border-slate-50/50">
                    <div 
                        v-for="filter in activeFilters" 
                        :key="filter.key"
                        class="flex items-center gap-1.5 bg-blue-50/50 border border-blue-100/50 px-3 py-1.5 rounded-full text-xs font-bold text-blue-700 shadow-sm hover:bg-blue-50 transition-colors group animate-in fade-in slide-in-from-left-2 duration-300"
                    >
                        <span class="opacity-60 font-medium">{{ filter.label }}:</span>
                        <span>{{ filter.valueLabel }}</span>
                        <button 
                            @click="removeSpecificFilter(filter.key)" 
                            class="ml-1 p-0.5 hover:bg-blue-100 rounded-full transition-colors"
                        >
                            <X class="w-3 h-3 text-blue-400 group-hover:text-blue-600" />
                        </button>
                    </div>

                    <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="clearFilters"
                        class="h-7 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-full px-3 transition-all"
                    >
                        Reset
                    </Button>
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
