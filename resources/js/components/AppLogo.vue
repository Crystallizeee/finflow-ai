<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-indigo-600 text-white shadow-lg shadow-indigo-500/20">
        <AppLogoIcon class="size-5 fill-current" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate font-black leading-none tracking-tight text-slate-900 dark:text-white">FinFlow <span class="text-indigo-600">AI</span></span>
    </div>
</template>
