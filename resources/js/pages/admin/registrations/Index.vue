<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import RegistrationStatusBadge from '@/components/admin/registrations/RegistrationStatusBadge.vue';
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
import { usePriceFormat } from '@/composables/usePriceFormat';
import { index } from '@/routes/admin/registrations';
import type {
    Paginated,
    RegistrationFilterOptions,
    RegistrationFilters,
    RegistrationResource,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Registrations',
                href: index(),
            },
        ],
    },
});

const props = defineProps<{
    registrations: Paginated<RegistrationResource>;
    filters: RegistrationFilters;
    filterOptions: RegistrationFilterOptions;
}>();

const { formatPrice } = usePriceFormat();
const dateFormatter = new Intl.DateTimeFormat('pl', {
    dateStyle: 'short',
    timeStyle: 'short',
    timeZone: 'Europe/Warsaw',
});

const search = ref(props.filters.search);

const hasFilters = computed(() =>
    Object.values(props.filters).some((value) => value !== ''),
);

const apply = (changes: Partial<RegistrationFilters>): void => {
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
    apply({ event: '', status: '', attendance: '', search: '' });
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
        name: 'event' as const,
        placeholder: 'All events',
        items: props.filterOptions.events,
    },
    {
        name: 'status' as const,
        placeholder: 'All statuses',
        items: props.filterOptions.statuses,
    },
    {
        name: 'attendance' as const,
        placeholder: 'Online and in person',
        items: props.filterOptions.attendances,
    },
]);
</script>

<template>
    <Head :title="$t('Registrations')" />

    <div class="flex flex-col gap-6 p-4">
        <Heading :title="$t('Registrations')" />

        <div class="flex flex-col gap-3">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Input
                    v-model="search"
                    type="search"
                    :placeholder="$t('Search by participant')"
                    :aria-label="$t('Search by participant')"
                    data-test="registration-search"
                />
                <FilterSelect
                    v-for="field in filterFields"
                    :key="field.name"
                    :model-value="filters[field.name]"
                    :placeholder="$t(field.placeholder)"
                    :items="field.items"
                    :test-id="`registration-filter-${field.name}`"
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
                    <TableHead>{{ $t('Participant') }}</TableHead>
                    <TableHead>{{ $t('Event') }}</TableHead>
                    <TableHead>{{ $t('Attendance') }}</TableHead>
                    <TableHead class="text-right">{{ $t('Amount') }}</TableHead>
                    <TableHead>{{ $t('Status') }}</TableHead>
                    <TableHead>{{ $t('Registered at') }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="registration in registrations.data"
                    :key="registration.id"
                    data-test="registration-row"
                >
                    <TableCell>
                        <div class="font-medium">
                            {{ registration.user.name }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {{ registration.user.email }}
                        </div>
                    </TableCell>
                    <TableCell class="max-w-72 whitespace-normal">
                        {{ registration.event.title }}
                        <Badge
                            v-if="registration.event.is_deleted"
                            variant="secondary"
                            class="ml-1"
                        >
                            {{ $t('Event deleted') }}
                        </Badge>
                    </TableCell>
                    <TableCell>{{ registration.attendance_label }}</TableCell>
                    <TableCell class="text-right tabular-nums">
                        {{ formatPrice(registration.amount) }}
                    </TableCell>
                    <TableCell>
                        <RegistrationStatusBadge
                            :status="registration.status"
                            :label="registration.status_label"
                        />
                    </TableCell>
                    <TableCell class="text-muted-foreground tabular-nums">
                        {{
                            registration.created_at
                                ? dateFormatter.format(
                                      new Date(registration.created_at),
                                  )
                                : ''
                        }}
                    </TableCell>
                </TableRow>
                <TableEmpty v-if="registrations.data.length === 0" :colspan="6">
                    {{
                        hasFilters
                            ? $t('No registrations match these filters.')
                            : $t('No registrations yet.')
                    }}
                </TableEmpty>
            </TableBody>
        </Table>

        <PaginationControls :paginator="registrations" />
    </div>
</template>
