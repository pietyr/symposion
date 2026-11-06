<script setup lang="ts">
import { useElementSize } from '@vueuse/core';
import { computed, useTemplateRef } from 'vue';
import type { EventSectionResource } from '@/types';

defineProps<{
    sections: EventSectionResource[];
}>();

const nav = useTemplateRef('nav');
const { height } = useElementSize(nav, undefined, { box: 'border-box' });

// Sticks once it reaches the middle of the window; a tall list stops below the site header instead.
const stickyTop = computed(
    () => `max(5rem, calc(50vh - ${height.value / 2}px))`,
);
</script>

<template>
    <nav
        ref="nav"
        :aria-label="$t('Table of contents')"
        :style="{ top: stickyTop }"
        class="rounded-lg border p-4 text-sm"
        data-test="event-toc"
    >
        <ul class="flex flex-col gap-1">
            <li>
                <a
                    href="#event-details"
                    class="block py-1 text-muted-foreground transition-colors hover:text-foreground"
                >
                    {{ $t('Information') }}
                </a>
            </li>
            <li v-for="section in sections" :key="section.id">
                <a
                    :href="`#section-${section.id}`"
                    class="block py-1 text-muted-foreground transition-colors hover:text-foreground"
                >
                    {{ section.title }}
                </a>
            </li>
        </ul>
    </nav>
</template>
