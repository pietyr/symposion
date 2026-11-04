<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PaginationControls from '@/components/PaginationControls.vue';
import EventGrid from '@/components/site/events/EventGrid.vue';
import type { Paginated, PublicEventResource } from '@/types';

defineProps<{
    events: Paginated<PublicEventResource>;
}>();
</script>

<template>
    <Head :title="$t('Upcoming events')" />

    <section class="flex flex-col gap-6">
        <h1 class="text-3xl font-semibold tracking-tight">
            {{ $t('Upcoming events') }}
        </h1>

        <EventGrid
            :events="events.data"
            :empty-message="$t('No upcoming events.')"
        />

        <PaginationControls :paginator="events" />
    </section>
</template>
