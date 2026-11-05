<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { useDebounceFn } from '@vueuse/core';
import { computed, ref, watch } from 'vue';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import EventTable from '@/components/admin/events/EventTable.vue';
import PaginationControls from '@/components/PaginationControls.vue';
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
import type { EventResource, Paginated, SelectOption } from '@/types';

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
    filters: {
        search: string;
        trashed: boolean;
        sort: string;
    };
    sorts: SelectOption[];
}>();

const defaultSort = 'upcoming_then_past';

const sortQuery = computed(() =>
    props.filters.sort === defaultSort ? {} : { sort: props.filters.sort },
);

const search = ref(props.filters.search);

const applyFilters = (sort: string): void => {
    router.get(
        index.url({
            query: {
                ...(search.value && { search: search.value }),
                ...(props.filters.trashed && { trashed: 1 }),
                ...(sort !== defaultSort && { sort }),
            },
        }),
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

// Waits for a pause in typing, so a word sends one request instead of one per letter.
watch(
    search,
    useDebounceFn(() => applyFilters(props.filters.sort), 300),
);
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
                    @update:model-value="applyFilters(String($event))"
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
                    :placeholder="$t('Search by title')"
                    :aria-label="$t('Search by title')"
                    data-test="event-search"
                />
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
