<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

onMounted(() => {
    const user = usePage().props.auth.user;
    if (user && window.Echo) {
        window.Echo.private(`user.${user.id}`)
            .listen('.budget.exceeded', (e: any) => {
                alert(e.message); // Placeholder for a better toast later
            });
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
