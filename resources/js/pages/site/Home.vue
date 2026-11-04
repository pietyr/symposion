<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EventGrid from '@/components/site/events/EventGrid.vue';
import { Button } from '@/components/ui/button';
import { index } from '@/routes/events';
import type { PublicEventResource } from '@/types';

defineProps<{
    events: PublicEventResource[];
}>();
</script>

<template>
    <Head :title="$t('Medical conferences')" />

    <section class="flex flex-col gap-6">
        <h1 class="text-3xl font-semibold tracking-tight">
            {{ $t('Upcoming events') }}
        </h1>

        <EventGrid
            :events="events"
            :empty-message="$t('No upcoming events.')"
        />

        <div v-if="events.length > 0">
            <Button variant="outline" as-child>
                <Link :href="index()">{{ $t('All upcoming events') }}</Link>
            </Button>
        </div>
    </section>
</template>
