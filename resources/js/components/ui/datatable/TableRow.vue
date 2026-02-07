<script setup lang="ts">
import { type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <tr
        :class="cn(
            'border-b border-slate-50 transition-colors hover:bg-slate-50/50 data-[state=selected]:bg-muted',
            props.class
        )"
    >
        <slot />
    </tr>
</template>
