<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import type { RouteFormDefinition } from '@/wayfinder';

const props = defineProps<{
    action: RouteFormDefinition<'post'>;
    isFirst: boolean;
    isLast: boolean;
    upLabel: string;
    downLabel: string;
    testId: string;
}>();

const moves = computed(() => [
    {
        direction: 'up',
        icon: ArrowUp,
        label: props.upLabel,
        isAtEdge: props.isFirst,
    },
    {
        direction: 'down',
        icon: ArrowDown,
        label: props.downLabel,
        isAtEdge: props.isLast,
    },
]);
</script>

<template>
    <div class="flex gap-1">
        <Form
            v-for="move in moves"
            :key="move.direction"
            v-bind="action"
            :options="{ preserveScroll: true, preserveState: true }"
            v-slot="{ processing }"
        >
            <input type="hidden" name="direction" :value="move.direction" />
            <Button
                type="submit"
                variant="ghost"
                size="icon-sm"
                :disabled="processing || move.isAtEdge"
                :aria-label="move.label"
                :data-test="`${testId}-${move.direction}`"
            >
                <component :is="move.icon" class="size-4" />
            </Button>
        </Form>
    </div>
</template>
