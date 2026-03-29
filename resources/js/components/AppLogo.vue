<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl overflow-hidden bg-white shadow-lg ring-1 ring-slate-200/50"
    >
        <AppLogoIcon class="h-full w-full object-contain" />
    </div>
    <div class="ml-3 grid flex-1 text-left leading-tight">
        <span class="font-bold text-slate-800 text-base">BAAK News</span>
        <span class="text-xs text-slate-500 font-medium tracking-wide">Administrator</span>
    </div>
</template>

