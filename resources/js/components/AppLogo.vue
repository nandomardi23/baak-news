<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/20"
    >
        <AppLogoIcon class="size-6 fill-current" />
    </div>
    <div class="ml-3 grid flex-1 text-left leading-tight">
        <span class="font-bold text-slate-800 text-base">BAAK News</span>
        <span class="text-xs text-slate-500 font-medium tracking-wide">Administrator</span>
    </div>
</template>
