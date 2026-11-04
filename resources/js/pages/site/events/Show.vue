<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Calendar, MapPin } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import type { PublicEventResource } from '@/types';

defineProps<{
    event: PublicEventResource;
}>();

const { formatDateTimeRange } = useDateTimeFormat();
</script>

<template>
    <Head :title="event.title" />

    <article class="flex flex-col gap-4">
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
        </dl>
    </article>
</template>
