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
import BiometricLock from '@/Components/UI/BiometricLock.vue';
import NotificationToast from '@/Components/UI/NotificationToast.vue';

onMounted(() => {
    const user = usePage().props.auth.user;
    if (user && window.Echo) {
        window.Echo.private(`user.${user.id}`)
            .listen('.budget.exceeded', (e: any) => {
                // Now we can use the notification system via events or state
                // For now, let's just make sure the component is there
            });
    }
});
</script>

<template>
    <BiometricLock />
    <NotificationToast />
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
