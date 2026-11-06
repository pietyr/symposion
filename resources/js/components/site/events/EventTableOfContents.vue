<script setup lang="ts">
import { useElementSize, usePreferredReducedMotion } from '@vueuse/core';
import { trans } from 'laravel-vue-i18n';
import { computed, useTemplateRef } from 'vue';
import type { EventSectionResource } from '@/types';

const props = defineProps<{
    sections: EventSectionResource[];
}>();

const items = computed(() => [
    { id: 'informacje', title: trans('Information') },
    ...props.sections.map((section) => ({
        id: section.slug,
        title: section.title,
    })),
]);

const nav = useTemplateRef('nav');
const { height } = useElementSize(nav, undefined, { box: 'border-box' });

// Sticks once it reaches the middle of the window; a tall list stops below the site header instead.
const stickyTop = computed(
    () => `max(5rem, calc(50vh - ${height.value / 2}px))`,
);

const motion = usePreferredReducedMotion();

// Only these links scroll smoothly: a global setting would also animate moving between pages.
const scrollTo = (id: string): void => {
    document.getElementById(id)?.scrollIntoView({
        behavior: motion.value === 'reduce' ? 'auto' : 'smooth',
    });
    window.history.pushState(window.history.state, '', `#${id}`);
};
</script>

<template>
    <nav
        ref="nav"
        :aria-label="$t('Table of contents')"
        :style="{ top: stickyTop }"
        class="rounded-lg border border-t-4 border-t-(color:--event-frame) p-4 text-sm"
        data-test="event-toc"
    >
        <ul class="flex flex-col gap-1">
            <li v-for="item in items" :key="item.id">
                <a
                    :href="`#${item.id}`"
                    class="block py-1 text-muted-foreground transition-colors hover:text-(--event-link)"
                    @click.prevent="scrollTo(item.id)"
                >
                    {{ item.title }}
                </a>
            </li>
        </ul>
    </nav>
</template>
