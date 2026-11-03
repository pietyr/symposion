<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineProps<{
    paginator: Pick<Paginated<unknown>, 'links' | 'meta'>;
}>();
</script>

<template>
    <div
        v-if="paginator.meta.total > 0"
        class="flex items-center justify-between gap-4 text-sm text-muted-foreground"
    >
        <p>
            {{
                $t('Showing :from–:to of :total', {
                    from: String(paginator.meta.from),
                    to: String(paginator.meta.to),
                    total: String(paginator.meta.total),
                })
            }}
        </p>

        <div class="flex items-center gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="!paginator.links.prev"
                :as-child="!!paginator.links.prev"
            >
                <Link
                    v-if="paginator.links.prev"
                    :href="paginator.links.prev"
                    preserve-scroll
                >
                    <ChevronLeft class="size-4" />
                    {{ $t('Previous') }}
                </Link>
                <template v-else>
                    <ChevronLeft class="size-4" />
                    {{ $t('Previous') }}
                </template>
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="!paginator.links.next"
                :as-child="!!paginator.links.next"
            >
                <Link
                    v-if="paginator.links.next"
                    :href="paginator.links.next"
                    preserve-scroll
                >
                    {{ $t('Next') }}
                    <ChevronRight class="size-4" />
                </Link>
                <template v-else>
                    {{ $t('Next') }}
                    <ChevronRight class="size-4" />
                </template>
            </Button>
        </div>
    </div>
</template>
