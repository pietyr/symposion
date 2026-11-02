<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { Toaster } from '@/components/ui/sonner';

const name = usePage().props.name;
const year = new Date().getFullYear();
</script>

<template>
    <AppShell variant="header">
        <SiteHeader />
        <AppContent variant="header" class="px-4 py-8">
            <slot />
        </AppContent>
        <footer class="border-t border-sidebar-border/80">
            <div
                class="mx-auto max-w-7xl px-4 py-6 text-sm text-muted-foreground"
            >
                &copy; {{ year }} {{ name }}
            </div>
        </footer>
        <Toaster />
    </AppShell>
</template>
