<script setup lang="ts">
import { Globe, LayoutGrid, Settings } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { home } from '@/routes';
import { dashboard, settings } from '@/routes/account';
import type { BreadcrumbItem, SidebarNavigation } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const navigation: SidebarNavigation = {
    label: trans('Account'),
    homeHref: dashboard(),
    items: [
        {
            title: trans('Overview'),
            href: dashboard(),
            icon: LayoutGrid,
            exact: true,
        },
        {
            title: trans('Settings'),
            href: settings(),
            icon: Settings,
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
