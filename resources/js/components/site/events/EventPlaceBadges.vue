<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type { PublicEventResource } from '@/types';

const props = defineProps<{
    event: Pick<PublicEventResource, 'city' | 'format'>;
}>();

const isOnline = computed(() => props.event.format !== 'in_person');
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <Badge
            v-if="event.city"
            class="border-transparent bg-emerald-700 text-white"
            data-test="event-place-city"
        >
            {{ event.city }}
        </Badge>
        <Badge
            v-if="isOnline"
            class="border-transparent bg-sky-700 text-white"
            data-test="event-place-online"
        >
            {{ $t('Online') }}
        </Badge>
    </div>
</template>
