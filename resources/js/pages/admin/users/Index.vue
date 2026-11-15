<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import FilterSelect from '@/components/FilterSelect.vue';
import Heading from '@/components/Heading.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index } from '@/routes/admin/users';
import type {
    AdminUserResource,
    Paginated,
    UserFilterOptions,
    UserFilters,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Users',
                href: index(),
            },
        ],
    },
});

const props = defineProps<{
    users: Paginated<AdminUserResource>;
    filters: UserFilters;
    filterOptions: UserFilterOptions;
}>();

const dateFormatter = new Intl.DateTimeFormat('pl', {
    dateStyle: 'short',
    timeZone: 'Europe/Warsaw',
});

const search = ref(props.filters.search);

const hasFilters = computed(() =>
    Object.values(props.filters).some((value) => value !== ''),
);

const apply = (changes: Partial<UserFilters>): void => {
    const filters = { ...props.filters, search: search.value, ...changes };

    router.get(
        index.url(),
        Object.fromEntries(
            Object.entries(filters).filter(([, value]) => value !== ''),
        ),
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const clearFilters = (): void => {
    search.value = '';
    apply({ profession: '', state: '', search: '' });
};

// Waits for a pause in typing, so a word sends one request instead of one per letter.
watch(
    search,
    useDebounceFn(() => {
        if (search.value !== props.filters.search) {
            apply({});
        }
    }, 300),
);

const filterFields = computed(() => [
    {
        name: 'profession' as const,
        placeholder: 'All professions',
        items: props.filterOptions.professions,
    },
    {
        name: 'state' as const,
        placeholder: 'All accounts',
        items: props.filterOptions.states,
    },
]);
</script>

<template>
    <Head :title="$t('Users')" />

    <div class="flex flex-col gap-6 p-4">
        <Heading :title="$t('Users')" />

        <div class="flex flex-col gap-3">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <Input
                    v-model="search"
                    type="search"
                    :placeholder="$t('Search by name or email')"
                    :aria-label="$t('Search by name or email')"
                    data-test="user-search"
                />
                <FilterSelect
                    v-for="field in filterFields"
                    :key="field.name"
                    :model-value="filters[field.name]"
                    :placeholder="$t(field.placeholder)"
                    :items="field.items"
                    :test-id="`user-filter-${field.name}`"
                    @update:model-value="apply({ [field.name]: $event })"
                />
            </div>
            <div v-if="hasFilters">
                <Button variant="ghost" size="sm" @click="clearFilters">
                    {{ $t('Clear filters') }}
                </Button>
            </div>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ $t('User') }}</TableHead>
                    <TableHead>{{ $t('Profession') }}</TableHead>
                    <TableHead>{{ $t('Account') }}</TableHead>
                    <TableHead>{{ $t('Signed up at') }}</TableHead>
                    <TableHead class="text-right">{{
                        $t('Registrations')
                    }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="user in users.data"
                    :key="user.id"
                    data-test="user-row"
                >
                    <TableCell>
                        <div class="font-medium">{{ user.name }}</div>
                        <div class="text-xs text-muted-foreground">
                            {{ user.email }}
                        </div>
                    </TableCell>
                    <TableCell>{{ user.profession_label ?? '—' }}</TableCell>
                    <TableCell>
                        <div class="flex flex-wrap gap-1">
                            <Badge v-if="user.is_admin" variant="secondary">
                                {{ $t('Administrator') }}
                            </Badge>
                            <Badge v-if="!user.is_verified" variant="outline">
                                {{ $t('Unverified') }}
                            </Badge>
                        </div>
                    </TableCell>
                    <TableCell class="text-muted-foreground tabular-nums">
                        {{
                            user.created_at
                                ? dateFormatter.format(
                                      new Date(user.created_at),
                                  )
                                : ''
                        }}
                    </TableCell>
                    <TableCell class="text-right tabular-nums">
                        {{ user.registrations_count }}
                    </TableCell>
                </TableRow>
                <TableEmpty v-if="users.data.length === 0" :colspan="5">
                    {{
                        hasFilters
                            ? $t('No users match these filters.')
                            : $t('No users yet.')
                    }}
                </TableEmpty>
            </TableBody>
        </Table>

        <PaginationControls :paginator="users" />
    </div>
</template>
