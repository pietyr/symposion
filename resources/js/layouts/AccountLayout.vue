<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Globe, LayoutGrid, Settings, Shield, Ticket } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { home } from '@/routes';
import { dashboard, settings } from '@/routes/account';
import { index as registrations } from '@/routes/account/registrations';
import { dashboard as adminDashboard } from '@/routes/admin';
import type { BreadcrumbItem, SidebarNavigation } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage();

// Computed, because translations finish loading after this persistent layout is set up.
const navigation = computed<SidebarNavigation>(() => ({
    title: trans('User panel'),
    homeHref: dashboard(),
    groups: [
        {
            label: trans('Account'),
            items: [
                {
                    title: trans('Overview'),
                    href: dashboard(),
                    icon: LayoutGrid,
                    exact: true,
                },
                {
                    title: trans('My registrations'),
                    href: registrations(),
                    icon: Ticket,
                },
                {
                    title: trans('Settings'),
                    href: settings(),
                    icon: Settings,
                },
            ],
        },
    ],
    footerItems: [
        ...(page.props.auth.user?.is_admin
            ? [
                  {
                      title: trans('Admin panel'),
                      href: adminDashboard(),
                      icon: Shield,
                  },
              ]
            : []),
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
