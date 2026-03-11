<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Command, CommandEmpty, CommandGroup, CommandItem, CommandList } from '@/components/ui/command';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

export interface Option {
    label: string | number;
    value: string | number;
}

const props = defineProps<{
    modelValue?: string | number | null;
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
    emptyText?: string;
    widthClass?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number | null): void;
}>();

const open = ref(false);
const searchQuery = ref('');

// Manual filtering for maximum reliability
const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    const q = searchQuery.value.toLowerCase();
    return props.options.filter(opt => 
        String(opt.label).toLowerCase().includes(q) || 
        String(opt.value).toLowerCase().includes(q)
    );
});

// Clear search when closed
watch(open, (val) => {
    if (!val) searchQuery.value = '';
});

const isActive = computed(() => {
    return props.modelValue !== null && props.modelValue !== undefined && props.modelValue !== '' && props.modelValue !== 'all';
});

const onSelect = (val: string | number) => {
    emit('update:modelValue', val === props.modelValue ? null : val);
    open.value = false;
};

const selectedLabel = computed(() => {
    return props.options.find((opt) => String(opt.value) === String(props.modelValue))?.label;
});

const popoverWidthClass = computed(() => {
    if (!props.widthClass) return 'w-[200px]';
    // Remove any height classes (like h-10, h-full, etc.) from being applied to PopoverContent
    return props.widthClass.split(' ').filter((c: string) => !c.startsWith('h-')).join(' ');
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                :class="cn(
                    'justify-between font-medium transition-all duration-200 rounded-xl px-3 border-slate-200 shadow-sm hover:shadow-md hover:border-blue-300 h-11',
                    isActive ? 'bg-blue-50/80 border-blue-200 ring-1 ring-blue-100/50 shadow-blue-100/20' : 'bg-white',
                    !modelValue || modelValue === 'all' ? 'text-slate-500 font-normal' : 'text-blue-700 font-bold',
                    widthClass || 'w-[200px]'
                )"
            >
                <span class="truncate block text-left flex-1">
                    {{ selectedLabel || placeholder || 'Pilih...' }}
                </span>
                <div class="flex items-center gap-1.5 ml-2 shrink-0">
                    <div v-if="isActive" class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                    <ChevronsUpDown class="h-4 w-4 opacity-50 text-slate-400 group-hover:text-blue-500 transition-colors" />
                </div>
            </Button>
        </PopoverTrigger>
        <PopoverContent 
            :class="cn('p-0 rounded-xl overflow-hidden shadow-xl border-slate-200 bg-white', popoverWidthClass)" 
            align="start"
            :side-offset="4"
        >
            <div class="flex flex-col h-full bg-white">
                <!-- Stable Search Header -->
                <div class="relative group border-b border-slate-100">
                    <Search class="absolute left-3 top-3.5 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors" />
                    <Input
                        v-model="searchQuery"
                        :placeholder="searchPlaceholder || 'Cari...'"
                        class="pl-10 h-11 border-none rounded-none focus-visible:ring-0 bg-transparent text-sm placeholder:text-slate-400"
                    />
                </div>

                <div class="max-h-[300px] overflow-y-auto overflow-x-hidden p-1.5">
                    <div v-if="filteredOptions.length === 0" class="py-8 px-4 flex flex-col items-center justify-center text-slate-400">
                        <Search class="size-8 mb-2 opacity-20" />
                        <p class="text-xs text-center">{{ emptyText || 'Data tidak ditemukan.' }}</p>
                    </div>

                    <div v-else class="flex flex-col gap-0.5">
                        <button
                            v-for="option in filteredOptions"
                            :key="String(option.value)"
                            type="button"
                            @click="onSelect(option.value)"
                            :class="cn(
                                'flex items-center gap-2 rounded-lg py-2 px-3 text-sm transition-all duration-200 cursor-pointer w-full text-left outline-none',
                                String(modelValue) === String(option.value) 
                                    ? 'bg-blue-50 text-blue-700 font-bold' 
                                    : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900 group'
                            )"
                        >
                            <span class="truncate block flex-1">{{ option.label }}</span>
                            <Check
                                :class="cn(
                                    'h-4 w-4 shrink-0 text-blue-600 transition-opacity',
                                    String(modelValue) === String(option.value) ? 'opacity-100' : 'opacity-0'
                                )"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>

