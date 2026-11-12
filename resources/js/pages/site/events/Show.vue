<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Banknote, Calendar, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import EventBanner from '@/components/site/events/EventBanner.vue';
import EventPlaceBadges from '@/components/site/events/EventPlaceBadges.vue';
import EventRegistrationPanel from '@/components/site/events/EventRegistrationPanel.vue';
import MyRegistrationSummary from '@/components/site/events/MyRegistrationSummary.vue';
import EventTableOfContents from '@/components/site/events/EventTableOfContents.vue';
import { Badge } from '@/components/ui/badge';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { usePriceFormat } from '@/composables/usePriceFormat';
import type {
    EventRegistrationState,
    MyRegistrationResource,
    PublicEventResource,
} from '@/types';

const props = defineProps<{
    event: PublicEventResource;
    registration: EventRegistrationState;
    myRegistration: MyRegistrationResource | null;
}>();

const brandColors = computed(() => ({
    '--brand-primary': props.event.primary_color ?? undefined,
    '--brand-accent': props.event.accent_color ?? undefined,
}));

const { formatDateRange, formatDateTimeRange } = useDateTimeFormat();
const { formatPrice } = usePriceFormat();
</script>

<template>
    <Head :title="event.title" />

    <article class="event-page flex flex-col gap-4" :style="brandColors">
        <div class="relative -mt-8 mb-4 ml-[calc(50%-50vw)] w-screen">
            <EventBanner
                kind="banner"
                :url="event.large_banner_url"
                :default-number="event.default_banner"
                sizes="100vw"
                class="h-60 w-full object-cover sm:aspect-[4128/1024] sm:h-auto"
                fetchpriority="high"
                data-test="event-page-banner"
            />
            <div
                class="absolute inset-x-0 bottom-0 bg-black/60 text-white backdrop-blur-sm"
                data-test="event-banner-bar"
            >
                <div
                    class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-6 gap-y-2 px-4 py-4 sm:py-6"
                >
                    <h1
                        class="text-2xl leading-tight font-semibold tracking-tight sm:text-3xl"
                    >
                        {{ event.title }}
                    </h1>
                    <div
                        class="flex flex-wrap items-center gap-3 text-base sm:text-lg"
                    >
                        <EventPlaceBadges :event="event" large />
                        <span>{{
                            formatDateRange(event.starts_at, event.ends_at)
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-start"
        >
            <EventTableOfContents
                v-if="event.sections?.length"
                :sections="event.sections"
                class="lg:sticky lg:order-last"
            />

            <div
                class="flex min-w-0 flex-col gap-8"
                :class="{ 'lg:col-span-2': !event.sections?.length }"
            >
                <div id="informacje" class="flex scroll-mt-20 flex-col gap-4">
                    <h2
                        class="border-l-4 border-(color:--event-mark) pl-3 text-2xl font-semibold tracking-tight"
                    >
                        {{ $t('Information') }}
                    </h2>

                    <dl class="flex flex-col gap-2 text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <dt>
                                <Calendar class="size-4 text-(--event-icon)" />
                                <span class="sr-only">{{ $t('Date') }}</span>
                            </dt>
                            <dd>
                                {{
                                    formatDateTimeRange(
                                        event.starts_at,
                                        event.ends_at,
                                    )
                                }}
                            </dd>
                        </div>
                        <div v-if="event.city" class="flex items-center gap-2">
                            <dt>
                                <MapPin class="size-4 text-(--event-icon)" />
                                <span class="sr-only">{{ $t('Venue') }}</span>
                            </dt>
                            <dd>{{ event.city }}, {{ event.venue }}</dd>
                        </div>
                        <div
                            v-if="event.price_online !== null"
                            class="flex items-center gap-2"
                        >
                            <dt>
                                <Banknote class="size-4 text-(--event-icon)" />
                                <span class="sr-only">{{
                                    $t('Online price')
                                }}</span>
                            </dt>
                            <dd>
                                {{ $t('Online') }}:
                                {{ formatPrice(event.price_online) }}
                            </dd>
                        </div>
                        <div
                            v-if="event.price_in_person !== null"
                            class="flex items-center gap-2"
                        >
                            <dt>
                                <Banknote class="size-4 text-(--event-icon)" />
                                <span class="sr-only">{{
                                    $t('In-person price')
                                }}</span>
                            </dt>
                            <dd>
                                {{ $t('In person') }}:
                                {{ formatPrice(event.price_in_person) }}
                            </dd>
                        </div>
                    </dl>

                    <MyRegistrationSummary
                        v-if="myRegistration"
                        :registration="myRegistration"
                    />
                    <EventRegistrationPanel
                        v-else
                        :event="event"
                        :registration="registration"
                    />

                    <section
                        v-if="event.professions?.length"
                        class="flex flex-col gap-2 pt-2"
                    >
                        <h2 class="text-sm font-medium">
                            {{ $t('For professions') }}
                        </h2>
                        <ul class="flex flex-wrap gap-2">
                            <li
                                v-for="profession in event.professions"
                                :key="profession"
                            >
                                <Badge variant="outline">{{
                                    profession
                                }}</Badge>
                            </li>
                        </ul>
                    </section>

                    <section
                        v-if="event.specializations?.length"
                        class="flex flex-col gap-2"
                    >
                        <h2 class="text-sm font-medium">
                            {{ $t('Specializations') }}
                        </h2>
                        <ul class="flex flex-wrap gap-2">
                            <li
                                v-for="specialization in event.specializations"
                                :key="specialization"
                            >
                                <Badge variant="outline">{{
                                    specialization
                                }}</Badge>
                            </li>
                        </ul>
                    </section>
                </div>

                <section
                    v-for="section in event.sections"
                    :id="section.slug"
                    :key="section.id"
                    class="flex scroll-mt-20 flex-col gap-3"
                    data-test="event-page-section"
                >
                    <h2
                        class="border-l-4 border-(color:--event-mark) pl-3 text-2xl font-semibold tracking-tight"
                    >
                        {{ section.title }}
                    </h2>
                    <!-- eslint-disable-next-line vue/no-v-html -- sanitized when saved (SanitizeEventContent) -->
                    <div class="event-content" v-html="section.content" />
                </section>
            </div>
        </div>
    </article>
</template>
