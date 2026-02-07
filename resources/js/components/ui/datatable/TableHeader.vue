<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <th
        :class="cn(
            'h-12 px-4 text-left align-middle font-bold text-slate-500 uppercase text-[11px] tracking-wider border-b border-slate-100 bg-slate-50/80 [&:has([role=checkbox])]:pr-0',
            props.class
        )"
    >
        <div class="flex items-center gap-2">
            <slot />
        </div>
    </th>
</template>
