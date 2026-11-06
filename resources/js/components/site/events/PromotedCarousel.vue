<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import {
    useDebounceFn,
    useDocumentVisibility,
    useElementSize,
    useEventListener,
    useIntervalFn,
    usePreferredReducedMotion,
} from '@vueuse/core';
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import EventCard from '@/components/site/events/EventCard.vue';
import { Button } from '@/components/ui/button';
import type { PublicEventResource } from '@/types';

const props = defineProps<{
    events: PublicEventResource[];
}>();

const autoScrollInterval = 5000;

const track = useTemplateRef('track');
const { width: trackWidth } = useElementSize(track);
const motion = usePreferredReducedMotion();
const visibility = useDocumentVisibility();

const isHovered = ref(false);
const hasKeyboardFocus = ref(false);

const gap = ref(0);
// Width of one card plus the gap after it.
const step = ref(0);
const loopWidth = computed(() => props.events.length * step.value);
const isLooping = computed(
    () => step.value > 0 && loopWidth.value - gap.value > trackWidth.value + 1,
);

// Three copies, so there is always a card on both sides of the middle one.
const items = computed(() => {
    const copies = isLooping.value ? [0, 1, 2] : [1];

    return copies.flatMap((copy) =>
        props.events.map((event) => ({
            event,
            key: `${copy}-${event.id}`,
            isCopy: copy !== 1,
        })),
    );
});

const scrollInstantly = (left: number): void => {
    track.value?.scrollTo({ left, behavior: 'instant' });
};

// The copies look the same, so moving back to the middle one cannot be seen.
const returnToMiddleCopy = (): void => {
    if (!track.value || !isLooping.value) {
        return;
    }

    const left = track.value.scrollLeft;

    if (left < loopWidth.value) {
        scrollInstantly(left + loopWidth.value);
    } else if (left >= loopWidth.value * 2) {
        scrollInstantly(left - loopWidth.value);
    }
};

useEventListener(track, 'scroll', useDebounceFn(returnToMiddleCopy, 150));

const move = (direction: 1 | -1): void => {
    returnToMiddleCopy();
    track.value?.scrollBy({
        left: direction * step.value,
        behavior: motion.value === 'reduce' ? 'instant' : 'smooth',
    });
};

watch(
    [trackWidth, () => props.events.length],
    () => {
        const card = track.value?.firstElementChild;

        if (track.value && card) {
            gap.value =
                parseFloat(getComputedStyle(track.value).columnGap) || 0;
            step.value = card.getBoundingClientRect().width + gap.value;
        }
    },
    { flush: 'post' },
);

watch(loopWidth, async () => {
    await nextTick();

    if (isLooping.value) {
        scrollInstantly(loopWidth.value);
    }
});

useIntervalFn(() => {
    if (
        isLooping.value &&
        !isHovered.value &&
        !hasKeyboardFocus.value &&
        visibility.value === 'visible' &&
        motion.value !== 'reduce'
    ) {
        move(1);
    }
}, autoScrollInterval);

const onFocusIn = (event: FocusEvent): void => {
    hasKeyboardFocus.value = (event.target as HTMLElement).matches(
        ':focus-visible',
    );
};
</script>

<template>
    <section
        class="flex flex-col gap-4"
        @pointerenter="isHovered = true"
        @pointerleave="isHovered = false"
        @focusin="onFocusIn"
        @focusout="hasKeyboardFocus = false"
    >
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold tracking-tight">
                {{ $t('Promoted events') }}
            </h2>
            <div v-if="isLooping" class="hidden gap-2 lg:flex">
                <Button
                    variant="outline"
                    size="icon"
                    :aria-label="$t('Previous')"
                    data-test="promoted-previous"
                    @click="move(-1)"
                >
                    <ChevronLeft class="size-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    :aria-label="$t('Next')"
                    data-test="promoted-next"
                    @click="move(1)"
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
                v-for="item in items"
                :key="item.key"
                class="w-[85%] shrink-0 snap-start sm:w-[calc(50%-0.5rem)] lg:w-[calc((100%-2rem)/3)]"
                :aria-hidden="item.isCopy || undefined"
            >
                <EventCard
                    :event="item.event"
                    :tabindex="item.isCopy ? -1 : undefined"
                />
            </div>
        </div>
    </section>
</template>
