<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { useTemplateRef } from 'vue';
import EventCard from '@/components/site/events/EventCard.vue';
import { Button } from '@/components/ui/button';
import type { PublicEventResource } from '@/types';

defineProps<{
    events: PublicEventResource[];
}>();

const track = useTemplateRef('track');

const scrollByPage = (direction: 1 | -1): void => {
    track.value?.scrollBy({
        left: direction * track.value.clientWidth,
        behavior: 'smooth',
    });
};
</script>

<template>
    <section class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight">
                {{ $t('Promoted events') }}
            </h2>
            <div v-if="events.length > 3" class="hidden gap-2 lg:flex">
                <Button
                    variant="outline"
                    size="icon"
                    :aria-label="$t('Previous')"
                    data-test="promoted-previous"
                    @click="scrollByPage(-1)"
                >
                    <ChevronLeft class="size-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    :aria-label="$t('Next')"
                    data-test="promoted-next"
                    @click="scrollByPage(1)"
                >
                    <ChevronRight class="size-4" />
                </Button>
            </div>
        </div>

        <div
            ref="track"
            class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto"
            data-test="promoted-track"
        >
            <div
                v-for="event in events"
                :key="event.id"
                class="w-[85%] shrink-0 snap-start sm:w-[calc(50%-0.5rem)] lg:w-[calc((100%-2rem)/3)]"
            >
                <EventCard :event="event" />
            </div>
        </div>
    </section>
</template>
