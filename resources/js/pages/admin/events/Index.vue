<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Plus, RotateCcw, Search } from '@lucide/vue';
import EventController from '@/actions/App/Http/Controllers/Admin/EventController';
import RestoreEventController from '@/actions/App/Http/Controllers/Admin/RestoreEventController';
import PaginationControls from '@/components/PaginationControls.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useDateTimeFormat } from '@/composables/useDateTimeFormat';
import { index } from '@/routes/admin/events';
import type { EventResource, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Events',
                href: index(),
            },
        ],
    },
});

defineProps<{
    events: Paginated<EventResource>;
    filters: {
        search: string;
        trashed: boolean;
    };
}>();

const { formatDateRange } = useDateTimeFormat();
</script>

<template>
    <Head :title="$t('Events')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading :title="$t('Events')" />
            <Button as-child>
                <Link :href="EventController.create()">
                    <Plus class="size-4" />
                    {{ $t('Add event') }}
                </Link>
            </Button>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <nav class="flex gap-2" :aria-label="$t('Event list')">
                <Button
                    :variant="filters.trashed ? 'ghost' : 'secondary'"
                    size="sm"
                    as-child
                >
                    <Link :href="index()">{{ $t('Active') }}</Link>
                </Button>
                <Button
                    :variant="filters.trashed ? 'secondary' : 'ghost'"
                    size="sm"
                    as-child
                >
                    <Link :href="index({ query: { trashed: 1 } })">
                        {{ $t('Deleted') }}
                    </Link>
                </Button>
            </nav>

            <Form
                v-bind="index.form()"
                class="flex w-full max-w-sm gap-2"
                :options="{ preserveState: true }"
            >
                <input
                    v-if="filters.trashed"
                    type="hidden"
                    name="trashed"
                    value="1"
                />
                <Input
                    name="search"
                    type="search"
                    :default-value="filters.search"
                    :placeholder="$t('Search by title')"
                    :aria-label="$t('Search by title')"
                />
                <Button type="submit" variant="outline" size="icon">
                    <Search class="size-4" />
                    <span class="sr-only">{{ $t('Search') }}</span>
                </Button>
            </Form>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ $t('Title') }}</TableHead>
                    <TableHead>{{ $t('Date') }}</TableHead>
                    <TableHead>{{ $t('Format') }}</TableHead>
                    <TableHead class="text-right">
                        <span class="sr-only">{{ $t('Actions') }}</span>
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="event in events.data" :key="event.id">
                    <TableCell>
                        <div class="flex items-baseline justify-between gap-4">
                            <Link
                                v-if="!event.deleted_at"
                                :href="EventController.edit(event.id)"
                                class="font-medium hover:underline"
                            >
                                {{ event.title }}
                            </Link>
                            <span v-else class="font-medium">
                                {{ event.title }}
                            </span>
                            <span
                                v-if="event.city"
                                class="text-xs text-muted-foreground"
                            >
                                {{ event.city }}
                            </span>
                        </div>
                    </TableCell>
                    <TableCell class="whitespace-nowrap">
                        {{ formatDateRange(event.starts_at, event.ends_at) }}
                    </TableCell>
                    <TableCell>{{ event.format_label }}</TableCell>
                    <TableCell class="text-right">
                        <Form
                            v-if="event.deleted_at"
                            v-bind="RestoreEventController.form(event.id)"
                            v-slot="{ processing }"
                        >
                            <Button
                                type="submit"
                                variant="outline"
                                size="sm"
                                :disabled="processing"
                            >
                                <RotateCcw class="size-4" />
                                {{ $t('Restore') }}
                            </Button>
                        </Form>
                        <Button v-else variant="ghost" size="sm" as-child>
                            <Link :href="EventController.edit(event.id)">
                                {{ $t('Edit') }}
                            </Link>
                        </Button>
                    </TableCell>
                </TableRow>
                <TableEmpty v-if="events.data.length === 0" :colspan="4">
                    {{ $t('No events found.') }}
                </TableEmpty>
            </TableBody>
        </Table>

        <PaginationControls :paginator="events" />
    </div>
</template>
