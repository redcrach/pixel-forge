<script setup lang="ts">
import AppLayout from '@/layouts/app/AppStudioLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout>
        <slot />
    </AppLayout>
</template>
