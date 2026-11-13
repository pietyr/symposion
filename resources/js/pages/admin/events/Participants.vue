<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import EventParticipantController from '@/actions/App/Http/Controllers/Admin/EventParticipantController';
import RegistrationController from '@/actions/App/Http/Controllers/Admin/RegistrationController';
import RegistrationStatusBadge from '@/components/registrations/RegistrationStatusBadge.vue';
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
import { index } from '@/routes/admin/events';
import type {
    Paginated,
    ParticipantFilterOptions,
    ParticipantFilters,
    RegistrationResource,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Events',
                href: index(),
            },
            {
                title: 'Participants',
            },
        ],
    },
});

const props = defineProps<{
    event: { id: number; title: string; is_deleted: boolean };
    registrations: Paginated<RegistrationResource>;
    filters: ParticipantFilters;
    filterOptions: ParticipantFilterOptions;
}>();

const search = ref(props.filters.search);

const hasFilters = computed(() =>
    Object.values(props.filters).some((value) => value !== ''),
);

const apply = (changes: Partial<ParticipantFilters>): void => {
    const filters = { ...props.filters, search: search.value, ...changes };

    router.get(
        EventParticipantController.index.url(props.event.id),
        Object.fromEntries(
            Object.entries(filters).filter(([, value]) => value !== ''),
        ),
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const clearFilters = (): void => {
    search.value = '';
    apply({ status: '', attendance: '', profession: '', search: '' });
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

// A one-format event has nothing to filter by attendance.
const filterFields = computed(() =>
    [
        {
            name: 'status' as const,
            placeholder: 'Pending and paid',
            items: props.filterOptions.statuses,
        },
        {
            name: 'attendance' as const,
            placeholder: 'Online and in person',
            items: props.filterOptions.attendances,
        },
        {
            name: 'profession' as const,
            placeholder: 'All professions',
            items: props.filterOptions.professions,
        },
    ].filter((field) => field.items.length > 0),
);
</script>

<template>
    <Head :title="`${$t('Participants')}: ${event.title}`" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start gap-3">
            <Heading
                :title="event.title"
                :description="`${$t('Participants')}: ${registrations.meta.total}`"
            />
            <Badge v-if="event.is_deleted" variant="secondary" class="mt-1">
                {{ $t('Event deleted') }}
            </Badge>
        </div>

        <div class="flex flex-col gap-3">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <Input
                    v-model="search"
                    type="search"
                    :placeholder="$t('Search by participant')"
                    :aria-label="$t('Search by participant')"
                    data-test="participant-search"
                />
                <FilterSelect
                    v-for="field in filterFields"
                    :key="field.name"
                    :model-value="filters[field.name]"
                    :placeholder="$t(field.placeholder)"
                    :items="field.items"
                    :test-id="`participant-filter-${field.name}`"
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
                    <TableHead>{{ $t('Profession') }}</TableHead>
                    <TableHead>{{ $t('Attendance') }}</TableHead>
                    <TableHead>{{ $t('Benefits') }}</TableHead>
                    <TableHead>{{ $t('Status') }}</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="registration in registrations.data"
                    :key="registration.id"
                    data-test="participant-row"
                >
                    <TableCell>
                        <Link
                            :href="RegistrationController.show(registration.id)"
                            class="font-medium hover:underline"
                        >
                            {{ registration.user.name }}
                        </Link>
                        <div class="text-xs text-muted-foreground">
                            {{ registration.user.email }}
                        </div>
                    </TableCell>
                    <TableCell>
                        {{ registration.user.profession_label ?? '—' }}
                    </TableCell>
                    <TableCell>{{ registration.attendance_label }}</TableCell>
                    <TableCell class="whitespace-normal">
                        <ul class="flex flex-col gap-0.5 text-sm">
                            <li
                                v-for="benefit in registration.benefits"
                                :key="benefit.id"
                            >
                                {{ benefit.benefit_name }}:
                                {{ benefit.option_name }}
                            </li>
                        </ul>
                    </TableCell>
                    <TableCell>
                        <RegistrationStatusBadge
                            :status="registration.status"
                            :label="registration.status_label"
                        />
                    </TableCell>
                </TableRow>
                <TableEmpty v-if="registrations.data.length === 0" :colspan="5">
                    {{
                        hasFilters
                            ? $t('No participants match these filters.')
                            : $t('No participants yet.')
                    }}
                </TableEmpty>
            </TableBody>
        </Table>

        <PaginationControls :paginator="registrations" />
    </div>
</template>
