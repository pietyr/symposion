<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { home, login, register } from '@/routes';
import { dashboard } from '@/routes/account';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <header class="border-b border-sidebar-border/80">
        <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
            <Link :href="home()" class="flex items-center gap-x-2">
                <AppLogo />
            </Link>

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
