<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { usePriceFormat } from '@/composables/usePriceFormat';
import { show } from '@/routes/events';
import type { PublicEventResource } from '@/types';

defineProps<{
    event: PublicEventResource;
}>();

const { formatDateRange } = useDateTimeFormat();
const { formatPriceRange } = usePriceFormat();
</script>

<template>
    <Link
        :href="show({ event: event.id, slug: event.slug })"
        class="block rounded-xl focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        data-test="event-card"
    >
        <Card class="h-full transition-colors hover:bg-accent/50">
            <CardHeader>
                <CardTitle class="leading-snug">{{ event.title }}</CardTitle>
            </CardHeader>
            <CardContent
                class="mt-auto flex flex-col gap-2 text-sm text-muted-foreground"
            >
                <span>
                    {{ formatDateRange(event.starts_at, event.ends_at) }}
                    <template v-if="event.city"> · {{ event.city }}</template>
                </span>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-foreground">
                        {{
                            formatPriceRange(
                                event.price_online,
                                event.price_in_person,
                            )
                        }}
                    </span>
                    <Badge variant="secondary">{{ event.format_label }}</Badge>
                </div>
            </CardContent>
        </Card>
    </Link>
</template>
