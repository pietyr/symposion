<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { SidebarNavigation } from '@/types';

defineProps<{
    navigation: SidebarNavigation;
}>();
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="navigation.homeHref">
                            <AppLogo :title="navigation.title" />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :label="navigation.label" :items="navigation.items" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="navigation.footerItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
