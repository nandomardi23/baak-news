<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <div :class="cn('w-full', props.class)">
        <div class="w-full rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
            <!-- Header/Toolbar Slot -->
            <div v-if="$slots.toolbar" class="p-4 border-b border-slate-100 bg-white flex flex-col sm:flex-row gap-4 justify-between items-center">
                <slot name="toolbar" />
            </div>

            <!-- Table Container -->
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm text-left">
                    <slot />
                </table>
            </div>

            <!-- Pagination Slot -->
            <div v-if="$slots.pagination" class="p-4 border-t border-slate-100 bg-slate-50/50">
                <slot name="pagination" />
            </div>
        </div>
    </div>
</template>
