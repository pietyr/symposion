<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Banknote, Calendar, MapPin } from '@lucide/vue';
import EventBanner from '@/components/site/events/EventBanner.vue';
import { Badge } from '@/components/ui/badge';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { usePriceFormat } from '@/composables/usePriceFormat';
import type { PublicEventResource } from '@/types';

defineProps<{
    event: PublicEventResource;
}>();

const { formatDateTimeRange } = useDateTimeFormat();
const { formatPrice } = usePriceFormat();
</script>

<template>
    <Head :title="event.title" />

    <article class="flex flex-col gap-4">
        <div class="-mt-8 mb-4 ml-[calc(50%-50vw)] w-screen">
            <EventBanner
                kind="banner"
                :url="event.large_banner_url"
                :default-number="event.default_banner"
                sizes="100vw"
                class="h-32 w-full object-cover sm:aspect-[4128/1024] sm:h-auto"
                fetchpriority="high"
                data-test="event-page-banner"
            />
        </div>

        <div>
            <Badge variant="secondary">{{ event.format_label }}</Badge>
        </div>

        <h1 class="text-3xl font-semibold tracking-tight">{{ event.title }}</h1>

        <dl class="flex flex-col gap-2 text-muted-foreground">
            <div class="flex items-center gap-2">
                <dt>
                    <Calendar class="size-4" />
                    <span class="sr-only">{{ $t('Date') }}</span>
                </dt>
                <dd>
                    {{ formatDateTimeRange(event.starts_at, event.ends_at) }}
                </dd>
            </div>
            <div v-if="event.city" class="flex items-center gap-2">
                <dt>
                    <MapPin class="size-4" />
                    <span class="sr-only">{{ $t('Venue') }}</span>
                </dt>
                <dd>{{ event.city }}, {{ event.venue }}</dd>
            </div>
            <div
                v-if="event.price_online !== null"
                class="flex items-center gap-2"
            >
                <dt>
                    <Banknote class="size-4" />
                    <span class="sr-only">{{ $t('Online price') }}</span>
                </dt>
                <dd>
                    {{ $t('Online') }}: {{ formatPrice(event.price_online) }}
                </dd>
            </div>
            <div
                v-if="event.price_in_person !== null"
                class="flex items-center gap-2"
            >
                <dt>
                    <Banknote class="size-4" />
                    <span class="sr-only">{{ $t('In-person price') }}</span>
                </dt>
                <dd>
                    {{ $t('In person') }}:
                    {{ formatPrice(event.price_in_person) }}
                </dd>
            </div>
        </dl>

        <section
            v-if="event.professions?.length"
            class="flex flex-col gap-2 pt-2"
        >
            <h2 class="text-sm font-medium">{{ $t('For professions') }}</h2>
            <ul class="flex flex-wrap gap-2">
                <li v-for="profession in event.professions" :key="profession">
                    <Badge variant="outline">{{ profession }}</Badge>
                </li>
            </ul>
        </section>

        <section
            v-if="event.specializations?.length"
            class="flex flex-col gap-2"
        >
            <h2 class="text-sm font-medium">{{ $t('Specializations') }}</h2>
            <ul class="flex flex-wrap gap-2">
                <li
                    v-for="specialization in event.specializations"
                    :key="specialization"
                >
                    <Badge variant="outline">{{ specialization }}</Badge>
                </li>
            </ul>
        </section>

        <section
            v-for="section in event.sections"
            :id="`section-${section.id}`"
            :key="section.id"
            class="flex scroll-mt-8 flex-col gap-3 pt-6"
            data-test="event-page-section"
        >
            <h2 class="text-2xl font-semibold tracking-tight">
                {{ section.title }}
            </h2>
            <!-- eslint-disable-next-line vue/no-v-html -- sanitized when saved (SanitizeEventContent) -->
            <div class="event-content" v-html="section.content" />
        </section>
    </article>
</template>
