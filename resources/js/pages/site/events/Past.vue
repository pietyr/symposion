<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import PaginationControls from '@/components/PaginationControls.vue';
import EventFilters from '@/components/site/events/EventFilters.vue';
import EventGrid from '@/components/site/events/EventGrid.vue';
import { past } from '@/routes/events';
import type {
    EventFilterOptions,
    EventFilters as Filters,
    Paginated,
    PublicEventResource,
} from '@/types';

const props = defineProps<{
    events: Paginated<PublicEventResource>;
    filters: Filters;
    filterOptions: EventFilterOptions;
}>();

const hasFilters = computed(() =>
    Object.values(props.filters).some((value) => value !== ''),
);
</script>

<template>
    <Head :title="$t('Past events')" />

    <section class="flex flex-col gap-6">
        <h1 class="text-3xl font-semibold tracking-tight">
            {{ $t('Past events') }}
        </h1>

        <EventFilters
            :url="past.url()"
            :filters="filters"
            :options="filterOptions"
        />

        <EventGrid
            :events="events.data"
            :empty-message="
                hasFilters
                    ? $t('No events match these filters.')
                    : $t('No past events.')
            "
        />

        <PaginationControls :paginator="events" />
    </section>
</template>
