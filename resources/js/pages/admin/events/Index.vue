<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import EventTable from '@/components/admin/events/EventTable.vue';
import PaginationControls from '@/components/PaginationControls.vue';
import FilterSelect from '@/components/FilterSelect.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/admin/events';
import type {
    AdminEventFilterOptions,
    AdminEventFilters,
    EventResource,
    Paginated,
    SelectOption,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Events',
                href: index(),
            },
        ],
    },
});

const props = defineProps<{
    overview: {
        upcoming: EventResource[];
        past: EventResource[];
    } | null;
    events: Paginated<EventResource> | null;
    filters: AdminEventFilters;
    filterOptions: AdminEventFilterOptions;
    sorts: SelectOption[];
}>();

const defaultSort = 'upcoming_then_past';

const sortQuery = computed(() =>
    props.filters.sort === defaultSort ? {} : { sort: props.filters.sort },
);

const search = ref(props.filters.search);

const hasFilters = computed(
    () =>
        props.filters.format !== '' ||
        props.filters.profession !== '' ||
        props.filters.voivodeship !== '' ||
        props.filters.price !== '' ||
        props.filters.visibility !== '' ||
        props.filters.search !== '',
);

const apply = (changes: Partial<AdminEventFilters>): void => {
    const filters = { ...props.filters, search: search.value, ...changes };

    router.get(
        index.url(),
        {
            ...(filters.search && { search: filters.search }),
            ...(filters.trashed && { trashed: 1 }),
            ...(filters.sort !== defaultSort && { sort: filters.sort }),
            ...(filters.format && { format: filters.format }),
            ...(filters.profession && { profession: filters.profession }),
            ...(filters.voivodeship && { voivodeship: filters.voivodeship }),
            ...(filters.price && { price: filters.price }),
            ...(filters.visibility && { visibility: filters.visibility }),
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const clearFilters = (): void => {
    search.value = '';
    apply({
        search: '',
        format: '',
        profession: '',
        voivodeship: '',
        price: '',
        visibility: '',
    });
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
        name: 'format' as const,
        placeholder: 'All formats',
        items: props.filterOptions.formats,
    },
    {
        name: 'profession' as const,
        placeholder: 'All professions',
        items: props.filterOptions.professions,
    },
    {
        name: 'voivodeship' as const,
        placeholder: 'All voivodeships',
        items: props.filterOptions.voivodeships,
    },
    {
        name: 'price' as const,
        placeholder: 'Any price',
        items: props.filterOptions.prices,
    },
    {
        name: 'visibility' as const,
        placeholder: 'Visible and hidden',
        items: props.filterOptions.visibilities,
    },
]);
</script>

<template>
    <Head :title="$t('Events')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading :title="$t('Events')" />
            <Button as-child>
                <Link :href="EventController.create()">
                    <Plus class="size-4" />
                    {{ $t('Add event') }}
                </Link>
            </Button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <nav class="flex gap-2" :aria-label="$t('Event list')">
                <Button
                    :variant="filters.trashed ? 'ghost' : 'secondary'"
                    size="sm"
                    as-child
                >
                    <Link :href="index({ query: sortQuery })">{{
                        $t('Active')
                    }}</Link>
                </Button>
                <Button
                    :variant="filters.trashed ? 'secondary' : 'ghost'"
                    size="sm"
                    as-child
                >
                    <Link
                        :href="index({ query: { ...sortQuery, trashed: 1 } })"
                    >
                        {{ $t('Deleted') }}
                    </Link>
                </Button>
            </nav>

            <div
                class="flex w-full flex-wrap items-center justify-end gap-2 lg:w-auto lg:flex-nowrap"
            >
                <Select
                    :model-value="filters.sort"
                    @update:model-value="apply({ sort: String($event) })"
                >
                    <SelectTrigger
                        class="w-60"
                        :aria-label="$t('Sort by')"
                        data-test="event-sort"
                    >
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in sorts"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Input
                    v-model="search"
                    type="search"
                    class="w-full max-w-sm lg:w-80"
                    :placeholder="$t('Search by title or city')"
                    :aria-label="$t('Search by title or city')"
                    data-test="event-search"
                />
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <FilterSelect
                    v-for="field in filterFields"
                    :key="field.name"
                    :model-value="filters[field.name]"
                    :placeholder="$t(field.placeholder)"
                    :items="field.items"
                    :test-id="`admin-filter-${field.name}`"
                    @update:model-value="apply({ [field.name]: $event })"
                />
            </div>
            <div v-if="hasFilters">
                <Button
                    variant="ghost"
                    size="sm"
                    data-test="admin-filter-clear"
                    @click="clearFilters"
                >
                    {{ $t('Clear filters') }}
                </Button>
            </div>
        </div>

        <template v-if="overview">
            <section class="flex flex-col gap-3">
                <Heading variant="small" :title="$t('Upcoming events')" />
                <EventTable :events="overview.upcoming" />
                <div v-if="overview.upcoming.length > 0">
                    <Button variant="outline" size="sm" as-child>
                        <Link
                            :href="index({ query: { sort: 'upcoming' } })"
                            data-test="more-upcoming-events"
                        >
                            {{ $t('See more') }}
                        </Link>
                    </Button>
                </div>
            </section>

            <section class="flex flex-col gap-3">
                <Heading variant="small" :title="$t('Past events')" />
                <EventTable :events="overview.past" />
                <div v-if="overview.past.length > 0">
                    <Button variant="outline" size="sm" as-child>
                        <Link
                            :href="index({ query: { sort: 'past' } })"
                            data-test="more-past-events"
                        >
                            {{ $t('See more') }}
                        </Link>
                    </Button>
                </div>
            </section>
        </template>

        <template v-if="events">
            <EventTable
                :events="events.data"
                :with-sections="filters.sort === defaultSort"
            />
            <PaginationControls :paginator="events" />
        </template>
    </div>
</template>
