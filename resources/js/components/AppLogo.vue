<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-indigo-500 to-purple-600 shadow-lg ring-1 ring-white/10"
    >
        <AppLogoIcon class="size-6 fill-current" />
    </div>
    <div class="ml-3 grid flex-1 text-left leading-tight">
        <span class="font-bold text-slate-800 text-base">BAAK News</span>
        <span class="text-xs text-slate-500 font-medium tracking-wide">Administrator</span>
    </div>
</template>
