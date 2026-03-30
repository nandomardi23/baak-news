<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';

defineProps<{
    message?: string;
}>();
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <div v-show="message" class="flex items-start gap-1.5 mt-1.5">
            <AlertCircle class="w-4 h-4 text-rose-500 shrink-0 mt-[1.5px]" />
            <p class="text-[13px] font-medium text-rose-600 dark:text-rose-400">
                {{ message }}
            </p>
        </div>
    </Transition>
</template>
