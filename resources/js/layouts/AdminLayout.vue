<script setup lang="ts">
import { Globe, LayoutGrid } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { home } from '@/routes';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem, SidebarNavigation } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const navigation: SidebarNavigation = {
    label: trans('Administration'),
    homeHref: dashboard(),
    items: [
        {
            title: trans('Dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
            exact: true,
        },
    ],
    footerItems: [
        {
            title: trans('Website'),
            href: home(),
            icon: Globe,
        },
    ],
};
</script>

<template>
    <AppSidebarLayout :navigation="navigation" :breadcrumbs="breadcrumbs">
        <slot />
    </AppSidebarLayout>
</template>
