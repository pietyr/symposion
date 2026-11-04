<script setup lang="ts">
import EventCard from '@/components/site/events/EventCard.vue';
import type { PublicEventResource } from '@/types';

defineProps<{
    events: PublicEventResource[];
    emptyMessage: string;
}>();
</script>

<template>
    <div
        v-if="events.length > 0"
        class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
        <EventCard v-for="event in events" :key="event.id" :event="event" />
    </div>
    <p v-else class="text-muted-foreground">{{ emptyMessage }}</p>
</template>
