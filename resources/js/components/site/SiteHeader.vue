<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { home, login, register } from '@/routes';
import { dashboard } from '@/routes/account';
import { index as upcomingEvents, past as pastEvents } from '@/routes/events';
import type { NavItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isCurrentUrl } = useCurrentUrl();

// Computed, because translations finish loading after this persistent layout is set up.
const navItems = computed<NavItem[]>(() => [
    {
        title: trans('Upcoming events'),
        href: upcomingEvents(),
    },
    {
        title: trans('Past events'),
        href: pastEvents(),
    },
]);
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-sidebar-border/80 bg-background"
    >
        <div class="mx-auto flex h-16 items-center gap-2 px-4 md:max-w-7xl">
            <Sheet>
                <SheetTrigger :as-child="true">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="lg:hidden"
                        :aria-label="$t('Navigation menu')"
                    >
                        <Menu class="size-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="left" class="w-[300px] p-6">
                    <SheetTitle class="sr-only">
                        {{ $t('Navigation menu') }}
                    </SheetTitle>
                    <nav class="mt-6 flex flex-col gap-1">
                        <Button
                            v-for="item in navItems"
                            :key="item.title"
                            :variant="
                                isCurrentUrl(item.href) ? 'secondary' : 'ghost'
                            "
                            class="justify-start"
                            as-child
                        >
                            <Link :href="item.href">{{ item.title }}</Link>
                        </Button>
                    </nav>
                </SheetContent>
            </Sheet>

            <Link :href="home()" class="flex items-center gap-x-2">
                <AppLogo />
            </Link>

            <nav class="ml-8 hidden items-center gap-1 lg:flex">
                <Button
                    v-for="item in navItems"
                    :key="item.title"
                    :variant="isCurrentUrl(item.href) ? 'secondary' : 'ghost'"
                    as-child
                >
                    <Link :href="item.href">{{ item.title }}</Link>
                </Button>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <template v-if="user">
                    <Button
                        variant="ghost"
                        as-child
                        class="hidden sm:inline-flex"
                    >
                        <Link :href="dashboard()">{{ $t('My account') }}</Link>
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </template>

                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="login()">{{ $t('Log in') }}</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">{{ $t('Register') }}</Link>
                    </Button>
                </template>
            </div>
        </div>
    </header>
</template>
