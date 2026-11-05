<script setup lang="ts">
import {
    CalendarDays,
    Globe,
    LayoutGrid,
    Stethoscope,
    UserRound,
} from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { home } from '@/routes';
import { dashboard as accountDashboard } from '@/routes/account';
import { dashboard } from '@/routes/admin';
import { index as events } from '@/routes/admin/events';
import { index as specializations } from '@/routes/admin/specializations';
import type { BreadcrumbItem, SidebarNavigation } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

// Computed, because translations finish loading after this persistent layout is set up.
const navigation = computed<SidebarNavigation>(() => ({
    title: trans('Admin panel'),
    label: trans('Administration'),
    homeHref: dashboard(),
    items: [
        {
            title: trans('Dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
            exact: true,
        },
        {
            title: trans('Events'),
            href: events(),
            icon: CalendarDays,
        },
        {
            title: trans('Specializations'),
            href: specializations(),
            icon: Stethoscope,
        },
    ],
    footerItems: [
        {
            title: trans('User panel'),
            href: accountDashboard(),
            icon: UserRound,
        },
        {
            title: trans('Website'),
            href: home(),
            icon: Globe,
        },
    ],
}));
</script>

<template>
    <AppSidebarLayout :navigation="navigation" :breadcrumbs="breadcrumbs">
        <slot />
    </AppSidebarLayout>
</template>
