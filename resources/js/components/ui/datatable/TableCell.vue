<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <td
        :class="cn(
            'p-4 align-middle [&:has([role=checkbox])]:pr-0 text-slate-700 text-sm font-medium border-r border-slate-50 last:border-r-0',
            props.class
        )"
    >
        <slot />
    </td>
</template>
