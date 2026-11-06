<script setup lang="ts">
import type { EventSectionResource } from '@/types';

defineProps<{
    sections: EventSectionResource[];
}>();
</script>

<template>
    <nav
        :aria-label="$t('Table of contents')"
        class="flex flex-col gap-2 rounded-lg border p-4 text-sm"
        data-test="event-toc"
    >
        <h2 class="font-medium">{{ $t('Table of contents') }}</h2>
        <ul class="flex flex-col gap-1">
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
