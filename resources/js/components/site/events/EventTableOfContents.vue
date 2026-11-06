<script setup lang="ts">
import type { EventSectionResource } from '@/types';

defineProps<{
    sections: EventSectionResource[];
}>();
</script>

<template>
    <nav
        :aria-label="$t('Table of contents')"
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
